<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model\ImportExport;

use Laminas\Validator\EmailAddress;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use MageWorx\GiftCards\Api\GiftCardsRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use MageWorx\GiftCards\Model\ResourceModel\GiftCards as GiftCardsResourceModel;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\GiftCards\Api\Data\GiftCardsInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection as GroupCollection;

class ImportHandler implements \MageWorx\GiftCards\Api\ImportHandlerInterface
{
    const COUNT_FIELDS_CSV = 22;
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var GiftCardsRepositoryInterface
     */
    protected $giftCardsRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var GiftCardsResourceModel
     */
    protected $giftCardsResourceModel;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var GroupCollection
     */
    protected $groupCollection;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var EmailAddress
     */
    protected $emailAddressValidator;

    /**
     * @var array
     */
    protected $storesCodes = [];

    /**
     * @var
     */
    protected $groupNames = [];

    /**
     * @var array
     */
    protected $giftCardsData = [];

    /**
     * @var array
     */
    protected $giftCardsDataFromCSV = [];

    /**
     * @var string
     */
    protected $delimiter = ',';

    /**
     * @var string
     */
    protected $enclosure = '"';

    /**
     * ImportHandler constructor.
     *
     * @param Csv $csvProcessor
     * @param Escaper $escaper
     * @param GiftCardsRepositoryInterface $giftCardsRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GiftCardsResourceModel $giftCardsResourceModel
     * @param StoreManagerInterface $storeManager
     * @param GroupCollection $groupCollection
     * @param DateTime $date
     * @param EmailAddress $emailAddressValidator
     */
    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Escaper $escaper,
        GiftCardsRepositoryInterface $giftCardsRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GiftCardsResourceModel $giftCardsResourceModel,
        StoreManagerInterface $storeManager,
        GroupCollection $groupCollection,
        DateTime $date,
        \Laminas\Validator\EmailAddress $emailAddressValidator
    ) {
        $this->csvProcessor           = $csvProcessor;
        $this->escaper                = $escaper;
        $this->giftCardsRepository    = $giftCardsRepository;
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
        $this->giftCardsResourceModel = $giftCardsResourceModel;
        $this->storeManager           = $storeManager;
        $this->groupCollection        = $groupCollection;
        $this->date                   = $date;
        $this->emailAddressValidator  = $emailAddressValidator;

        $this->storesCodes = $this->getStoresCodes();
        $this->groupNames  = $this->getGroupsNames();
    }

    /**
     * Import CSV file
     *
     * @param array $file file info retrieved from $_FILES array
     * @return void
     * @throws LocalizedException
     * @throws \Exception
     */
    public function importFromCsvFile($file)
    {
        if (!isset($file['tmp_name'])
            || !isset($file['type'])
            || !in_array($file['type'], $this->getAllowedFormatFile())) {
            throw new LocalizedException(__('Invalid file upload attempt.'));
        }

        $this->csvProcessor->setDelimiter($this->delimiter);
        $this->csvProcessor->setEnclosure($this->enclosure);
        $rawData     = $this->csvProcessor->getData($file['tmp_name']);
        $dataFromCSV = $this->getDataFormCSV($rawData);

        if ($this->validateData($dataFromCSV)) {
            $dataFromCSV         = $this->replaceStoresCodesToStoresIds($dataFromCSV);
            $dataFromCSV         = $this->replaceGroupsNamesToGroupsIds($dataFromCSV);
            $dataFromCSV         = $this->replaceCardType($dataFromCSV);
            $dataFromCSV         = $this->replaceCardStatus($dataFromCSV);
            $this->giftCardsData = $this->getPrepareGiftCardsData();

            $giftCardsDataToUpdate = $this->prepareGiftCardsDataToUpdate($dataFromCSV);
            $giftCardsDataToSave   = $this->prepareGiftCardsDataToSave($dataFromCSV);

            if (!empty($giftCardsDataToUpdate)) {
                $this->giftCardsResourceModel->updateGiftCards($giftCardsDataToUpdate);
            }

            if (!empty($giftCardsDataToSave)) {
                $this->giftCardsResourceModel->saveGiftCards($giftCardsDataToSave);
            }
        }
    }


    /**
     * @param array $dataFromCSV
     * @return bool
     * @throws LocalizedException
     */
    protected function validateData($dataFromCSV): bool
    {
        $this->validateDuplicatedData($dataFromCSV);
        $this->validateEmails($dataFromCSV);
        $this->validateCurrencies($dataFromCSV);
        $this->validateStoresCodes($dataFromCSV);
        $this->validateGroupsNames($dataFromCSV);

        return true;
    }

    /**
     * @param array $dataFromCSV
     * @return array
     */
    protected function replaceStoresCodesToStoresIds($dataFromCSV)
    {
        if (empty($this->storesCodes)) {
            $this->storesCodes = $this->getStoresCodes();
        }
        foreach ($dataFromCSV as $dataIndex => $datum) {
            $storeCodes = $datum[GiftCardsInterface::STORE_CODE];
            unset($dataFromCSV[$dataIndex][GiftCardsInterface::STORE_CODE]);

            if (strcasecmp($storeCodes, GiftCardsInterface::ALL) == 0) {
                $dataFromCSV[$dataIndex][GiftCardsInterface::STORE_ID][] = 0;
                continue;
            }

            $dataFromCSV[$dataIndex][GiftCardsInterface::STORE_ID] = $this->getStoresIdsByStoreCodes($storeCodes);
        }

        return $dataFromCSV;
    }

    /**
     * @param string $storeCodes
     * @return array
     */
    protected function getStoresIdsByStoreCodes($storeCodes)
    {
        $storesIds        = [];
        $storesCodesArray = explode(',', $storeCodes);
        if (!is_array($storesCodesArray) || empty($storesCodesArray)) {
            return [];
        }

        foreach ($storesCodesArray as $storeCode) {
            $key = array_search($storeCode, $this->storesCodes);
            if ($key !== false) {
                $storesIds[] = $key;
            }
        }

        return $storesIds;
    }

    /**
     * @param array $dataFromCSV
     * @return array
     */
    protected function replaceGroupsNamesToGroupsIds($dataFromCSV)
    {
        if (empty($this->groupNames)) {
            $this->groupNames = $this->getGroupsNames();
        }

        foreach ($dataFromCSV as $dataIndex => $datum) {
            $groupsNames = $datum[GiftCardsInterface::GROUP_NAME];
            unset($dataFromCSV[$dataIndex][GiftCardsInterface::GROUP_NAME]);

            if (strcasecmp($groupsNames, GiftCardsInterface::ALL) == 0) {
                $dataFromCSV[$dataIndex][GiftCardsInterface::GROUP_ID] = array_keys($this->groupNames);
                continue;
            }

            $dataFromCSV[$dataIndex][GiftCardsInterface::GROUP_ID] = $this->getGroupsIdsByGroupNames($groupsNames);
        }

        return $dataFromCSV;
    }

    /**
     * @param array $dataFromCSV
     * @return array
     */
    protected function replaceCardType($dataFromCSV)
    {
        foreach ($dataFromCSV as $dataIndex => $datum) {
            $cardType = $datum[GiftCardsInterface::CARD_TYPE];

            if ($cardType == 'email') {
                $dataFromCSV[$dataIndex][GiftCardsInterface::CARD_TYPE] = '1';
                continue;
            }

            if ($cardType == 'print') {
                $dataFromCSV[$dataIndex][GiftCardsInterface::CARD_TYPE] = '2';
                continue;
            }

            if ($cardType == 'offline') {
                $dataFromCSV[$dataIndex][GiftCardsInterface::CARD_TYPE] = '3';
                continue;
            }
        }

        return $dataFromCSV;
    }

    /**
     * @param array $dataFromCSV
     * @return array
     */
    protected function replaceCardStatus($dataFromCSV)
    {
        foreach ($dataFromCSV as $dataIndex => $datum) {
            $cardStatus = $datum[GiftCardsInterface::CARD_STATUS];

            if ($cardStatus == 'Inactive') {
                $dataFromCSV[$dataIndex][GiftCardsInterface::CARD_STATUS] = '0';
                continue;
            }

            if ($cardStatus == 'Active') {
                $dataFromCSV[$dataIndex][GiftCardsInterface::CARD_STATUS] = '1';
                continue;
            }

            if ($cardStatus == 'Used') {
                $dataFromCSV[$dataIndex][GiftCardsInterface::CARD_STATUS] = '2';
                continue;
            }
        }

        return $dataFromCSV;
    }

    /**
     * @param $groupsNames
     * @return array
     */
    protected function getGroupsIdsByGroupNames($groupsNames)
    {
        $groupIds         = [];
        $groupsNamesArray = explode(',', $groupsNames);
        if (!is_array($groupsNamesArray) || empty($groupsNamesArray)) {
            return [];
        }

        foreach ($groupsNamesArray as $groupName) {
            $key = array_search($groupName, $this->groupNames);
            if ($key !== false) {
                $groupIds[] = $key;
            }
        }

        return $groupIds;
    }

    /**
     * @return array
     */
    protected function getPrepareGiftCardsData()
    {
        $prepareGiftCardsData = [];
        $searchCriteria       = $this->searchCriteriaBuilder->create();
        $giftCardsData        = $this->giftCardsRepository
            ->getList($searchCriteria, true)
            ->getItems();

        foreach ($giftCardsData as $datum) {
            if (!array_key_exists($datum[GiftCardsInterface::CARD_CODE], $prepareGiftCardsData)) {
                $prepareGiftCardsData[$datum[GiftCardsInterface::CARD_CODE]] = $datum;
            }
        }

        return $prepareGiftCardsData;
    }

    /**
     * @param array $rawData
     * @throws LocalizedException
     */
    protected function validateEmails(array $rawData): void
    {
        $invalidEmails = [];
        $mailToEmails  = GiftCardsInterface::MAIL_TO_EMAIL;

        /* check correct email */
        foreach ($rawData as $dataIndex => $datum) {
            if (is_array($datum)
                && !empty($datum[$mailToEmails])
                && !$this->emailAddressValidator->isValid(trim($datum[$mailToEmails]))) {
                $invalidEmails[] = $datum[$mailToEmails];
            }
        }

        if (!empty($invalidEmails)) {
            $invalidEmails         = array_unique($invalidEmails);
            $invalidEmailsAsString = $this->escaper->escapeHtml(implode(", ", $invalidEmails));
            throw new LocalizedException(__('Not correct emails: %1', $invalidEmailsAsString));
        }
    }

    /**
     * @param $rawData
     * @throws LocalizedException
     */
    protected function validateStoresCodes($rawData)
    {
        $invalidStoresCodes = [];
        if (empty($this->storesCodes)) {
            $this->storesCodes = $this->getStoresCodes();
        }
        $constStoreCode = GiftCardsInterface::STORE_CODE;

        foreach ($rawData as $dataIndex => $datum) {
            if (!array_key_exists($constStoreCode, $datum)) {
                continue;
            }

            if (is_array($datum) && isset($datum[$constStoreCode])) {

                if (strcasecmp($datum[$constStoreCode], GiftCardsInterface::ALL) == 0) {
                    continue;
                }

                $cardStoresCodes = explode(',', $datum[$constStoreCode]);
                foreach ($cardStoresCodes as $cardStoreCode) {
                    if (!in_array($cardStoreCode, $this->storesCodes)) {
                        $invalidStoresCodes[] = $cardStoreCode;
                    }
                }
            }
        }

        if (!empty($invalidStoresCodes)) {
            $invalidStoresCodes         = array_unique($invalidStoresCodes);
            $invalidStoresCodesAsString = $this->escaper->escapeHtml(implode(", ", $invalidStoresCodes));
            throw new LocalizedException(
                __('There are no stores with the following store codes: %1', $invalidStoresCodesAsString)
            );
        }
    }

    /**
     * @param array $rawData
     * @throws LocalizedException
     */
    protected function validateCurrencies($rawData)
    {
        $invalidCurrencies = [];
        $constCardCurrency = GiftCardsInterface::CARD_CURRENCY;

        foreach ($rawData as $dataIndex => $datum) {
            if (!array_key_exists($constCardCurrency, $datum)) {
                continue;
            }

            if (is_array($datum) && isset($datum[$constCardCurrency])) {
                if (!in_array($datum[$constCardCurrency], $this->getAllAvailableCurrencies())) {
                    $invalidCurrencies[] = $datum[$constCardCurrency];
                }
            }
        }

        if (!empty($invalidCurrencies)) {
            $invalidCurrencies         = array_unique($invalidCurrencies);
            $invalidCurrenciesAsString = $this->escaper->escapeHtml(implode(", ", $invalidCurrencies));
            throw new LocalizedException(
                __('Following currencies are not allowed for your store: %1', $invalidCurrenciesAsString)
            );
        }
    }

    /**
     * Get available currency from all stores
     *
     * @return array
     */
    protected function getAllAvailableCurrencies()
    {
        $availableCurrencies = [];
        foreach ($this->storeManager->getStores() as $store) {
            foreach ($store->getAvailableCurrencyCodes() as $currency) {
                $availableCurrencies[$currency] = $currency;
            }
        }

        return $availableCurrencies;
    }

    /**
     * @param $rawData
     * @throws LocalizedException
     */
    protected function validateGroupsNames($rawData)
    {
        $invalidGroupsNames = [];
        if (empty($this->groupNames)) {
            $this->groupNames = $this->getGroupsNames();
        }
        $constGroupName = GiftCardsInterface::GROUP_NAME;

        /* check correct email */
        foreach ($rawData as $dataIndex => $datum) {
            if (!array_key_exists($constGroupName, $datum)) {
                continue;
            }

            if (is_array($datum) && isset($datum[$constGroupName])) {
                if (strcasecmp($datum[$constGroupName], GiftCardsInterface::ALL) == 0) {
                    continue;
                }

                $cardGroupsNames = explode(',', $datum[$constGroupName]);
                foreach ($cardGroupsNames as $cardStoreCode) {
                    if (!in_array($cardStoreCode, $this->groupNames)) {
                        $invalidGroupsNames[] = $cardStoreCode;
                    }
                }
            }
        }

        if (!empty($invalidGroupsNames)) {
            $invalidGroupsNames        = array_unique($invalidGroupsNames);
            $invalidGroupsNameAsString = $this->escaper->escapeHtml(implode(", ", $invalidGroupsNames));
            throw new LocalizedException(
                __('Customer groups with the following names are not exist: %1', $invalidGroupsNameAsString)
            );
        }
    }

    /**
     * @param array $data
     * @throws LocalizedException
     */
    protected function validateDuplicatedData($data)
    {
        $duplicatedCardCodeArray = [];
        foreach ($data as $value) {

            if (empty($value[GiftCardsInterface::CARD_CODE])) {
                continue;
            }

            if (!array_key_exists($value[GiftCardsInterface::CARD_CODE], $this->giftCardsDataFromCSV)) {
                $this->giftCardsDataFromCSV[$value[GiftCardsInterface::CARD_CODE]] = $value;
            } else {
                $duplicatedCardCodeArray[] = $value[GiftCardsInterface::CARD_CODE];
            }
        }

        if (!empty($duplicatedCardCodeArray)) {
            $invalidDuplicatedCardCodeAsString = $this->escaper->escapeHtml(implode(", ", $duplicatedCardCodeArray));
            throw new LocalizedException(
                __('Not correct card codes: %1', $invalidDuplicatedCardCodeAsString)
            );
        }
    }

    /**
     * @param array $dataFromCSV
     * @return array
     */
    protected function prepareGiftCardsDataToUpdate($dataFromCSV)
    {
        $updateGiftCardsData = [];
        foreach ($dataFromCSV as $dataIndex => $datum) {
            $constCardCode = GiftCardsInterface::CARD_CODE;
            if (!empty($this->giftCardsData[$datum[$constCardCode]])) {

                $updateGiftCardsData[] = [
                    GiftCardsInterface::CARD_ID         => $this->giftCardsData[$datum[$constCardCode]]->getCardId(),
                    GiftCardsInterface::CARD_CODE       => $datum[$constCardCode],
                    GiftCardsInterface::CUSTOMER_ID     => $datum[GiftCardsInterface::CUSTOMER_ID],
                    GiftCardsInterface::CARD_CURRENCY   => $datum[GiftCardsInterface::CARD_CURRENCY],
                    GiftCardsInterface::CARD_AMOUNT     => $datum[GiftCardsInterface::CARD_AMOUNT],
                    GiftCardsInterface::CARD_BALANCE    => $datum[GiftCardsInterface::CARD_BALANCE],
                    GiftCardsInterface::CARD_STATUS     => $datum[GiftCardsInterface::CARD_STATUS],
                    GiftCardsInterface::CARD_TYPE       => $datum[GiftCardsInterface::CARD_TYPE],
                    GiftCardsInterface::MAIL_FROM       => $datum[GiftCardsInterface::MAIL_FROM],
                    GiftCardsInterface::MAIL_TO         => $datum[GiftCardsInterface::MAIL_TO],
                    GiftCardsInterface::MAIL_TO_EMAIL   => $datum[GiftCardsInterface::MAIL_TO_EMAIL],
                    GiftCardsInterface::MAIL_MESSAGE    => $datum[GiftCardsInterface::MAIL_MESSAGE],
                    GiftCardsInterface::OFFLINE_COUNTRY => $datum[GiftCardsInterface::OFFLINE_COUNTRY],
                    GiftCardsInterface::OFFLINE_STATE   => $datum[GiftCardsInterface::OFFLINE_STATE],
                    GiftCardsInterface::OFFLINE_CITY    => $datum[GiftCardsInterface::OFFLINE_CITY],
                    GiftCardsInterface::OFFLINE_STREET  => $datum[GiftCardsInterface::OFFLINE_STREET],
                    GiftCardsInterface::OFFLINE_ZIP     => $datum[GiftCardsInterface::OFFLINE_ZIP],
                    GiftCardsInterface::OFFLINE_PHONE   => $datum[GiftCardsInterface::OFFLINE_PHONE],
                    GiftCardsInterface::UPDATED_AT      => $this->date->gmtDate(),
                    GiftCardsInterface::EXPIRE_DATE     => $datum[GiftCardsInterface::EXPIRE_DATE],
                    GiftCardsInterface::STORE_ID        => $datum[GiftCardsInterface::STORE_ID],
                    GiftCardsInterface::GROUP_ID        => $datum[GiftCardsInterface::GROUP_ID]
                ];
            }
        }

        return $updateGiftCardsData;
    }

    /**
     * @param array $dataFromCSV
     * @return array
     */
    protected function prepareGiftCardsDataToSave($dataFromCSV)
    {
        $saveGiftCardsData = [];
        foreach ($dataFromCSV as $dataIndex => $datum) {
            if (!empty($datum[GiftCardsInterface::CARD_CODE])
                && !array_key_exists($datum[GiftCardsInterface::CARD_CODE], $this->giftCardsData)) {
                $saveGiftCardsData[] = [
                    GiftCardsInterface::CARD_ID                     => '',
                    GiftCardsInterface::CARD_CODE                   => $datum[GiftCardsInterface::CARD_CODE],
                    GiftCardsInterface::CUSTOMER_ID                 => $datum[GiftCardsInterface::CUSTOMER_ID],
                    GiftCardsInterface::ORDER_ID                    => '0',
                    GiftCardsInterface::PRODUCT_ID                  => '0',
                    GiftCardsInterface::CARD_CURRENCY               => $datum[GiftCardsInterface::CARD_CURRENCY],
                    GiftCardsInterface::CARD_AMOUNT                 => $datum[GiftCardsInterface::CARD_AMOUNT],
                    GiftCardsInterface::CARD_BALANCE                => $datum[GiftCardsInterface::CARD_BALANCE],
                    GiftCardsInterface::CARD_STATUS                 => $datum[GiftCardsInterface::CARD_STATUS],
                    GiftCardsInterface::CARD_TYPE                   => $datum[GiftCardsInterface::CARD_TYPE],
                    GiftCardsInterface::MAIL_FROM                   => $datum[GiftCardsInterface::MAIL_FROM],
                    GiftCardsInterface::MAIL_TO                     => $datum[GiftCardsInterface::MAIL_TO],
                    GiftCardsInterface::MAIL_TO_EMAIL               => $datum[GiftCardsInterface::MAIL_TO_EMAIL],
                    GiftCardsInterface::MAIL_MESSAGE                => $datum[GiftCardsInterface::MAIL_MESSAGE],
                    GiftCardsInterface::OFFLINE_COUNTRY             => $datum[GiftCardsInterface::OFFLINE_COUNTRY],
                    GiftCardsInterface::OFFLINE_STATE               => $datum[GiftCardsInterface::OFFLINE_STATE],
                    GiftCardsInterface::OFFLINE_CITY                => $datum[GiftCardsInterface::OFFLINE_CITY],
                    GiftCardsInterface::OFFLINE_STREET              => $datum[GiftCardsInterface::OFFLINE_STREET],
                    GiftCardsInterface::OFFLINE_ZIP                 => $datum[GiftCardsInterface::OFFLINE_ZIP],
                    GiftCardsInterface::OFFLINE_PHONE               => $datum[GiftCardsInterface::OFFLINE_PHONE],
                    GiftCardsInterface::MAIL_DELIVERY_DATE          => null,
                    GiftCardsInterface::CREATED_TIME                => $datum[GiftCardsInterface::CREATED_TIME],
                    GiftCardsInterface::UPDATED_AT                  => $this->date->gmtDate(),
                    GiftCardsInterface::EXPIRE_DATE                 => $datum[GiftCardsInterface::EXPIRE_DATE],
                    GiftCardsInterface::EXPIRED_EMAIL_SEND          => '0',
                    GiftCardsInterface::EXPIRATION_ALERT_EMAIL_SEND => '0',
                    GiftCardsInterface::STORE_ID                    => $datum[GiftCardsInterface::STORE_ID],
                    GiftCardsInterface::GROUP_ID                    => $datum[GiftCardsInterface::GROUP_ID]
                ];
            }
        }

        return $saveGiftCardsData;
    }

    /**
     * @param array $rawData
     * @return array
     * @throws LocalizedException
     */
    protected function getDataFormCSV($rawData)
    {
        $prepareData = [];

        $delimiter = $this->getUseDelimiter($rawData);
        if ($delimiter != $this->delimiter) {
            throw new LocalizedException(__('Please use comma as delimiter while export in Magento 1.'));
        }

        $tempArray = [];
        foreach ($rawData as $dataIndex => $data) {
            // skip headers
            if ($dataIndex == 0) {
                continue;
            }
            if (is_array($data) && count($data) == self::COUNT_FIELDS_CSV) {
                $tempArray[] = $data;
            } else {
                $tempArray[] = explode($delimiter, $data[0]);
            }
        }
        $rawData = $tempArray;

        foreach ($rawData as $dataIndex => $datum) {
            if (is_array($datum) && count($datum) == self::COUNT_FIELDS_CSV) {
                $prepareData[] = [
                    GiftCardsInterface::CARD_ID         => $datum[0],
                    GiftCardsInterface::CARD_CODE       => empty($datum[1]) ? null : $datum[1],
                    GiftCardsInterface::CARD_AMOUNT     => empty($datum[2]) ? null : $datum[2],
                    GiftCardsInterface::CARD_BALANCE    => empty($datum[3]) ? null : $datum[3],
                    GiftCardsInterface::CARD_CURRENCY   => empty($datum[4]) ? null : $datum[4],
                    GiftCardsInterface::CARD_TYPE       => empty($datum[5]) ? '0' : $datum[5],
                    GiftCardsInterface::CARD_STATUS     => empty($datum[6]) ? '0' : $datum[6],
                    GiftCardsInterface::MAIL_FROM       => empty($datum[7]) ? null : $datum[7],
                    GiftCardsInterface::MAIL_TO         => empty($datum[8]) ? null : $datum[8],
                    GiftCardsInterface::MAIL_TO_EMAIL   => empty($datum[9]) ? null : $datum[9],
                    GiftCardsInterface::MAIL_MESSAGE    => empty($datum[10]) ? null : $datum[10],
                    GiftCardsInterface::OFFLINE_COUNTRY => empty($datum[11]) ? null : $datum[11],
                    GiftCardsInterface::OFFLINE_STATE   => empty($datum[12]) ? null : $datum[12],
                    GiftCardsInterface::OFFLINE_CITY    => empty($datum[13]) ? null : $datum[13],
                    GiftCardsInterface::OFFLINE_STREET  => empty($datum[14]) ? null : $datum[14],
                    GiftCardsInterface::OFFLINE_ZIP     => empty($datum[15]) ? null : $datum[15],
                    GiftCardsInterface::OFFLINE_PHONE   => empty($datum[16]) ? null : $datum[16],
                    GiftCardsInterface::CUSTOMER_ID     => $datum[17],
                    GiftCardsInterface::CREATED_TIME    => empty($datum[18]) ? null : $datum[18],
                    GiftCardsInterface::EXPIRE_DATE     => empty($datum[19]) ? null : $datum[19],
                    GiftCardsInterface::STORE_CODE      => empty($datum[20]) ? null : $datum[20],
                    GiftCardsInterface::GROUP_NAME      => empty($datum[21]) ? null : $datum[21]
                ];
            }
        }

        return $prepareData;
    }

    /**
     * @param array $rawData
     * @return null|string
     */
    protected function getUseDelimiter($rawData)
    {
        if (!empty($rawData) && !empty($rawData[0])) {
            //define correct delimiter by header csv
            $headData = $rawData[0];

            if (is_array($headData) && count($headData) == self::COUNT_FIELDS_CSV) {
                return ',';
            }

            if (strpos($headData[0], ',') !== false && count(explode(",", $headData[0])) == self::COUNT_FIELDS_CSV) {
                return ',';
            }

            if (strpos($headData[0], ';') !== false && count(explode(";", $headData[0])) == self::COUNT_FIELDS_CSV) {
                return ';';
            }
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getAllowedFormatFile()
    {
        return ['text/csv', 'application/vnd.ms-excel'];
    }

    /**
     * @return array
     */
    protected function getStoresCodes()
    {
        $codes                = array();
        $storeManagerDataList = $this->storeManager->getStores();

        foreach ($storeManagerDataList as $key => $store) {
            $codes[$store['store_id']] = $store['code'];
        }

        return $codes;
    }

    /**
     * @return array
     */
    protected function getGroupsNames()
    {
        $groups          = array();
        $groupsNamesList = $this->groupCollection->toOptionArray();

        foreach ($groupsNamesList as $key => $group) {
            $groups[$group['value']] = $group['label'];
        }

        return $groups;
    }
}

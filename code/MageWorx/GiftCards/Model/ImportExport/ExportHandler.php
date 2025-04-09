<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model\ImportExport;

use MageWorx\CustomerPrices\Api\GiftCardsInterface;
use Magento\Framework\DataObject;
use MageWorx\GiftCards\Api\GiftCardsRepositoryInterface;
use MageWorx\GiftCards\Api\GiftCardsOrderRepositoryInterface;
use MageWorx\GiftCards\Api\Data\GiftCardsInterface as GiftCardsDataInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection as GroupCollection;

class ExportHandler implements \MageWorx\GiftCards\Api\ExportHandlerInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $dataObjectFactory;

    /**
     * @var GiftCardsRepositoryInterface
     */
    protected $giftCardsRepository;

    /**
     * @var GiftCardsOrderRepositoryInterface
     */
    protected $giftCardsOrderRepository;

    /**
     * @var array|GiftCardsInterface[]
     */
    protected $giftCards;

    /**
     * @var array|GiftCardsOrdersInterface[]
     */
    protected $giftCardsOrders;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var GroupCollection
     */
    protected $customerGroup;

    /**
     * @var null|array
     */
    protected $associatedStoresIdsToCodes = null;

    /**
     * @var null|array
     */
    protected $associatedGroupIdsToGroupCodes = null;

    /**
     * ExportHandler constructor.
     *
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DataObject\Factory $dataObjectFactory
     * @param GiftCardsRepositoryInterface $giftCardsRepository
     * @param GiftCardsOrderRepositoryInterface $giftCardsOrderRepository
     * @param StoreManagerInterface $storeManager
     * @param GroupCollection $customerGroup
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\DataObject\Factory $dataObjectFactory,
        GiftCardsRepositoryInterface $giftCardsRepository,
        GiftCardsOrderRepositoryInterface $giftCardsOrderRepository,
        StoreManagerInterface $storeManager,
        GroupCollection $customerGroup
    ) {
        $this->searchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->dataObjectFactory        = $dataObjectFactory;
        $this->giftCardsRepository      = $giftCardsRepository;
        $this->giftCardsOrderRepository = $giftCardsOrderRepository;
        $this->storeManager             = $storeManager;
        $this->customerGroup            = $customerGroup;
    }

    /**
     * Get content as a CSV string
     *
     * @param array $ids
     * @return string
     */
    public function getContent(array $ids = [])
    {
        $headers  = $this->getHeaders();
        $template = $this->getStringCsvTemplate($headers);
        // Add header (titles)
        $content[] = $headers->toString($template);
        $giftCards = $this->getGiftCards($ids);

        foreach ($giftCards as $datum) {
            if (!$datum instanceof \Magento\Framework\DataObject) {
                continue;
            }
            $datum->addData(
                [
                    GiftCardsDataInterface::CARD_ID         => $datum->getCardId(),
                    GiftCardsDataInterface::CARD_CODE       => $datum->getCardCode(),
                    GiftCardsDataInterface::CARD_AMOUNT     => $datum->getCardAmount(),
                    GiftCardsDataInterface::CARD_BALANCE    => $datum->getCardBalance(),
                    GiftCardsDataInterface::CARD_CURRENCY   => $datum->getCardCurrency(),
                    GiftCardsDataInterface::CARD_TYPE       => $datum->getCardType(),
                    GiftCardsDataInterface::CARD_STATUS     => $datum->getCardStatus(),
                    GiftCardsDataInterface::MAIL_FROM       => $datum->getMailFrom(),
                    GiftCardsDataInterface::MAIL_TO         => $datum->getMailTo(),
                    GiftCardsDataInterface::MAIL_TO_EMAIL   => $datum->getMailToEmail(),
                    GiftCardsDataInterface::MAIL_MESSAGE    => $datum->getMailMessage(),
                    GiftCardsDataInterface::OFFLINE_COUNTRY => $datum->getOfflineCountry(),
                    GiftCardsDataInterface::OFFLINE_STATE   => $datum->getOfflineState(),
                    GiftCardsDataInterface::OFFLINE_CITY    => $datum->getOfflineCity(),
                    GiftCardsDataInterface::OFFLINE_STREET  => $datum->getOfflineStreet(),
                    GiftCardsDataInterface::OFFLINE_ZIP     => $datum->getOfflineZip(),
                    GiftCardsDataInterface::OFFLINE_PHONE   => $datum->getOfflinePhone(),
                    GiftCardsDataInterface::CUSTOMER_ID     => $datum->getCustomerId(),
                    GiftCardsDataInterface::CREATED_TIME    => $datum->getCreatedTime(),
                    GiftCardsDataInterface::EXPIRE_DATE     => $datum->getExpireDate(),
                    GiftCardsDataInterface::STORE_CODE      => $this->getStoresCode($datum->getStoreId()),
                    GiftCardsDataInterface::GROUP_NAME      => $this->getGroupsName($datum->getCustomerGroupId()),
                ]
            );
            $content[] = $datum->toString($template);
        }

        $contentAsAString = implode("\n", $content);

        return $contentAsAString;
    }

    /**
     * @param array $storesIds
     * @return string
     */
    protected function getStoresCode($storesIds)
    {
        $codesArray = [];
        if (is_null($this->associatedStoresIdsToCodes)) {
            $this->associatedStoresIdsToCodes = $this->getAssociatedStoresIdsToCodes();
        }

        foreach ($storesIds as $storeId) {
            if ($storeId == 0) {
                return GiftCardsDataInterface::ALL;
            }
            $codesArray[$storeId] = $this->associatedStoresIdsToCodes[$storeId];
        }

        if (empty(array_diff($this->associatedStoresIdsToCodes, $codesArray))) {
            return GiftCardsDataInterface::ALL;
        }

        return implode(',', $codesArray);
    }

    /**
     * @return array
     */
    protected function getAssociatedStoresIdsToCodes()
    {
        $prepareDate = [];
        foreach ($this->storeManager->getStores() as $store) {
            $prepareDate[$store->getStoreId()] = $store->getCode();
        }

        return $prepareDate;
    }

    /**
     * @param array $groupsIds
     * @return string
     */
    protected function getGroupsName($groupsIds)
    {
        $groupsArray = [];
        if (is_null($this->associatedGroupIdsToGroupCodes)) {
            $this->associatedGroupIdsToGroupCodes = $this->getAssociatedGroupIdsToGroupCodes();
        }

        foreach ($groupsIds as $groupId) {
            $groupsArray[$groupId] = $this->associatedGroupIdsToGroupCodes[$groupId];
        }

        if (empty(array_diff($this->associatedGroupIdsToGroupCodes, $groupsArray))) {
            return GiftCardsDataInterface::ALL;
        }

        return implode(',', $groupsArray);
    }

    /**
     * @return array
     */
    protected function getAssociatedGroupIdsToGroupCodes()
    {
        $prepareDate    = [];
        $customerGroups = $this->customerGroup->toOptionArray();
        foreach ($customerGroups as $customerGroup) {
            $prepareDate[$customerGroup['value']] = $customerGroup['label'];
        }

        return $prepareDate;
    }

    /**
     * Create data template from headers
     *
     * @param \Magento\Framework\DataObject $headers
     * @return string
     */
    private function getStringCsvTemplate(\Magento\Framework\DataObject $headers)
    {
        $data         = $headers->getData();
        $templateData = [];
        foreach ($data as $propertyKey => $value) {
            $templateData[] = '"{{' . $propertyKey . '}}"';
        }
        $template = implode(',', $templateData);

        return $template;
    }

    /**
     * @param array $ids
     * @return array|GiftCardsInterface[]
     */
    protected function getGiftCards(array $ids = [])
    {
        if (empty($this->giftCards)) {
            if (!empty($ids)) {
                $this->searchCriteriaBuilder->addFilter(
                    GiftCardsDataInterface::CARD_ID,
                    $ids,
                    'in'
                );
            }
            $searchCriteria  = $this->searchCriteriaBuilder->create();
            $this->giftCards = $this->giftCardsRepository
                ->getList($searchCriteria, true)
                ->getItems();
        }

        return $this->giftCards;
    }

    /**
     * @param array $ids
     * @return array|GiftCardsInterface[]
     */
    protected function getGiftCardsOrders(array $ids = [])
    {
        if (empty($this->giftCardsOrders)) {
            if (!empty($ids)) {
                $this->searchCriteriaBuilder->addFilter(
                    GiftCardsDataInterface::CARD_ID,
                    $ids,
                    'in'
                );
            }
            $searchCriteria        = $this->searchCriteriaBuilder->create();
            $this->giftCardsOrders = $this->giftCardsOrderRepository
                ->getList($searchCriteria, true)
                ->getItems();
        }

        return $this->giftCardsOrders;
    }

    /**
     * Get headers for the selected entities
     *
     * @return \Magento\Framework\DataObject
     */
    protected function getHeaders()
    {
        $dataFields = [
            GiftCardsDataInterface::CARD_ID         => __('Card ID'),
            GiftCardsDataInterface::CARD_CODE       => __('Card Code'),
            GiftCardsDataInterface::CARD_AMOUNT     => __('Card Amount'),
            GiftCardsDataInterface::CARD_BALANCE    => __('Card Balance'),
            GiftCardsDataInterface::CARD_CURRENCY   => __('Card Currency'),
            GiftCardsDataInterface::CARD_TYPE       => __('Card Type'),
            GiftCardsDataInterface::CARD_STATUS     => __('Card Status'),
            GiftCardsDataInterface::MAIL_FROM       => __('Mail From'),
            GiftCardsDataInterface::MAIL_TO         => __('Mail To'),
            GiftCardsDataInterface::MAIL_TO_EMAIL   => __('User Email'),
            GiftCardsDataInterface::MAIL_MESSAGE    => __('Message'),
            GiftCardsDataInterface::OFFLINE_COUNTRY => __('Country'),
            GiftCardsDataInterface::OFFLINE_STATE   => __('State'),
            GiftCardsDataInterface::OFFLINE_CITY    => __('City'),
            GiftCardsDataInterface::OFFLINE_STREET  => __('Street'),
            GiftCardsDataInterface::OFFLINE_ZIP     => __('Zip'),
            GiftCardsDataInterface::OFFLINE_PHONE   => __('Phone'),
            GiftCardsDataInterface::CUSTOMER_ID     => __('Customer ID'),
            GiftCardsDataInterface::CREATED_TIME    => __('Created Date'),
            GiftCardsDataInterface::EXPIRE_DATE     => __('Expiration Date'),
            GiftCardsDataInterface::STORE_CODE      => __('Store Code'),
            GiftCardsDataInterface::GROUP_NAME      => __('Group Name'),
        ];

        $dataObject = $this->dataObjectFactory->create($dataFields);

        return $dataObject;
    }
}
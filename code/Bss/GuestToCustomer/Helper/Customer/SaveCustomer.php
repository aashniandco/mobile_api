<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_GuestToCustomer
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GuestToCustomer\Helper\Customer;

use Bss\GuestToCustomer;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\UrlInterface;
use Magento\Customer\Api\CustomerMetadataInterface;

class SaveCustomer
{
    const BSS_CUSTOMER_IS_GUEST = 0;

    /**
     * FrontendUrl
     *
     * @var \Bss\GuestToCustomer\Plugin\FrontendUrl $urlFrontend
     */
    protected $urlFrontend;

    /**
     * Resource Guest
     * @var GuestToCustomer\Model\ResourceModel\Guest
     */
    protected $resourceGuest;

    /**
     * Account Management
     * @var AccountManagement $accountManagement
     */
    protected $accountManagement;

    /**
     * Data Object Helper
     * @var DataObjectHelper $dataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var Data
     */
    protected $helperCustomerData;

    /**
     * @var CustomerMetadataInterface
     */
    protected $customerMetadata;
    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $customerFactory;
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * SaveCustomer constructor.
     *
     * @param GuestToCustomer\Model\ResourceModel\Guest $resourceGuest
     * @param DataObjectHelper $dataObjectHelper
     * @param AccountManagement $accountManagement
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Data $helperCustomerData
     * @param UrlInterface $urlBuilder
     * @param CustomerMetadataInterface $customerMetadata
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $customerFactory
     */
    public function __construct(
        \Bss\GuestToCustomer\Plugin\FrontendUrl $urlFrontend,
        GuestToCustomer\Model\ResourceModel\Guest $resourceGuest,
        DataObjectHelper $dataObjectHelper,
        AccountManagement $accountManagement,
        \Magento\Eav\Model\Config $eavConfig,
        GuestToCustomer\Helper\Customer\Data $helperCustomerData,
        CustomerMetadataInterface $customerMetadata,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $customerFactory
    ) {
        $this->urlFrontend = $urlFrontend;
        $this->eavConfig = $eavConfig;
        $this->customerFactory = $customerFactory;
        $this->customerMetadata = $customerMetadata;
        $this->resourceGuest = $resourceGuest;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->accountManagement = $accountManagement;
        $this->helperCustomerData = $helperCustomerData;
    }

    /**
     * GeneratedPassWord
     *
     * @return string
     * @throws LocalizedException
     */
    public function generatedPassWord()
    {
        $length = 4;
        $mathRandom = $this->helperCustomerData->getMathRanDom();
        $password = $mathRandom->getRandomString($length, Random::CHARS_LOWERS);
        $password .= $mathRandom->getRandomString($length, Random::CHARS_UPPERS);
        $password .= $mathRandom->getRandomString($length, Random::CHARS_DIGITS);
        return $password;
    }

    /**
     * SetAtributeCode
     *
     * @param array $customerData
     * @return mixed
     * @throws LocalizedException
     */
    public function setAtributeCode($customerData)
    {
        $collection = $this->customerFactory->create();
        foreach ($collection as $value) {
            $attributeCode = trim($value->getAttributeCode());
            if (!isset($customerData[$attributeCode])) {
                $type = $value->getFrontendInput();
                $attributeNotAllow = ['disable_auto_group_change', 'website_id', 'store_id'];
                $dateNotAllow = ['created_at', 'rp_token_created_at', 'updated_at'];
                if ((in_array($type, ['select', 'boolean', 'multiselect']))
                    && !in_array($attributeCode, $attributeNotAllow)) {
                    $attribute = $this->eavConfig->getAttribute('customer', $attributeCode);
                    $customerData[$attributeCode] = $this->getCustomerData($attribute, $attributeCode);
                } elseif (($type == 'date') && (!in_array($attributeCode, $dateNotAllow))) {
                    $customerData[$attributeCode] = $this->checkValue($attributeCode, '1970-01-01');
                } elseif ($type == 'text') {
                    $customerData[$attributeCode] = $this->checkValue($attributeCode, '--');
                }
            }
        }
        return $customerData;
    }

    /**$attributeCode
     * @param $attribute
     * @param $attributeCode
     * @return mixed|null
     * @throws LocalizedException
     */
    private function getCustomerData($attribute, $attributeCode)
    {
        if ($attribute) {
            try {
                $options = $attribute->getSource()->getAllOptions();
            } catch (\Exception $exception) {
                $options = [];
            }
            if (!empty($options)) {
                foreach ($options as $option) {
                    $customerData[$attributeCode] = $this->checkValue($attributeCode, $option);
                    break;
                }
            }
        }
        return $customerData[$attributeCode];
    }

    /**
     * CheckValue
     *
     * @param $attribute
     * @param $value
     * @return null
     * @throws LocalizedException
     */
    public function checkValue($attribute, $value)
    {
        return ($this->isRequired($attribute) != false) ? $value : null;
    }

    /**
     * Save Customer
     *
     * From 25/02/2020 don't use this function
     *
     * @param array $customerData
     * @param string $entityTypeCode
     * @return int|null
     */
    public function saveCustomer($customerData = [])
    {
        try {
            $customerData = $this->setAtributeCode($customerData);
            $customer = $this->helperCustomerData->createCustomerInterface();
            $this->dataObjectHelper->populateWithArray(
                $customer,
                $customerData,
                \Magento\Customer\Api\Data\CustomerInterface::class
            );
            $this->helperCustomerData->getRegistry()->unregister('configSendEmail');
            $this->helperCustomerData->getRegistry()->register('configSendEmail', true);
            $idCustomer = $this->accountManagement->createAccount($customer, null)->getId();
            $newCustomer = $this->helperCustomerData->getCustomerFactory()->load($idCustomer);
            $newCustomer->reindex();
            $token = $newCustomer->getRpToken();
            $confirm = $newCustomer->getConfirmation();
            if ($confirm) {
                $newCustomer->setConfirmation(null)->save();
            }
            $urlGet = $this->urlFrontend->getFrontendUrl()->getUrl(
                'customer/account/createPassword/',
                ['_query' =>['id' => $idCustomer, 'token' => $token],'_nosid' => 1]
            );
            $nameCustomer = $newCustomer->getName();
            $storeCustomer = $customerData['store_id'];
            $storeName = $this->helperCustomerData->getHelperObserver()->getStoreManager()->getStore()->getName();
            //Send Email To Customer
            $emailReceiver = $customerData['email'];
            $emailTemplate = $this->helperCustomerData->getHelperObserver()
                ->getHelperConfigAdmin()
                ->getConfigEmailTemplate();
            $templateVar = [
                'nameCustomer' => $nameCustomer,
                'email' => $emailReceiver,
                'urlGet' => $urlGet,
                'nameStore' => $storeName
            ];
            error_log("creating cutomer Email in helper");
            $this->helperCustomerData->getHelperObserver()->getHelperEmail()->sendEmail(
                $emailReceiver,
                $emailTemplate,
                $templateVar,
                $storeCustomer
            );
            error_log("creating cutomer Email in helper 1");
            return $idCustomer;
        } catch (\Exception $exception) {
            $this->helperCustomerData->getHelperObserver()->getLogger()->error($exception->getMessage());
        }
        return null;
    }

    /**
     * Process create customer account and address
     *
     * @param array $customerData
     * @param array $addresses
     * @return \Magento\Customer\Model\Customer|null
     */
    public function processCreateCustomer($addresses, $customerData = [])
    {
        try {
            $customerData = $this->setAtributeCode($customerData);
            $customer = $this->helperCustomerData->createCustomerInterface();
            $this->dataObjectHelper->populateWithArray(
                $customer,
                $customerData,
                \Magento\Customer\Api\Data\CustomerInterface::class
            );
            $this->helperCustomerData->getRegistry()->unregister('configSendEmail');
            $this->helperCustomerData->getRegistry()->register('configSendEmail', true);
            $customer->setAddresses($addresses);
            $idCustomer = $this->accountManagement->createAccount($customer, null)->getId();
            $newCustomer = $this->helperCustomerData->getCustomerFactory()->load($idCustomer);
            $newCustomer->reindex();
            $token = $newCustomer->getRpToken();
            $confirm = $newCustomer->getConfirmation();
            if ($confirm) {
                $newCustomer->setConfirmation(null)->save();
            }
            $urlGet = $this->urlFrontend->getFrontendUrl()->getUrl(
                'customer/account/createPassword/',
                ['_query' =>['id' => $idCustomer, 'token' => $token],'_nosid' => 1]
            );
            $nameCustomer = $newCustomer->getName();
            $storeCustomer = $customerData['store_id'];
            $storeName = $this->helperCustomerData->getHelperObserver()->getStoreManager()->getStore()->getName();
            //Send Email To Customer
            $emailReceiver = $customerData['email'];
            $emailTemplate = $this->helperCustomerData->getHelperObserver()
                ->getHelperConfigAdmin()
                ->getConfigEmailTemplate();
            $templateVar = [
                'nameCustomer' => $nameCustomer,
                'email' => $emailReceiver,
                'urlGet' => $urlGet,
                'nameStore' => $storeName
            ];
            error_log("creating cutomer Email in helper 3");
            $this->helperCustomerData->getHelperObserver()->getHelperEmail()->sendEmail(
                $emailReceiver,
                $emailTemplate,
                $templateVar,
                $storeCustomer
            );
            error_log("creating cutomer Email in helper 4");
            return $newCustomer;
        } catch (\Exception $exception) {
            $this->helperCustomerData->getHelperObserver()->getLogger()->error($exception->getMessage());
        }
        return null;
    }

    /**
     * Save Order By Id
     *
     * @param int $orderId
     * @param int $customerId
     * @param array $customerData
     */
    protected function saveOrderById($orderId, $customerId, $customerData = [])
    {
        try {
            $order = $this->helperCustomerData->createOrder()->load($orderId);
            $order
                ->setCustomerId($customerId)
                ->setCustomerIsGuest(self::BSS_CUSTOMER_IS_GUEST)
                ->setCustomerGroupId($customerData['group_id'])
                ->setCustomerFirstname($customerData['firstname'])
                ->setCustomerLastname($customerData['lastname']);
            $customer = $this->helperCustomerData->getCustomerFactory()->load($customerId);
            //Get Biliing and Shipping address id
            $billingAddressId = $customer->getDefaultBilling();
            $shippingAddressId = $customer->getDefaultShipping();
            if ($order->getBillingAddress()) {
                $order->getBillingAddress()->setCustomerId($customerId);
                $order->getBillingAddress()->setCustomerAddressId($billingAddressId);
            }
            if ($order->getShippingAddress()) {
                $order->getShippingAddress()->setCustomerId($customerId);
                $order->getShippingAddress()->setCustomerAddressId($shippingAddressId);
            }
            $order->save();
        } catch (\Exception $exception) {
            $this->helperCustomerData->getHelperObserver()->getLogger()->error($exception->getMessage());
        }
    }

    /**
     * Save Orders
     *
     * @param int $customerId
     * @param array $customerData
     * @return void
     */
    public function saveOrders($customerId, $customerData = [])
    {
        $orders = $this->helperCustomerData->createOrder()->getCollection()
            ->addFieldToFilter('customer_email', $customerData['email']);
        foreach ($orders as $orderCurrent) {
            $orderId = $orderCurrent->getId();
            $this->saveOrderById($orderId, $customerId, $customerData);
        }
    }

    /**
     * Save Address
     *
     * @param array $data
     * @param int $idCustomer
     * @param bool $type
     * @return void
     */
    public function saveAddress($data, $idCustomer, $type, $asyncaddress = false)
    {
        try {
            $idAddress = $this->resourceGuest->insertAddress($data);
            if ($asyncaddress == false) {
                $this->resourceGuest->updateCustomerAddessDefault($idAddress, $idCustomer, $type);
            } else {
                $this->resourceGuest->updateCustomerBothAddessDefault($idAddress, $idCustomer);
            }

        } catch (\Exception $exception) {
            $this->helperCustomerData->getHelperObserver()->getLogger()->error($exception->getMessage());
        }
    }

    public function saveCustomerAddresses($idCustomer, $addresses)
    {
        $customer = $this->helperCustomerData->getCustomerFactory()->load($idCustomer);
        $customer->setAddresses($addresses);
        $customer->save();
    }

    /**
     * Save one address
     *
     * @param array $data
     * @param int $idCustomer
     * @param bool $type
     * @return void
     */
    public function saveOneAddress($data, $idCustomer)
    {
        try {
            $idAddress = $this->resourceGuest->insertAddress($data);
            $this->resourceGuest->updateCustomerOneAddessDefault($idAddress, $idCustomer);
        } catch (\Exception $exception) {
            $this->helperCustomerData->getHelperObserver()->getLogger()->error($exception->getMessage());
        }
    }

    /**
     * Check require attribute
     *
     * @param string $attribute
     * @return bool
     * @throws LocalizedException
     */
    public function isRequired($attribute)
    {
        return $this->getAttribute($attribute) ?
            (bool)$this->getAttribute($attribute)->isRequired() : false;
    }

    /**
     * GetAttribute
     *
     * @param String $attributeCode
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface|null
     * @throws LocalizedException
     */
    protected function getAttribute($attributeCode)
    {
        try {
            return $this->customerMetadata->getAttributeMetadata($attributeCode);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }
}

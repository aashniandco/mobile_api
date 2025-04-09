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
 * @package    BSS_GuestToCustomer
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GuestToCustomer\Observer;

use Bss\GuestToCustomer;
use Bss\GuestToCustomer\Model\Guest;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;

class GuestSave implements ObserverInterface
{
    const BSS_CUSTOMER_IS_GUEST = 0;
    const BSS_BILLING_ADDRESS_TYPE = true;
    const BSS_SHIPPING_ADDRESS_TYPE = false;
    const BSS_DEFAULT_TRUE = true;
    const BSS_DEFAULT_FALSE = false;

    /**
     * Guest
     * @var Guest
     */
    protected $guest;

    /**
     * Customer
     * @var Customer
     */
    protected $customers;

    /**
     * Resource Guest
     * @var GuestToCustomer\Model\ResourceModel\Guest
     */
    protected $resourceGuest;

    /**
     * Helper Customer
     * @var GuestToCustomer\Helper\Customer\SaveCustomer
     */
    protected $helperCustomer;

    /**
     * @var GuestToCustomer\Helper\Observer\Helper
     */
    protected $helperObserver;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    protected $helperConvertAddress;

    /**
     * GuestSave constructor.
     * @param SerializerInterface $serializer
     * @param GuestToCustomer\Model\ResourceModel\Guest $resourceGuest
     * @param Guest $guest
     * @param Customer $customers
     * @param GuestToCustomer\Helper\Customer\SaveCustomer $helperCustomer
     * @param GuestToCustomer\Helper\Observer\Helper $helperObserver
     */
    public function __construct(
        SerializerInterface $serializer,
        GuestToCustomer\Model\ResourceModel\Guest $resourceGuest,
        Guest $guest,
        Customer $customers,
        GuestToCustomer\Helper\Customer\SaveCustomer $helperCustomer,
        \Bss\GuestToCustomer\Helper\Observer\Helper $helperObserver,
        \Bss\GuestToCustomer\Helper\Customer\Address $helperConvertAddress
    ) {
        $this->serializer = $serializer;
        $this->resourceGuest = $resourceGuest;
        $this->customers = $customers;
        $this->guest = $guest;
        $this->helperCustomer = $helperCustomer;
        $this->helperObserver = $helperObserver;
        $this->helperConvertAddress = $helperConvertAddress;
    }

    /**
     * Isset Email Guest
     *
     * @param string $emailGuest
     * @return boolean
     */
    protected function issetEmailGuest($emailGuest)
    {
        $check = false;
        if ($this->resourceGuest->existEmailGuest($emailGuest)) {
            $check = true;
        }
        return $check;
    }

    /**
     * Get Auto Convert
     *
     * @return boolean
     */
    protected function isAutoConvert()
    {
        return $this->helperObserver->getHelperConfigAdmin()->getConfigAutoConvert();
    }

    /**
     * Get Auto Convert
     *
     * @return int $groupId
     */
    protected function getConfigGroupId()
    {
        return $this->helperObserver->getHelperConfigAdmin()->getConfigCustomerGroup();
    }

    /**
     * Delete Guest If Isset
     *
     * @param string $emailGuest
     * @return void
     */
    protected function deleteGuestIfIsset($emailGuest)
    {
        if ($this->issetEmailGuest($emailGuest)) {
            $where = [
                'email = ?' => (string)$emailGuest
            ];
            $this->resourceGuest->deleteGuest($where);
        }
    }

    /**
     * Check Email Customer
     *
     * @param string $customerEmail
     * @param id $websiteId
     * @return bool
     */
    protected function checkEmailCustomer($customerEmail, $websiteId)
    {
        $this->customers->setWebsiteId($websiteId);
        $customer = $this->customers->loadByEmail($customerEmail);

        if ($customer->getData() != null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * GetCustomerData
     *
     * @param array $arrBillingAddress
     * @return array
     */
    protected function getCustomerData($arrBillingAddress = [])
    {
        $storeManager = $this->helperObserver->getStoreManager();
        $websiteId = $storeManager->getWebsite()->getWebsiteId();
        $storeId = $storeManager->getStore()->getId();
        $groupId = $this->getConfigGroupId();
        $customerData =
            [
                "website_id" => $websiteId,
                'store_id' => $storeId,
                "group_id" => $groupId,
                "disable_auto_group_change" => 0,
                "prefix" => $arrBillingAddress['prefix'],
                "firstname" => $arrBillingAddress['firstname'],
                "lastname" => $arrBillingAddress['lastname'],
                "suffix" => $arrBillingAddress['suffix'],
                "email" => $arrBillingAddress['email'],
                "fax" => $arrBillingAddress['fax'],
                'telephone' => $arrBillingAddress['telephone'],
                'company' => $arrBillingAddress['company'],
                "sendemail_store_id" => 1
            ];
        return $customerData;
    }

    /**
     * Save Address Customer
     *
     * @param array $addressData
     * @param int $idCustomer
     * @param bool $type
     * @return void
     */
    private function saveAddressCustomer($addressData, $idCustomer, $type, $asyncaddress = false)
    {
        $bind = [
            'parent_id' => $idCustomer,
            'firstname' => $addressData['firstname'],
            'middlename' => $addressData['middlename'],
            'lastname' => $addressData['lastname'],
            'country_id' => $addressData['country_id'],
            'postcode' => $addressData['postcode'],
            'city' => $addressData['city'],
            'telephone' => $addressData['telephone'],
            'company' => $addressData['company'],
            'street' => $addressData['street'],
            'region' => $addressData['region'],
            'region_id' => $addressData['region_id'],
            "suffix" => $addressData['suffix'],
            "fax" => $addressData['fax'],
            "prefix" => $addressData['prefix']
        ];

        $this->helperCustomer->saveAddress($bind, $idCustomer, $type, $asyncaddress);
    }

    /**
     * Save Orders Customer
     *
     * @param bool $config
     * @param int $customerId
     * @param array $customerData
     * @return void
     */
    private function saveOrdersCustomer($config, $customerId, $customerData = [])
    {
        if ($config) {
            $this->helperCustomer->saveOrders($customerId, $customerData);
        }
    }

    /**
     * Set Guest To Customer Type
     *
     * @return void
     */
    protected function setGuestToCustomerType()
    {
        $this->helperObserver->getCoreSession()->start();
        $this->helperObserver->getCoreSession()->setData('bss_guest_to_customer_type', 1);
    }

    /**
     * Remove Extension Attributes
     *
     * @param array $arrShippingAddress
     * @return array
     */
    protected function removeExtensionAttributes($arrShippingAddress)
    {
        if (isset($arrShippingAddress['extension_attributes'])) {
            unset($arrShippingAddress['extension_attributes']);
        }
        return $arrShippingAddress;
    }

    /**
     * Is async address
     *
     * @param array $shippingAddress
     * @param array $billingAddress
     * @return bool
     */
    protected function isAsyncAddress($shippingAddress, $billingAddress)
    {
        unset($shippingAddress['address_type']);
        unset($billingAddress['address_type']);
        unset($shippingAddress['quote_address_id']);
        unset($billingAddress['quote_address_id']);
        if (!empty(array_udiff($shippingAddress, $billingAddress, function ($shippingAddress, $billingAddress) {
            $billingAddress = $this->removeExtensionAttributes($billingAddress);
            $shippingAddress = $this->removeExtensionAttributes($shippingAddress);
            if ($billingAddress === $shippingAddress) {
                return 0;
            } elseif ($shippingAddress > $billingAddress) {
                return 1;
            } else {
                return -1;
            }
        }))) {
            $sameAddress = false;
        } else {
            $sameAddress = true;
        }
        if ($sameAddress && $this->helperObserver->getHelperConfigAdmin()->isAsyncAddress()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Save Billing address, shipping address
     *
     * @param int $idCustomer
     * @param array $arrShippingAddress
     * @param array $arrBillingAddress
     */
    protected function saveCustomerAddresses($idCustomer, $arrShippingAddress, $arrBillingAddress)
    {
        if (!$this->isAsyncAddress($arrShippingAddress, $arrBillingAddress)) {
            if (!empty($arrShippingAddress)) {
                $this->saveAddressCustomer(
                    $arrShippingAddress,
                    $idCustomer,
                    self::BSS_SHIPPING_ADDRESS_TYPE
                );
            }
            if (!empty($arrBillingAddress)) {
                $this->saveAddressCustomer(
                    $arrBillingAddress,
                    $idCustomer,
                    self::BSS_BILLING_ADDRESS_TYPE
                );
            }
        } else {
            if (isset($arrShippingAddress['address_type'])) {
                if (!empty($arrShippingAddress)) {
                    $this->saveAddressCustomer(
                        $arrShippingAddress,
                        $idCustomer,
                        self::BSS_SHIPPING_ADDRESS_TYPE,
                        true
                    );
                }
            }
        }
    }

    /**
     * Handle Save Guest
     *
     * @param string $guestInfo
     */
    protected function handleSaveGuest($guestInfo)
    {
        if (!$this->issetEmailGuest($guestInfo['email'])) {
            //Save DataBase
            $this->guest->setData($guestInfo);
            $this->guest->save();
        } else {
            //Update Database
            $where = [
                'email = ?' => (string)$guestInfo['email']
            ];
            $this->resourceGuest->updateGuest($guestInfo, $where);
        }
    }

    /**
     * HandleAutoConvert
     *
     * @param Object $observer
     * @param string $emailGuest
     * @param array $arrShippingAddress
     * @param string $arrBillingAddress
     */
    protected function handleAutoConvert(&$observer, $emailGuest, $arrShippingAddress, $arrBillingAddress)
    {
        // Set array data customer
        $customerData = $this->getCustomerData($arrBillingAddress);
        $addresses = $this->helperConvertAddress->processAddressCustomer($arrShippingAddress, $arrBillingAddress);
        $customer = $this->helperCustomer->processCreateCustomer($addresses, $customerData);
        $idCustomer = $customer->getId();
        if ($idCustomer) {
            /**
                Fix customer data customer group when config
                Stores->Configuration->Customers->Customer Configuration
                ->Create New Account Options->Enable Automatic Assignment to Customer Group (Yes)
            */
            $customerData['group_id'] = $customer->getGroupId();

            //Set Order
            $observer->getEvent()->getOrder()
                ->setCustomerId($idCustomer)
                ->setCustomerEmail($emailGuest)
                ->setCustomerFirstname($customerData['firstname'])
                ->setCustomerLastname($customerData['lastname'])
                ->setCustomerIsGuest(self::BSS_CUSTOMER_IS_GUEST)
                ->setCustomerGroupId($customer->getGroupId());

            //Set order address
            $this->setOrderAddress($idCustomer, $observer);

            //Delete guest if isset
            $this->deleteGuestIfIsset($emailGuest);

            $config = $this->helperObserver->getHelperConfigAdmin()->getConfigAssignOrders();
            $this->saveOrdersCustomer($config, $idCustomer, $customerData);
        }
    }

    /**
     * Execute
     *
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $this->setGuestToCustomerType();
        //Get order
        $order = $observer->getEvent()->getOrder();
        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        $websiteId = $this->helperObserver->getStoreManager()->getWebsite()->getWebsiteId();
        $emailGuest =  $billingAddress->getData('email');
        if ($order->getCustomerIsGuest() &&
            $this->checkEmailCustomer($emailGuest, $websiteId)
        ) {
            $observer->getEvent()->getOrder(
            )->setCustomerId(
                $this->customers->getId()
            )->setCustomerGroupId(
                (int)$this->customers->getGroupId()
            )->setCustomerIsGuest(
                self::BSS_CUSTOMER_IS_GUEST
            )->setCustomerFirstname(
                $this->customers->getName()
            );
            return $this;
        }
        if ($order->getCustomerIsGuest() &&
            $this->helperObserver->getHelperConfigAdmin()->getConfigEnableModule() &&
            !$this->checkEmailCustomer($emailGuest, $websiteId)
        ) {
            try {
                $arrShippingAddress = $shippingAddress ? $shippingAddress->getData() : [];
                $arrBillingAddress = $billingAddress->getData();
                if (!is_array($arrShippingAddress) || empty($arrShippingAddress)) {
                    $arrShippingAddress = $arrBillingAddress;
                }
                $this->helperObserver->getHelperConfigAdmin()->getConfigTelephoneRequire($arrShippingAddress);
                $this->helperObserver->getHelperConfigAdmin()->getConfigTelephoneRequire($arrBillingAddress);
                $arrShippingAddress = $this->removeExtensionAttributes($arrShippingAddress);
                if ($this->isAutoConvert()) {
                    $this->handleAutoConvert($observer, $emailGuest, $arrShippingAddress, $arrBillingAddress);
                } else {
                    $storeId = $this->helperObserver->getStoreManager()->getStore()->getId();
                    $guestInfo = [
                        'email' =>  $emailGuest,
                        'first_name' => $arrBillingAddress['firstname'],
                        'last_name' => $arrBillingAddress['lastname'],
                        'website_id' => $websiteId,
                        'store_id' => $storeId,
                        'shipping_address' => $this->serializer->serialize($arrShippingAddress),
                        'billing_address' => $this->serializer->serialize($arrBillingAddress)
                    ];
                    $this->handleSaveGuest($guestInfo);
                }
            } catch (\Exception $exception) {
                $this->helperObserver->getLogger()->error($exception->getMessage());
            }
        }
        $this->helperObserver->getCoreSession()->setData('bss_guest_to_customer_type', 0);
    }

    /**
     * SetOrderAddress
     *
     * @param $idCustomer
     * @param $observer
     */
    protected function setOrderAddress($idCustomer, $observer)
    {
        $idDefaultBilling = $this->customers->load($idCustomer)->getDefaultBilling();
        $idDefaultShipping = $this->customers->load($idCustomer)->getDefaultShipping();
        $order = $observer->getEvent()->getOrder();
        if ($order->getBillingAddress()) {
            $order->getBillingAddress()->setCustomerId($idCustomer);
            $order->getBillingAddress()->setCustomerAddressId($idDefaultBilling);
        }
        if ($order->getShippingAddress()) {
            $order->getShippingAddress()->setCustomerId($idCustomer);
            $order->getShippingAddress()->setCustomerAddressId($idDefaultShipping);
        }
    }
}

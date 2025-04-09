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
namespace Bss\GuestToCustomer\Helper\Customer;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Address extends AbstractHelper
{
    /**
     * @var RegionInterfaceFactory
     */
    protected $regionDataFactory;

     /**
      * @var AddressInterfaceFactory
      */
    protected $addressDataFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Bss\GuestToCustomer\Helper\Observer\Helper
     */
    protected $helperObserver;

    /**
     * Address constructor.
     * @param Context $context
     * @param RegionInterfaceFactory $regionDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Bss\GuestToCustomer\Helper\Observer\Helper $helperObserver
     */
    public function __construct(
        Context $context,
        RegionInterfaceFactory $regionDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Bss\GuestToCustomer\Helper\Observer\Helper $helperObserver
    ) {
        parent::__construct($context);
        $this->regionDataFactory = $regionDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->helperObserver = $helperObserver;
    }

    /**
     * Process shipping and billing address for customer
     *
     * @param array $arrShippingAddress
     * @param array $arrBillingAddress
     * @return array
     */
    public function processAddressCustomer($arrShippingAddress, $arrBillingAddress)
    {
        $addresses = [];
        if (!$this->isAsyncAddress($arrShippingAddress, $arrBillingAddress)) {
            if (!empty($arrShippingAddress)) {
                $addressData = $this->getAddressData($arrShippingAddress);
                $shippingAddress = $this->extractAddress($addressData, true, false);
                $addresses[] = $shippingAddress;
            }
            if (!empty($arrBillingAddress)) {
                $addressData = $this->getAddressData($arrBillingAddress);
                $billingAddress = $this->extractAddress($addressData, false, true);
                $addresses[] = $billingAddress;
            }
        } else {
            if (isset($arrShippingAddress['address_type'])) {
                if (!empty($arrShippingAddress)) {
                    $addressData = $this->getAddressData($arrShippingAddress);
                    $address = $this->extractAddress($addressData, true, true);
                    $addresses[] = $address;
                }
            }
        }
        if ($addresses === null) {
            return [];
        }
        return $addresses;
    }

    /**
     * @param $arrShippingAddress
     * @param $arrBillingAddress
     * @return array
     */
    public function processAddressesCustomer($arrShippingAddress, $arrBillingAddress)
    {
        $addresses = [];
        if ($this->isAddressImported($arrBillingAddress, $arrShippingAddress)) {
            // Case: import from old orders.
            foreach ($arrBillingAddress as $key => $item) {
                $billing = $arrBillingAddress[$key];
                $shipping = $arrShippingAddress[$key];
                $addr = $this->processAddressCustomer($shipping, $billing);
                foreach ($addr as $value) {
                    $addresses[] = $value;
                }
            }
            return $addresses;
        }
        return $this->processAddressCustomer($arrShippingAddress, $arrBillingAddress);
    }

    /**
     * If Address is imported
     *
     * @param array $arrBillingAddress
     * @param array $arrShippingAddress
     * @return bool
     */
    public function isAddressImported($arrBillingAddress, $arrShippingAddress)
    {
        if (is_array($arrBillingAddress) && isset($arrBillingAddress[0]) &&
            is_array($arrShippingAddress) && isset($arrShippingAddress[0])) {
            return true;
        }
        return false;
    }

    /**
     * Get data to sync address data
     *
     * @param array $dataAddress
     * @return array
     */
    protected function getAddressData($dataAddress)
    {
        $addressKeys = [
            'firstname',
            'middlename',
            'lastname',
            'country_id',
            'postcode',
            'city',
            'telephone',
            'company',
            'street',
            'region',
            'region_id',
            'suffix',
            'fax',
            'prefix',
            'vat_id',
            'country_id'
        ];
        $addressData = [];
        foreach ($addressKeys as $addressKey) {
            if ($addressKey != 'street') {
                $addressData[$addressKey] = $this->getValueElementArray($dataAddress, $addressKey);
            } else {
                $addressData[$addressKey] = [$this->getValueElementArray($dataAddress, $addressKey)];
            }
        }
        return $addressData;
    }

    /**
     * @param array $arr
     * @param string $key
     * @param string $type
     * @return string
     */
    public function getValueElementArray(
        $arr,
        $key,
        $type = 'string'
    ) {
        // type = string|int|array|boolean
        $finalValue = '';
        switch ($type) {
            case 'string':
                $finalValue = '';
                break;
            case 'int':
                $finalValue = 0;
                break;
            case 'array':
                $finalValue = [];
                break;
            case 'boolean':
                $finalValue = false;
                break;
            default:
                $finalValue = '';
                break;
        }
        return isset($arr[$key]) ? $arr[$key] : $finalValue;
    }

    /**
     * Extract address to object from array address data
     *
     * @param array $addressData
     * @param bool $isDefaultShipping
     * @param bool $isDefaultBilling
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    protected function extractAddress($addressData, $isDefaultShipping, $isDefaultBilling)
    {
        $regionDataObject = $this->regionDataFactory->create();
        if (isset($addressData['region_id']) && $addressData['region_id']) {
            $regionDataObject->setRegionId($addressData['region_id']);
        }
        if (isset($addressData['region']) && $addressData['region']) {
            $regionDataObject->setRegion($addressData['region']);
        }
        unset($addressData['region']);
        unset($addressData['region_id']);
        $addressDataObject = $this->addressDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            $addressData,
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        $addressDataObject->setRegion($regionDataObject);

        $addressDataObject->setIsDefaultBilling(
            $isDefaultShipping
        )->setIsDefaultShipping(
            $isDefaultBilling
        );
        return $addressDataObject;
    }

    /**
     * @param $address
     * @return array
     */
    protected function removeExtensionAttributes($address)
    {
        if (isset($address['extension_attributes'])) {
            unset($address['extension_attributes']);
        }
        return $address;
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
        if (!empty(
        array_diff(
            $shippingAddress,
            $billingAddress
        )
        )) {
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
}

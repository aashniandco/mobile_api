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
namespace Bss\GuestToCustomer\Model;

use Magento\Framework\Model\AbstractModel;

class Guest extends AbstractModel
{

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\GuestToCustomer\Model\ResourceModel\Guest::class);
    }

    /**
     * Get Id Guest
     *
     * @return int
     */
    public function getIdImage()
    {
        return $this->getData("guest_id");
    }

    /**
     * Set First Name
     *
     * @param string $firstName
     * @return void
     */
    public function setFirstName($firstName)
    {
        $this->setData("first_name", $firstName);
    }

    /**
     * Get First Name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->getData("first_name");
    }

    /**
     * Set last name
     *
     * @param string $lastName
     * @return void
     */
    public function setLastName($lastName)
    {
        $this->setData("last_name", $lastName);
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->getData("last_name");
    }

    /**
     * Set Email Guest
     *
     * @param email $email
     * @return void
     */
    public function setEmailGuest($email)
    {
        $this->setData("email", $email);
    }

    /**
     * Get Email Guest
     *
     * @return string
     */
    public function getEmailGuest()
    {
        return $this->getData("email");
    }

    /**
     * Set Store Id
     *
     * @param Int $storeId
     * @return void
     */
    public function setStoreId($storeId)
    {
        $this->setData('store_id', $storeId);
    }

    /**
     * Get Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    /**
     * Set Shipping Address
     *
     * @param string $shippingAddress
     * @return void
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->setData("shipping_address", $shippingAddress);
    }

    /**
     * Get Shipping Address
     *
     * @return string
     */
    public function getShippingAddress()
    {
        return $this->getData("shipping_address");
    }

    /**
     * Set Billing Address
     *
     * @param string $billingAddress
     * @return void
     */
    public function setBillingAddress($billingAddress)
    {
        $this->setData("billing_address", $billingAddress);
    }

    /**
     * Get Billing Address
     *
     * @return string
     */
    public function getBillingAddress()
    {
        return $this->getData("billing_address");
    }

    public function importGuest($data)
    {
        return $this->_getResource()->importGuest($data);
    }
}

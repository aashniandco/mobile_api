<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mec\PurchaseOrder\Model\Data;

use Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface;

class PurchaseOrder extends \Magento\Framework\Api\AbstractExtensibleObject implements PurchaseOrderInterface
{

    /**
     * Get purchase_order_id
     * @return string|null
     */
    public function getPurchaseOrderId()
    {
        return $this->_get(self::PURCHASE_ORDER_ID);
    }

    /**
     * Set purchase_order_id
     * @param string $purchaseOrderId
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setPurchaseOrderId($purchaseOrderId)
    {
        return $this->setData(self::PURCHASE_ORDER_ID, $purchaseOrderId);
    }

    /**
     * Get po_number
     * @return string|null
     */
    public function getPoNumber()
    {
        return $this->_get(self::PO_NUMBER);
    }

    /**
     * Set po_number
     * @param string $poNumber
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setPoNumber($poNumber)
    {
        return $this->setData(self::PO_NUMBER, $poNumber);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Mec\PurchaseOrder\Api\Data\PurchaseOrderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Mec\PurchaseOrder\Api\Data\PurchaseOrderExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * Set order_id
     * @param string $orderId
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get gst_number
     * @return string|null
     */
    public function getGstNumber()
    {
        return $this->_get(self::GST_NUMBER);
    }

    /**
     * Set gst_number
     * @param string $gstNumber
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setGstNumber($gstNumber)
    {
        return $this->setData(self::GST_NUMBER, $gstNumber);
    }

    /**
     * Get vendor
     * @return string|null
     */
    public function getVendor()
    {
        return $this->_get(self::VENDOR);
    }

    /**
     * Set vendor
     * @param string $vendor
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setVendor($vendor)
    {
        return $this->setData(self::VENDOR, $vendor);
    }

    /**
     * Get merchandiser_details
     * @return string|null
     */
    public function getMerchandiserDetails()
    {
        return $this->_get(self::MERCHANDISER_DETAILS);
    }

    /**
     * Set merchandiser_details
     * @param string $merchandiserDetails
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setMerchandiserDetails($merchandiserDetails)
    {
        return $this->setData(self::MERCHANDISER_DETAILS, $merchandiserDetails);
    }

    /**
     * Get ship_to
     * @return string|null
     */
    public function getShipTo()
    {
        return $this->_get(self::SHIP_TO);
    }

    /**
     * Set ship_to
     * @param string $shipTo
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setShipTo($shipTo)
    {
        return $this->setData(self::SHIP_TO, $shipTo);
    }

    /**
     * Get serial_no
     * @return string|null
     */
    public function getSerialNo()
    {
        return $this->_get(self::SERIAL_NO);
    }

    /**
     * Set serial_no
     * @param string $serialNo
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setSerialNo($serialNo)
    {
        return $this->setData(self::SERIAL_NO, $serialNo);
    }

    /**
     * Get order_increment_id
     * @return string|null
     */
    public function getOrderIncrementId()
    {
        return $this->_get(self::ORDER_INCREMENT_ID);
    }

    /**
     * Set order_increment_id
     * @param string $orderIncrementId
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setOrderIncrementId($orderIncrementId)
    {
        return $this->setData(self::ORDER_INCREMENT_ID, $orderIncrementId);
    }

    /**
     * Get sku
     * @return string|null
     */
    public function getSku()
    {
        return $this->_get(self::SKU);
    }

    /**
     * Set sku
     * @param string $sku
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * Get vendor_code
     * @return string|null
     */
    public function getVendorCode()
    {
        return $this->_get(self::VENDOR_CODE);
    }

    /**
     * Set vendor_code
     * @param string $vendorCode
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setVendorCode($vendorCode)
    {
        return $this->setData(self::VENDOR_CODE, $vendorCode);
    }

    /**
     * Get description
     * @return string|null
     */
    public function getDescription()
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * Set description
     * @param string $description
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }
}


<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mec\PurchaseOrder\Api\Data;

interface PurchaseOrderInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const PURCHASE_ORDER_ID = 'purchase_order_id';
    const SHIP_TO = 'ship_to';
    const MERCHANDISER_DETAILS = 'merchandiser_details';
    const SERIAL_NO = 'serial_no';
    const SKU = 'sku';
    const VENDOR = 'vendor';
    const VENDOR_CODE = 'vendor_code';
    const ORDER_ID = 'order_id';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const GST_NUMBER = 'gst_number';
    const DESCRIPTION = 'description';
    const PO_NUMBER = 'po_number';

    /**
     * Get purchase_order_id
     * @return string|null
     */
    public function getPurchaseOrderId();

    /**
     * Set purchase_order_id
     * @param string $purchaseOrderId
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setPurchaseOrderId($purchaseOrderId);

    /**
     * Get po_number
     * @return string|null
     */
    public function getPoNumber();

    /**
     * Set po_number
     * @param string $poNumber
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setPoNumber($poNumber);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Mec\PurchaseOrder\Api\Data\PurchaseOrderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Mec\PurchaseOrder\Api\Data\PurchaseOrderExtensionInterface $extensionAttributes
    );

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set order_id
     * @param string $orderId
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setOrderId($orderId);

    /**
     * Get gst_number
     * @return string|null
     */
    public function getGstNumber();

    /**
     * Set gst_number
     * @param string $gstNumber
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setGstNumber($gstNumber);

    /**
     * Get vendor
     * @return string|null
     */
    public function getVendor();

    /**
     * Set vendor
     * @param string $vendor
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setVendor($vendor);

    /**
     * Get merchandiser_details
     * @return string|null
     */
    public function getMerchandiserDetails();

    /**
     * Set merchandiser_details
     * @param string $merchandiserDetails
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setMerchandiserDetails($merchandiserDetails);

    /**
     * Get ship_to
     * @return string|null
     */
    public function getShipTo();

    /**
     * Set ship_to
     * @param string $shipTo
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setShipTo($shipTo);

    /**
     * Get serial_no
     * @return string|null
     */
    public function getSerialNo();

    /**
     * Set serial_no
     * @param string $serialNo
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setSerialNo($serialNo);

    /**
     * Get order_increment_id
     * @return string|null
     */
    public function getOrderIncrementId();

    /**
     * Set order_increment_id
     * @param string $orderIncrementId
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setOrderIncrementId($orderIncrementId);

    /**
     * Get sku
     * @return string|null
     */
    public function getSku();

    /**
     * Set sku
     * @param string $sku
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setSku($sku);

    /**
     * Get vendor_code
     * @return string|null
     */
    public function getVendorCode();

    /**
     * Set vendor_code
     * @param string $vendorCode
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setVendorCode($vendorCode);

    /**
     * Get description
     * @return string|null
     */
    public function getDescription();

    /**
     * Set description
     * @param string $description
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    public function setDescription($description);
}


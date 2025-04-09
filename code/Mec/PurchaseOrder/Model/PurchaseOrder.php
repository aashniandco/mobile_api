<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mec\PurchaseOrder\Model;

use Magento\Framework\Api\DataObjectHelper;
use Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface;
use Mec\PurchaseOrder\Api\Data\PurchaseOrderInterfaceFactory;

class PurchaseOrder extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'mec_purchaseorder_purchase_order';
    protected $dataObjectHelper;

    protected $purchase_orderDataFactory;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param PurchaseOrderInterfaceFactory $purchase_orderDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Mec\PurchaseOrder\Model\ResourceModel\PurchaseOrder $resource
     * @param \Mec\PurchaseOrder\Model\ResourceModel\PurchaseOrder\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        PurchaseOrderInterfaceFactory $purchase_orderDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Mec\PurchaseOrder\Model\ResourceModel\PurchaseOrder $resource,
        \Mec\PurchaseOrder\Model\ResourceModel\PurchaseOrder\Collection $resourceCollection,
        array $data = []
    ) {
        $this->purchase_orderDataFactory = $purchase_orderDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve purchase_order model with purchase_order data
     * @return PurchaseOrderInterface
     */
    public function getDataModel()
    {
        $purchase_orderData = $this->getData();
        
        $purchase_orderDataObject = $this->purchase_orderDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $purchase_orderDataObject,
            $purchase_orderData,
            PurchaseOrderInterface::class
        );
        
        return $purchase_orderDataObject;
    }
}


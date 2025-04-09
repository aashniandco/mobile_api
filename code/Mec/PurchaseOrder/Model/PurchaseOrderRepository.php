<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mec\PurchaseOrder\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Mec\PurchaseOrder\Api\Data\PurchaseOrderInterfaceFactory;
use Mec\PurchaseOrder\Api\Data\PurchaseOrderSearchResultsInterfaceFactory;
use Mec\PurchaseOrder\Api\PurchaseOrderRepositoryInterface;
use Mec\PurchaseOrder\Model\ResourceModel\PurchaseOrder as ResourcePurchaseOrder;
use Mec\PurchaseOrder\Model\ResourceModel\PurchaseOrder\CollectionFactory as PurchaseOrderCollectionFactory;

class PurchaseOrderRepository implements PurchaseOrderRepositoryInterface
{

    protected $purchaseOrderFactory;

    protected $resource;

    protected $extensibleDataObjectConverter;
    protected $searchResultsFactory;

    protected $purchaseOrderCollectionFactory;

    private $storeManager;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $extensionAttributesJoinProcessor;

    private $collectionProcessor;

    protected $dataPurchaseOrderFactory;


    /**
     * @param ResourcePurchaseOrder $resource
     * @param PurchaseOrderFactory $purchaseOrderFactory
     * @param PurchaseOrderInterfaceFactory $dataPurchaseOrderFactory
     * @param PurchaseOrderCollectionFactory $purchaseOrderCollectionFactory
     * @param PurchaseOrderSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourcePurchaseOrder $resource,
        PurchaseOrderFactory $purchaseOrderFactory,
        PurchaseOrderInterfaceFactory $dataPurchaseOrderFactory,
        PurchaseOrderCollectionFactory $purchaseOrderCollectionFactory,
        PurchaseOrderSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->purchaseOrderFactory = $purchaseOrderFactory;
        $this->purchaseOrderCollectionFactory = $purchaseOrderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPurchaseOrderFactory = $dataPurchaseOrderFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface $purchaseOrder
    ) {
        /* if (empty($purchaseOrder->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $purchaseOrder->setStoreId($storeId);
        } */
        
        $purchaseOrderData = $this->extensibleDataObjectConverter->toNestedArray(
            $purchaseOrder,
            [],
            \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface::class
        );
        
        $purchaseOrderModel = $this->purchaseOrderFactory->create()->setData($purchaseOrderData);
        
        try {
            $this->resource->save($purchaseOrderModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the purchaseOrder: %1',
                $exception->getMessage()
            ));
        }
        return $purchaseOrderModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($purchaseOrderId)
    {
        $purchaseOrder = $this->purchaseOrderFactory->create();
        $this->resource->load($purchaseOrder, $purchaseOrderId);
        if (!$purchaseOrder->getId()) {
            throw new NoSuchEntityException(__('purchase_order with id "%1" does not exist.', $purchaseOrderId));
        }
        return $purchaseOrder->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->purchaseOrderCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface::class
        );
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface $purchaseOrder
    ) {
        try {
            $purchaseOrderModel = $this->purchaseOrderFactory->create();
            $this->resource->load($purchaseOrderModel, $purchaseOrder->getPurchaseOrderId());
            $this->resource->delete($purchaseOrderModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the purchase_order: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($purchaseOrderId)
    {
        return $this->delete($this->get($purchaseOrderId));
    }
}


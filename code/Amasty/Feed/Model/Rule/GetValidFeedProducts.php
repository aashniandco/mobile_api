<?php

declare(strict_types=1);

namespace Amasty\Feed\Model\Rule;

use Amasty\Feed\Model\Feed;
use Amasty\Feed\Model\InventoryResolver;
use Amasty\Feed\Model\Rule\Condition\Sql\Builder;
use Amasty\Feed\Model\ValidProduct\ResourceModel\ValidProduct;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\DB\Select;

class GetValidFeedProducts
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var array
     */
    private $productIds = [];

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var Builder
     */
    protected $sqlBuilder;

    /**
     * @var InventoryResolver
     */
    private $inventoryResolver;

    public function __construct(
        RuleFactory $ruleFactory,
        CollectionFactory $productCollectionFactory,
        Builder $sqlBuilder,
        InventoryResolver $inventoryResolver
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->ruleFactory = $ruleFactory;
        $this->sqlBuilder = $sqlBuilder;
        $this->inventoryResolver = $inventoryResolver;
    }

    public function execute(Feed $model, array $ids = []): void
    {
        $rule = $this->ruleFactory->create();
        $rule->setConditionsSerialized($model->getConditionsSerialized());
        $rule->setStoreId($model->getStoreId());
        $model->setRule($rule);
        $this->updateIndex($model, $ids);
    }

    public function updateIndex(Feed $model, array $ids = []): void
    {
        /** @var $productCollection Collection */
        $productCollection = $this->prepareCollection($model, $ids);
        $this->productIds = [];

        $conditions = $model->getRule()->getConditions();
        $conditions->collectValidatedAttributes($productCollection);
        $this->sqlBuilder->attachConditionToCollection($productCollection, $conditions);
        /**
         * Prevent retrieval of duplicate records. This may occur when multiselect product attribute matches
         * several allowed values from condition simultaneously
         */
        $productCollection->distinct(true);
        $productCollection->getSelect()->reset(Select::COLUMNS);
        $select = $productCollection->getSelect()->columns(
            [
                'entity_id' => new \Zend_Db_Expr('null'),
                'feed_id' => new \Zend_Db_Expr((int)$model->getEntityId()),
                'valid_product_id' => 'e.' . $productCollection->getEntity()->getIdFieldName()
            ]
        );
        //fix for magento 2.3.2 for big number of products
        $select->reset(Select::ORDER);

        $query = $select->insertFromSelect($productCollection->getResource()->getTable(ValidProduct::TABLE_NAME));
        $productCollection->getConnection()->query($query);
    }

    private function prepareCollection(Feed $model, array $ids = []): AbstractCollection
    {
        /** $productCollection Collection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addStoreFilter($model->getStoreId());

        if ($ids) {
            $productCollection->addAttributeToFilter('entity_id', ['in' => $ids]);
        }

        // DBEST-1250
        if ($model->getExcludeDisabled()) {
            $productCollection->addAttributeToFilter(
                'status',
                ['eq' => Status::STATUS_ENABLED]
            );
        }
        if ($model->getExcludeNotVisible()) {
            $productCollection->addAttributeToFilter(
                'visibility',
                ['neq' => Visibility::VISIBILITY_NOT_VISIBLE]
            );
        }
        if ($model->getExcludeOutOfStock()) {
            $outOfStockProductIds = $this->inventoryResolver->getOutOfStockProductIds();

            if (!empty($outOfStockProductIds)) {
                $productCollection->addFieldToFilter(
                    'entity_id',
                    ['nin' => $outOfStockProductIds]
                );
            }
        }

        $model->getRule()->getConditions()->collectValidatedAttributes($productCollection);

        return $productCollection;
    }
}

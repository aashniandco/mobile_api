<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-event
 * @version   1.2.41
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Event\Event\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\ResourceModel\Stock;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Mirasvit\Event\Event\Context;
use Mirasvit\Event\Event\ObservableEvent;
use Mirasvit\Event\EventData\ProductData;

/**
 * @see \Magento\Catalog\Model\Product::save()
 * @see \Magento\CatalogInventory\Model\ResourceModel\Stock::correctItemsQty()
 * @see \Magento\InventoryApi\Api\SourceItemsSaveInterface::execute()
 */
class QtyEvent extends ObservableEvent
{
    const IDENTIFIER = 'qty_reduced';

    const PARAM_QTY_NEW = 'qty';
    const PARAM_QTY_OLD = 'old_qty';

    private $stockRegistryProvider;

    private $productRepository;

    public function __construct(
        StockRegistryProviderInterface $stockRegistryProvider,
        ProductRepositoryInterface $productRepository,
        Context $context
    ) {
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->productRepository     = $productRepository;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            self::IDENTIFIER => __('Product / Decreased QTY'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getEventData()
    {
        return [
            $this->context->get(ProductData::class),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function expand($params)
    {
        $product = $this->context->create(Product::class)->load($params[ProductData::ID]);

        $params[ProductData::IDENTIFIER] = $product;

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function toString($params)
    {
        $params = $this->expand($params);

        /** @var Product $product */
        $product = $params[ProductData::IDENTIFIER];

        return __(
            'QTY decreased for product %1 from %2 to %3 items',
            $product->getSku(),
            $params[self::PARAM_QTY_OLD],
            $params[self::PARAM_QTY_NEW]
        );
    }

    /**
     * Register an event if product QTY has been reduced while modifying a product.
     *
     * @param Product $subject
     * @param mixed   $result
     *
     * @return mixed $result
     */
    public function afterSave(Product $subject, $result)
    {
        //$stockItem = $this->stockRegistryProvider->getStockItem($subject->getId(), $subject->getStore()->getWebsiteId());
        //$stockStatus = $this->stockRegistryProvider->getStockStatus($subject->getId(), $subject->getStore()->getWebsiteId());
        //$newQty = $stockItem->getQty(); // method returns already changed QTY

        $qty       = $subject->getData('quantity_and_stock_status/qty') !== null
            ? $subject->getData('quantity_and_stock_status/qty')
            : $subject->getData('stock_data/qty');
        $oldQtyArr = $subject->getOrigData('quantity_and_stock_status');
        $oldQty    = is_array($oldQtyArr) && isset($oldQtyArr['qty']) ? $oldQtyArr['qty'] : false;

        if ($qty !== null && $oldQty !== false && $oldQty > $qty) {
            $params = [
                ProductData::ID          => $subject->getId(),
                self::PARAM_QTY_NEW      => $qty,
                self::PARAM_QTY_OLD      => $oldQty,
                self::PARAM_EXPIRE_AFTER => 1,
            ];

            $this->context->eventRepository->register(
                self::IDENTIFIER,
                [$params[ProductData::ID]],
                $params
            );
        }

        return $result;
    }

    /**
     * Register an event if product QTY has been reduced while placing an order.
     *
     * @param Stock    $stock
     * @param callable $proceed
     * @param int[]    $items
     * @param int      $websiteId
     * @param string   $operator +/-
     *
     * @return void
     */
    public function aroundCorrectItemsQty(Stock $stock, callable $proceed, array $items, $websiteId, $operator)
    {
        if ($operator === '-') {
            foreach ($items as $productId => $qty) {
                $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
                $newQty    = $stockItem->getQty(); // method returns already changed QTY
                $oldQty    = $stockItem->getQty() + $qty; // so to get old QTY we should add the subtracted QTY

                if ($oldQty > $newQty) {
                    $params = [
                        ProductData::ID          => $productId,
                        self::PARAM_QTY_NEW      => $newQty,
                        self::PARAM_QTY_OLD      => $oldQty,
                        self::PARAM_EXPIRE_AFTER => 1,
                    ];

                    $this->context->eventRepository->register(
                        self::IDENTIFIER,
                        [$params[ProductData::ID]],
                        $params
                    );
                }
            }
        }

        $proceed($items, $websiteId, $operator);
    }

    public function aroundExecute(\Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSave, callable $proceed, array $sourceItems)
    {
        $proceed($sourceItems);

        /** @var \Magento\Inventory\Model\SourceItem $item */
        foreach ($sourceItems as $item) {
            $newData = $item->getData();
            $oldData = $item->getOrigData();

            if (!is_array($newData) || !isset($newData['quantity'])) {
                continue;
            }

            if (!is_array($oldData) || !isset($oldData['quantity'])) {
                continue;
            }

            $newQty = $newData['quantity'];
            $oldQty = $oldData['quantity'];

            if ($oldQty > $newQty) {
                $sku     = (string)$newData['sku'];
                $product = $this->getProduct($sku);
                if ($product) {
                    $params = [
                        ProductData::ID          => $product->getId(),
                        self::PARAM_QTY_NEW      => $newQty,
                        self::PARAM_QTY_OLD      => $oldQty,
                        self::PARAM_EXPIRE_AFTER => 1,
                    ];
                }

                $this->context->eventRepository->register(
                    self::IDENTIFIER,
                    [$params[ProductData::ID]],
                    $params
                );
            }
        }
    }

    /**
     * @param string $sku
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    private function getProduct($sku)
    {
        $product = null;
        try {
            $product = $this->productRepository->get($sku);
        } catch (\Exception $e) {
        }

        return $product;
    }
}

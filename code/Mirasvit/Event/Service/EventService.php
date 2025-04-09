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



namespace Mirasvit\Event\Service;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Event\Api\Service\EventServiceInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EventService implements EventServiceInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ObjectManagerInterface $objectManager,
        StoreManagerInterface  $storeManager
    ) {
        $this->objectManager = $objectManager;
        $this->storeManager  = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getRandomParams($storeId = null)
    {
        $params = new DataObject();

        if ($storeId === null) {
            $storeId = $this->storeManager->getWebsite(true)
                ->getDefaultGroup()
                ->getDefaultStore()
                ->getStoreId();
        }

        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection */
        $customerCollection = $this->objectManager->create(\Magento\Customer\Model\ResourceModel\Customer\Collection::class);
        $customerCollection->addFieldToFilter('store_id', ['in' => [0, 1]]);
        $customerCollection->getSelect()->order(new \Zend_Db_Expr('RAND()'))->limit(1);

        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->objectManager->create(\Magento\Customer\Model\Customer::class);
        $customer->load($customerCollection->getFirstItem()->getId());

        /** @var \Magento\Quote\Model\ResourceModel\Quote\Collection $quoteCollection */
        $quoteCollection = $this->objectManager->create(\Magento\Quote\Model\ResourceModel\Quote\Collection::class);
        $quoteCollection->addFieldToFilter('items_qty', ['gt' => 0]);
        $quoteCollection->addFieldToFilter('store_id', $storeId);
        $quoteCollection->getSelect()->order(new \Zend_Db_Expr('RAND()'))->limit(1);

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote = $quote->setSharedStoreIds(array_keys($this->storeManager->getStores()))
            ->load($quoteCollection->getFirstItem()->getId());

        /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection */
        $orderCollection = $this->objectManager->create(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        $orderCollection->addFieldToFilter('store_id', $storeId);
        $orderCollection->getSelect()->order(new \Zend_Db_Expr('RAND()'))->limit(1);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->load($orderCollection->getFirstItem()->getId());

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $productCollection->getSelect()->order(new \Zend_Db_Expr('RAND()'))->limit(1);

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->load($productCollection->getFirstItem()->getId());

        /** @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollection */
        $reviewCollection = $this->objectManager->create(\Magento\Review\Model\ResourceModel\Review\Collection::class);
        $reviewCollection->getSelect()->order(new \Zend_Db_Expr('RAND()'))->limit(1);

        /** @var \Magento\Review\Model\Review $review */
        $review = $this->objectManager->create(\Magento\Review\Model\Review::class);
        $review->load($reviewCollection->getFirstItem()->getId());

        $params
            ->setCreatedAt(date('d.m.Y H:i:s'))
            ->setCustomerId($customer->getId())
            ->setCustomerName($customer->getName())
            ->setCustomerEmail($customer->getEmail())
            ->setOrderId($order->getId())
            ->setQuoteId($quote->getId())
            ->setStoreId($storeId)
            ->setProductId($product->getId())
            ->setReviewId($review->getId());

        return $params;
    }
}

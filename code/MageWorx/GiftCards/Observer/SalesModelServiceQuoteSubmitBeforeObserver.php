<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesModelServiceQuoteSubmitBeforeObserver implements ObserverInterface
{
    /**
     * @var \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory
     */
    protected $giftCardsCollection;

    /**
     * @var \MageWorx\GiftCards\Model\ResourceModel\Order\CollectionFactory
     */
    protected $giftCardsOrderCollection;

    /**
     * @var \MageWorx\GiftCards\Model\GiftCardsRepository
     */
    protected $giftCardsRepository;

    /**
     * @var \MageWorx\GiftCards\Model\OrderRepository
     */
    protected $giftCardsOrderRepository;

    /**
     * @var \MageWorx\GiftCards\Model\GiftCardsFactory
     */
    protected $giftCardsFactory;

    /**
     * @var \MageWorx\GiftCards\Model\OrderFactory
     */
    protected $giftCardsOrderFactory;

    /**
     * @var \MageWorx\GiftCards\Helper\Data
     */
    protected $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * SalesModelServiceQuoteSubmitBeforeObserver constructor.
     *
     * @param \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory $giftcardsCollection
     * @param \MageWorx\GiftCards\Model\ResourceModel\Order\CollectionFactory $giftcardsOrderCollection
     * @param \MageWorx\GiftCards\Model\GiftCardsRepository $giftCardsRepository
     * @param \MageWorx\GiftCards\Model\OrderRepository $giftCardsOrderRepository
     * @param \MageWorx\GiftCards\Model\GiftCardsFactory $giftCardsFactory
     * @param \MageWorx\GiftCards\Model\OrderFactory $giftCardsOrderFactory
     * @param \MageWorx\GiftCards\Helper\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory $giftcardsCollection,
        \MageWorx\GiftCards\Model\ResourceModel\Order\CollectionFactory $giftcardsOrderCollection,
        \MageWorx\GiftCards\Model\GiftCardsRepository $giftCardsRepository,
        \MageWorx\GiftCards\Model\OrderRepository $giftCardsOrderRepository,
        \MageWorx\GiftCards\Model\GiftCardsFactory $giftCardsFactory,
        \MageWorx\GiftCards\Model\OrderFactory $giftCardsOrderFactory,
        \MageWorx\GiftCards\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->giftCardsCollection      = $giftcardsCollection;
        $this->giftCardsOrderCollection = $giftcardsOrderCollection;
        $this->giftCardsRepository      = $giftCardsRepository;
        $this->giftCardsOrderRepository = $giftCardsOrderRepository;
        $this->giftCardsFactory         = $giftCardsFactory;
        $this->giftCardsOrderFactory    = $giftCardsOrderFactory;
        $this->helper                   = $helper;
        $this->logger                   = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();

        if (!$order && $observer->getOrders()) {
            $orders = $observer->getOrders();
            $order  = $orders[0];
        }

        $quote = $observer->getQuote();

        if ($quote->getMageworxGiftcardsDescription()) {
            $order->setMageworxGiftcardsAmount($quote->getMageworxGiftcardsAmount());
            $order->setBaseMageworxGiftcardsAmount($quote->getBaseMageworxGiftcardsAmount());
            $order->setMageworxGiftcardsDescription($quote->getMageworxGiftcardsDescription());
        }

        return $this;
    }
}

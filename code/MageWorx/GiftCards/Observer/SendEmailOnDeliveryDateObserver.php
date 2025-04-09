<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory;
use MageWorx\GiftCards\Model\GiftCards;
use MageWorx\GiftCards\Api\Data\GiftCardsInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Exception\MailException;
use Psr\Log\LoggerInterface;

class SendEmailOnDeliveryDateObserver implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SendEmailOnDeliveryDateObserver constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->collectionFactory      = $collectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->logger                 = $logger;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $giftCardsCollection = $this->getUserGiftCardsCollection();
        $userGiftCards       = $giftCardsCollection->getItems();

        if (empty($userGiftCards)) {
            return;
        }

        $orders      = $this->getOrdersByGiftCards($userGiftCards);
        $giftCardIds = [];

        /**
         * @var \MageWorx\GiftCards\Model\GiftCards $card
         */
        foreach ($userGiftCards as $card) {

            if (empty($orders[$card->getOrderId()])) {
                continue;
            }

            /**
             * @var \Magento\Sales\Model\Order $order
             */
            $order = $orders[$card->getOrderId()];

            if (!$card->hasValidMailDeliveryDate($order->getStore())) {
                try {
                    $card->send($order);
                    $giftCardIds[] = $card->getId();
                } catch (MailException $e) {
                    $this->logger->critical($e);
                }
            }
        }

        if ($giftCardIds) {

            $table = $giftCardsCollection->getTable('mageworx_giftcards_card');
            $bind  = [GiftCardsInterface::DELIVERY_STATUS => GiftCards::STATUS_DELIVERED];
            $where = [GiftCardsInterface::CARD_ID . ' IN (?)' => $giftCardIds];

            $giftCardsCollection->getConnection()->update($table, $bind, $where);
        }
    }

    /**
     * @return \MageWorx\GiftCards\Model\ResourceModel\GiftCards\Collection
     */
    protected function getUserGiftCardsCollection()
    {
        $giftCardsCollection = $this->getGiftCardsCollection();

        $giftCardsCollection
            ->addFieldToFilter(
                GiftCardsInterface::CUSTOMER_ID,
                ['neq' => 0]
            );

        return $giftCardsCollection;
    }

    /**
     * this method was created for future to separate logic of processing gift cards that are created by admin
     *
     * @return \MageWorx\GiftCards\Model\ResourceModel\GiftCards\Collection
     */
    protected function getAdminGiftCardsCollection()
    {
        $giftCardsCollection = $this->getGiftCardsCollection();

        $giftCardsCollection
            ->addFieldToFilter(
                GiftCardsInterface::CUSTOMER_ID,
                ['eq' => 0]
            );

        return $giftCardsCollection;
    }

    /**
     * @return \MageWorx\GiftCards\Model\ResourceModel\GiftCards\Collection
     */
    protected function getGiftCardsCollection()
    {
        $giftCardsCollection = $this->collectionFactory->create();

        $giftCardsCollection
            ->addFieldToFilter(
                GiftCardsInterface::CARD_TYPE,
                GiftCards::TYPE_EMAIL
            )
            ->addFieldToFilter(
                GiftCardsInterface::DELIVERY_STATUS,
                GiftCards::STATUS_NEED_SEND_BY_CRON
            );

        return $giftCardsCollection;
    }

    /**
     * @param GiftCardsInterface[] $giftCards
     * @return OrderInterface[]
     */
    protected function getOrdersByGiftCards($giftCards)
    {
        $orderIds = [];

        foreach ($giftCards as $card) {
            $orderIds[] = $card->getOrderId();
        }

        $orderIds = array_unique($orderIds);

        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection
            ->addAttributeToFilter(
                OrderInterface::ENTITY_ID,
                ['in' => $orderIds]
            );

        return $orderCollection->getItems();
    }
}

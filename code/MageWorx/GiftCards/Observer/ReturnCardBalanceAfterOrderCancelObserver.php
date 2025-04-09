<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Observer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageWorx\GiftCards\Api\Data\GiftCardsOrderInterface;
use MageWorx\GiftCards\Api\GiftCardsRepositoryInterface;
use MageWorx\GiftCards\Model\GiftCards;
use MageWorx\GiftCards\Model\ResourceModel\Order\CollectionFactory as GiftCardsOrderCollectionFactory;

class ReturnCardBalanceAfterOrderCancelObserver implements ObserverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var GiftCardsRepositoryInterface
     */
    private $giftCardsRepository;

    /**
     * @var GiftCardsOrderCollectionFactory
     */
    private $giftCardsOrderCollectionFactory;

    /**
     * ReturnCardBalanceAfterOrderCancelObserver constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GiftCardsRepositoryInterface $giftCardsRepository
     * @param GiftCardsOrderCollectionFactory $giftCardsOrderCollectionFactory
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GiftCardsRepositoryInterface $giftCardsRepository,
        GiftCardsOrderCollectionFactory $giftCardsOrderCollectionFactory
    ) {
        $this->searchCriteriaBuilder           = $searchCriteriaBuilder;
        $this->giftCardsRepository             = $giftCardsRepository;
        $this->giftCardsOrderCollectionFactory = $giftCardsOrderCollectionFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');

        if (!$order) {
            return;
        }

        /** @var \MageWorx\GiftCards\Model\ResourceModel\Order\Collection $giftCardsOrderCollection */
        $giftCardsOrderCollection = $this->giftCardsOrderCollectionFactory->create();

        $giftCardsOrderCollection
            ->addFieldToFilter(GiftCardsOrderInterface::ORDER_ID, $order->getId())
            ->addFieldToSelect([GiftCardsOrderInterface::GIFTCARD_ID, GiftCardsOrderInterface::DISCOUNTED]);

        $orderItemsData = $giftCardsOrderCollection->getData();

        if (empty($orderItemsData)) {
            return;
        }

        $discountedData = array_column(
            $orderItemsData,
            GiftCardsOrderInterface::DISCOUNTED,
            GiftCardsOrderInterface::GIFTCARD_ID
        );
        $giftCardIds    = array_column($orderItemsData, GiftCardsOrderInterface::GIFTCARD_ID);
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(GiftCards::CARD_ID, $giftCardIds, 'in')
            ->create();

        $giftCards = $this->giftCardsRepository->getList($searchCriteria)->getItems();

        foreach ($giftCards as $giftCard) {
            $updatedBalance = $giftCard->getCardBalance() + $discountedData[$giftCard->getId()];

            $giftCard->setCardBalance($updatedBalance);

            if ($giftCard->getCardStatus() == GiftCards::STATUS_USED && $updatedBalance > 0) {
                $giftCard->setCardStatus(GiftCards::STATUS_ACTIVE);
            }

            $this->giftCardsRepository->save($giftCard);
        }
    }
}

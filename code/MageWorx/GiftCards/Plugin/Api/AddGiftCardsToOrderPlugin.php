<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Plugin\Api;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\Data\OrderInterface;
use MageWorx\GiftCards\Api\Data\GiftCardsOrderInterface;
use MageWorx\GiftCards\Api\Data\GiftCardDetailsInterface;
use MageWorx\GiftCards\Api\Data\GiftCardDetailsInterfaceFactory;
use MageWorx\GiftCards\Api\GiftCardsOrderRepositoryInterface;

/**
 * Class AddGiftCardsToOrderPlugin
 *
 * @package MageWorx\GiftCards\Plugin\Api
 */
class AddGiftCardsToOrderPlugin
{
    /**
     * @var OrderExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var GiftCardDetailsInterfaceFactory
     */
    private $giftCardDetailsFactory;

    /**
     * @var GiftCardsOrderRepositoryInterface
     */
    private $giftCardsOrderRepository;

    /**
     * Init Plugin
     *
     * @param OrderExtensionFactory $extensionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GiftCardDetailsInterfaceFactory $giftCardDetailsFactory
     * @param GiftCardsOrderRepositoryInterface $giftCardsOrderRepository
     */
    public function __construct(
        OrderExtensionFactory $extensionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GiftCardDetailsInterfaceFactory $giftCardDetailsFactory,
        GiftCardsOrderRepositoryInterface $giftCardsOrderRepository
    ) {
        $this->extensionFactory         = $extensionFactory;
        $this->searchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->giftCardDetailsFactory   = $giftCardDetailsFactory;
        $this->giftCardsOrderRepository = $giftCardsOrderRepository;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $entity
     * @return OrderInterface
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $entity
    ) {
        if (!$entity->getMageworxGiftcardsDescription()) {
            return $entity;
        }
        /** @var \Magento\Sales\Api\Data\OrderExtension $extensionAttributes */
        $extensionAttributes = $entity->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionFactory->create();
        }

        $giftCardsDetails = $this->getGiftCardsDetails($entity->getEntityId());

        $extensionAttributes->setMageworxGiftcardsDetails($giftCardsDetails);
        $extensionAttributes->setMageworxGiftcardsDescription($entity->getMageworxGiftcardsDescription());
        $extensionAttributes->setMageworxGiftcardsAmount($entity->getMageworxGiftcardsAmount());
        $extensionAttributes->setBaseMageworxGiftcardsAmount($entity->getBaseMageworxGiftcardsAmount());

        $entity->setExtensionAttributes($extensionAttributes);

        return $entity;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $entities
     * @return OrderSearchResultInterface
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $entities
    ) {
        $orderIds               = $this->getAllIds($entities);
        $giftCardsDetailsForAll = $this->getGiftCardsDetailsForAll($orderIds);

        /** @var OrderInterface $entity */
        foreach ($entities->getItems() as $entity) {

            if (!$entity->getMageworxGiftcardsDescription()) {
                continue;
            }
            /** @var \Magento\Sales\Api\Data\OrderExtension $extensionAttributes */
            $extensionAttributes = $entity->getExtensionAttributes();

            if ($extensionAttributes === null) {
                $extensionAttributes = $this->extensionFactory->create();
            }

            $giftCardsDetails = $giftCardsDetailsForAll[$entity->getEntityId()];

            $extensionAttributes->setMageworxGiftcardsDetails($giftCardsDetails);
            $extensionAttributes->setMageworxGiftcardsDescription($entity->getMageworxGiftcardsDescription());
            $extensionAttributes->setMageworxGiftcardsAmount($entity->getMageworxGiftcardsAmount());
            $extensionAttributes->setBaseMageworxGiftcardsAmount($entity->getBaseMageworxGiftcardsAmount());

            $entity->setExtensionAttributes($extensionAttributes);
        }

        return $entities;
    }

    /**
     * Get Gift Cards Details
     *
     * @param int $orderId
     * @return array
     */
    private function getGiftCardsDetails($orderId)
    {
        $giftCardsDetails = [];

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(GiftCardsOrderInterface::ORDER_ID, $orderId)
            ->create();

        $items = $this->giftCardsOrderRepository->getList($searchCriteria)->getItems();

        /** @var GiftCardsOrderInterface $item */
        foreach ($items as $item) {
            /** @var GiftCardDetailsInterface $giftCardDetails */
            $giftCardDetails = $this->giftCardDetailsFactory->create();

            $giftCardDetails->setId($item->getCardId());
            $giftCardDetails->setCode($item->getCardCode());
            $giftCardDetails->setAmount($item->getDiscounted());
            $giftCardDetails->setBaseAmount($item->getDiscounted());

            $giftCardsDetails[] = $giftCardDetails;
        }

        return $giftCardsDetails;
    }

    /**
     * @param array $orderIds
     * @return array
     */
    private function getGiftCardsDetailsForAll($orderIds)
    {
        $giftCardsOrdersGroupedByOrderId = $this->getGiftCardsOrdersGroupedByOrderId($orderIds);
        $giftCardsDetailsForAll          = [];

        foreach ($orderIds as $orderId) {
            $giftCardsDetails = [];

            if (!empty($giftCardsOrdersGroupedByOrderId[$orderId])) {

                /** @var GiftCardsOrderInterface $item */
                foreach ($giftCardsOrdersGroupedByOrderId[$orderId] as $item) {
                    /** @var GiftCardDetailsInterface $giftCardDetails */
                    $giftCardDetails = $this->giftCardDetailsFactory->create();

                    $giftCardDetails->setId($item->getCardId());
                    $giftCardDetails->setCode($item->getCardCode());
                    $giftCardDetails->setAmount($item->getDiscounted());
                    $giftCardDetails->setBaseAmount($item->getDiscounted());

                    $giftCardsDetails[] = $giftCardDetails;
                }
            }

            $giftCardsDetailsForAll[$orderId] = $giftCardsDetails;
        }

        return $giftCardsDetailsForAll;
    }

    /**
     * Retrieve all ids
     *
     * @param OrderSearchResultInterface $entities
     * @return array
     */
    private function getAllIds(OrderSearchResultInterface $entities)
    {
        $ids = [];

        foreach ($entities->getItems() as $item) {
            $ids[] = $item->getEntityId();
        }

        return $ids;
    }

    /**
     * @param array $orderIds
     * @return array
     */
    private function getGiftCardsOrdersGroupedByOrderId($orderIds)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(GiftCardsOrderInterface::ORDER_ID, $orderIds, 'in')
            ->create();

        $items = $this->giftCardsOrderRepository->getList($searchCriteria)->getItems();

        $groupedByOrderId = [];

        foreach ($items as $item) {
            $groupedByOrderId[$item->getOrderId()][] = $item;
        }

        return $groupedByOrderId;
    }
}

<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\MailException;
use MageWorx\GiftCards\Api\GiftCardsRepositoryInterface;
use MageWorx\GiftCards\Model\GiftCards;
use Psr\Log\LoggerInterface;

class SalesOrderSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory
     */
    protected $giftcardsCollection;

    /**
     * @var GiftCardsRepositoryInterface
     */
    protected $giftCardsRepository;

    /**
     * @var \MageWorx\GiftCards\Helper\Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SalesOrderSaveAfterObserver constructor.
     *
     * @param \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory $giftcardsCollection
     * @param GiftCardsRepositoryInterface $giftCardsRepository
     * @param \MageWorx\GiftCards\Helper\Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory $giftcardsCollection,
        GiftCardsRepositoryInterface $giftCardsRepository,
        \MageWorx\GiftCards\Helper\Data $helper,
        LoggerInterface $logger
    ) {
        $this->giftcardsCollection = $giftcardsCollection;
        $this->giftCardsRepository = $giftCardsRepository;
        $this->helper              = $helper;
        $this->logger              = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order                   = $observer->getOrder();
        $orderSendEmailStatuses  = explode(',', $this->helper->getOrderStatuses());
        $orderActivationStatuses = explode(',', $this->helper->getCardActivationOrderStatuses());


        if (in_array($order->getStatus(), $orderSendEmailStatuses)
            || in_array($order->getStatus(), $orderActivationStatuses)
        ) {
            $cards = $this->giftcardsCollection->create()
                                               ->addFieldToFilter('order_id', $order->getId())
                                               ->load();

            foreach ($cards as $card) {
                $giftCard   = $this->giftCardsRepository->get($card->getId());
                $needToSave = false;

                if (in_array($order->getStatus(), $orderActivationStatuses)) {
                    if ($giftCard->getCardStatus() == GiftCards::STATUS_INACTIVE) {
                        $giftCard->setCardStatus(GiftCards::STATUS_ACTIVE);
                        $needToSave = true;
                    }
                }

                if (in_array($order->getStatus(), $orderSendEmailStatuses)) {
                    if ($giftCard->hasValidMailDeliveryDate($order->getStore())) {
                        $giftCard->setDeliveryStatus(GiftCards::STATUS_NEED_SEND_BY_CRON);

                        $needToSave = true;
                    } elseif ($giftCard->getDeliveryStatus() != GiftCards::STATUS_DELIVERED) {
                        $needToSave = true;

                        try {
                            $giftCard->send($order);
                            $giftCard->setDeliveryStatus(GiftCards::STATUS_DELIVERED);
                        } catch (MailException $e) {
                            $giftCard->setMailDeliveryDate(null);
                            $giftCard->setDeliveryStatus(GiftCards::STATUS_NEED_SEND_BY_CRON);
                            $this->logger->critical($e);
                        }
                    }
                }

                if ($needToSave) {
                    $this->giftCardsRepository->save($giftCard);
                }
            }
        }
    }
}

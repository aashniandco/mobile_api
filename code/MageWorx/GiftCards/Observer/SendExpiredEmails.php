<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Observer;

use Magento\Framework\Event\ObserverInterface;
use MageWorx\GiftCards\Model\GiftCards;

class SendExpiredEmails implements ObserverInterface
{
    /**
     * @var \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory
     */
    protected $giftcardsCollection;

    /**
     * @var \MageWorx\GiftCards\Model\GiftCardsFactory
     */
    protected $giftCardsFactory;

    /**
     * @var \MageWorx\GiftCards\Helper\Data
     */
    protected $helper;

    /**
     * SendExpiredEmails constructor.
     *
     * @param \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory $giftcardsCollection
     * @param \MageWorx\GiftCards\Model\GiftCardsFactory $giftCardsFactory
     * @param \MageWorx\GiftCards\Helper\Data $helper
     */
    public function __construct(
        \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory $giftcardsCollection,
        \MageWorx\GiftCards\Model\GiftCardsFactory $giftCardsFactory,
        \MageWorx\GiftCards\Helper\Data $helper
    ) {
        $this->giftcardsCollection = $giftcardsCollection;
        $this->giftCardsFactory    = $giftCardsFactory;
        $this->helper              = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $cards = $this->giftcardsCollection->create()
                                           ->addFieldToFilter('expired_email_send', 0)
                                           ->addFieldToFilter('card_balance', ['gt' => 0])
                                           ->addFieldToFilter('card_status', ['nin' => [GiftCards::STATUS_USED]])
                                           ->load();

        foreach ($cards as $card) {
            if ($this->helper->isExpired($card)) {
                $card->sendExpiredCard();
                $card->setExpiredEmailSend(1);
                $card->save();
            }
        }
    }
}

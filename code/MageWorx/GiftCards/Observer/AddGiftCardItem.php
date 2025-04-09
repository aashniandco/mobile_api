<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageWorx\GiftCards\Model\Session as GiftCardsSession;

class AddGiftCardItem implements ObserverInterface
{
    /**
     * @var GiftCardsSession
     */
    private $giftCardsSession;

    /**
     * AddGiftCardItem constructor.
     *
     * @param GiftCardsSession $giftCardsSession
     */
    public function __construct(
        GiftCardsSession $giftCardsSession
    ) {
        $this->giftCardsSession = $giftCardsSession;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Payment\Model\Cart $cart */
        $cart = $observer->getEvent()->getCart();

        $giftCardsData = $this->giftCardsSession->getFrontOptions();

        if ($this->giftCardsSession->getActive() && $giftCardsData) {

            $giftCardsAmount  = 0;
            $descriptionArray = [];

            foreach ($giftCardsData as $id => $card) {
                $descriptionArray[] = $card['code'];

                $giftCardsAmount += $card['applied'];
            }

            $description = $descriptionArray ? implode(',', array_unique($descriptionArray)) : '';

            $name = $description ? __('Gift Card (%1)', $description) : __('Gift Card');

            if ($giftCardsAmount > 0.0001) {
                $cart->addCustomItem($name, 1, -1.00 * $giftCardsAmount, 'mageworx_giftcards');
            }
        }
    }
}

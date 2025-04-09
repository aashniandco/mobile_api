<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\GiftCards\Plugin\Klarna;

class AddGiftCardsToOrderLinesPlugin
{
    /**
     * Checkout item type
     */
    const ITEM_TYPE_GIFTCARD = 'gift_card';

    /**
     * @param \Klarna\Core\Model\Checkout\Orderline\Giftcard $subject
     * @param \Klarna\Core\Model\Checkout\Orderline\Giftcard $result
     * @param \Klarna\Core\Api\BuilderInterface $checkout
     * @return \Klarna\Core\Model\Checkout\Orderline\Giftcard
     */
    public function afterFetch(
        \Klarna\Core\Model\Checkout\Orderline\Giftcard $subject,
        \Klarna\Core\Model\Checkout\Orderline\Giftcard $result,
        \Klarna\Core\Api\BuilderInterface $checkout
    ) {
        if ($checkout->getMageworxGiftcardsTotalAmount()) {
            $checkout->addOrderLine(
                [
                    'type'             => self::ITEM_TYPE_GIFTCARD,
                    'reference'        => $checkout->getMageworxGiftcardsReference(),
                    'name'             => $checkout->getMageworxGiftcardsTitle(),
                    'quantity'         => 1,
                    'unit_price'       => $checkout->getMageworxGiftcardsUnitPrice(),
                    'tax_rate'         => $checkout->getMageworxGiftcardsTaxRate(),
                    'total_amount'     => $checkout->getMageworxGiftcardsTotalAmount(),
                    'total_tax_amount' => $checkout->getMageworxGiftcardsTaxAmount(),
                ]
            );
        }

        return $result;
    }
}

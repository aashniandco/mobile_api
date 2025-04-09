<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\GiftCards\Plugin\Klarna;

class CollectGiftCardsPlugin
{
    /**
     * @param \Klarna\Core\Model\Checkout\Orderline\Giftcard $subject
     * @param \Klarna\Core\Model\Checkout\Orderline\Giftcard $result
     * @param \Klarna\Core\Api\BuilderInterface $checkout
     * @return \Klarna\Core\Model\Checkout\Orderline\Giftcard
     */
    public function afterCollect(
        \Klarna\Core\Model\Checkout\Orderline\Giftcard $subject,
        \Klarna\Core\Model\Checkout\Orderline\Giftcard $result,
        \Klarna\Core\Api\BuilderInterface $checkout
    ) {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote  = $checkout->getObject();
        $totals = $quote->getTotals();

        if (!is_array($totals) || !isset($totals['mageworx_giftcards'])) {
            return $result;
        }

        $total = $totals['mageworx_giftcards'];

        if ($total->getValue() !== 0) {
            $amount = $quote->getBaseMageworxGiftcardsAmount();
            $value  = $this->toApiFloat((float)$amount);

            $checkout->addData(
                [
                    'mageworx_giftcards_unit_price'   => $value,
                    'mageworx_giftcards_tax_rate'     => 0,
                    'mageworx_giftcards_total_amount' => $value,
                    'mageworx_giftcards_tax_amount'   => 0,
                    'mageworx_giftcards_title'        => $total->getTitle()->getText(),
                    'mageworx_giftcards_reference'    => $total->getCode()
                ]
            );
        }

        return $result;
    }

    /**
     * Prepare float for API call
     *
     * @param float $float
     *
     * @return false|float
     */
    private function toApiFloat(float $float)
    {
        return round($float * 100);
    }
}

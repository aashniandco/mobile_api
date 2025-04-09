<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\GiftCards\Plugin\Klarna\CheckoutExt;

abstract class AbstractCollectGiftCardsPlugin
{
    /**
     * @param \Klarna\Base\Model\Api\Parameter $parameter
     * @param \Klarna\Base\Model\Checkout\Orderline\DataHolder $dataHolder
     * @param float $amount
     */
    protected function collect(
        \Klarna\Base\Model\Api\Parameter $parameter,
        \Klarna\Base\Model\Checkout\Orderline\DataHolder $dataHolder,
        float $amount
    ) {
        $totals = $dataHolder->getTotals();

        if (!is_array($totals) || !isset($totals['mageworx_giftcards'])) {
            return;
        }

        $total = $totals['mageworx_giftcards'];

        if ($total->getValue() !== 0) {
            $value     = (float)$parameter->getGiftCardAccountUnitPrice() + $this->toApiFloat($amount);
            $title     = $parameter->getGiftCardAccountTitle();
            $title     = $title ? $title . ' ' . $total->getTitle()->getText() : $total->getTitle()->getText();
            $reference = $parameter->getGiftCardAccountReference();
            $reference = $reference ? $reference . ' ' . $total->getCode() : $total->getCode();

            $parameter->setGiftCardAccountUnitPrice($value)
                      ->setGiftCardAccountTaxRate(0)
                      ->setGiftCardAccountTotalAmount($value)
                      ->setGiftCardAccountTaxAmount(0)
                      ->setGiftCardAccountTitle($title)
                      ->setGiftCardAccountReference($reference);
        }
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

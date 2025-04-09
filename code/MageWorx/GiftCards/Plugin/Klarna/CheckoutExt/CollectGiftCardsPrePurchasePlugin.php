<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\GiftCards\Plugin\Klarna\CheckoutExt;

class CollectGiftCardsPrePurchasePlugin extends AbstractCollectGiftCardsPlugin
{
    /**
     * @param \Klarna\Base\Model\Checkout\Orderline\Items\Giftcard $subject
     * @param \Klarna\Base\Model\Checkout\Orderline\Items\Giftcard $result
     * @param \Klarna\Base\Model\Api\Parameter $parameter
     * @param \Klarna\Base\Model\Checkout\Orderline\DataHolder $dataHolder
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Klarna\Base\Model\Checkout\Orderline\Items\Giftcard
     */
    public function afterCollectPrePurchase(
        \Klarna\Base\Model\Checkout\Orderline\Items\Giftcard $subject,
        \Klarna\Base\Model\Checkout\Orderline\Items\Giftcard $result,
        \Klarna\Base\Model\Api\Parameter $parameter,
        \Klarna\Base\Model\Checkout\Orderline\DataHolder $dataHolder,
        \Magento\Quote\Api\Data\CartInterface $quote
    ) {
        $this->collect($parameter, $dataHolder, (float)$quote->getBaseMageworxGiftcardsAmount());

        return $result;
    }
}

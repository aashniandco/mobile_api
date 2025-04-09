<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\GiftCards\Plugin\Klarna\CheckoutExt;

class CollectGiftCardsPostPurchasePlugin extends AbstractCollectGiftCardsPlugin
{
    /**
     * @param \Klarna\Base\Model\Checkout\Orderline\Items\Giftcard $subject
     * @param \Klarna\Base\Model\Checkout\Orderline\Items\Giftcard $result
     * @param \Klarna\Base\Model\Api\Parameter $parameter
     * @param \Klarna\Base\Model\Checkout\Orderline\DataHolder $dataHolder
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Klarna\Base\Model\Checkout\Orderline\Items\Giftcard
     */
    public function afterCollectPostPurchase(
        \Klarna\Base\Model\Checkout\Orderline\Items\Giftcard $subject,
        \Klarna\Base\Model\Checkout\Orderline\Items\Giftcard $result,
        \Klarna\Base\Model\Api\Parameter $parameter,
        \Klarna\Base\Model\Checkout\Orderline\DataHolder $dataHolder,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $this->collect($parameter, $dataHolder, (float)$order->getBaseMageworxGiftcardsAmount());

        return $result;
    }
}

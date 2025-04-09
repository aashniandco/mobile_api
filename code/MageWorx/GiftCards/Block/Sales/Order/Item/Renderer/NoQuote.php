<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block\Sales\Order\Item\Renderer;

class NoQuote extends \MageWorx\GiftCards\Block\Sales\Order\Item\Renderer
{
    /**
     * Get the html for item price
     *
     * @param $item
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItemPrice($item)
    {
        $block = $this->getLayout()->getBlock('item_price');
        $block->setItem($item);

        return $block->toHtml();
    }
}

<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block\Checkout;

/**
 * Gift Cards Total Row Renderer
 */
class GiftCards extends \Magento\Checkout\Block\Total\DefaultTotal
{
    /**
     * @var string
     */
    protected $_template = 'MageWorx_GiftCards::checkout/giftcards.phtml';
}

<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block\Cart;

class GiftCards extends \Magento\Checkout\Block\Cart\AbstractCart
{
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $currency;

    /**
     * @var \MageWorx\GiftCards\Helper\Data
     */
    protected $helper;

    /**
     * Path to template file
     *
     * @var string
     */
    protected $_template = 'MageWorx_GiftCards::cart/coupon.phtml';

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \MageWorx\GiftCards\Helper\Data $helper
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Directory\Model\Currency $currency,
        \MageWorx\GiftCards\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->currency         = $currency;
        $this->helper           = $helper;
    }

    public function getCurrencySymbol()
    {
        return $this->currency->getCurrencySymbol();
    }

    public function canShow()
    {
        return $this->helper->showInCart();
    }

    /**
     * @return bool
     */
    public function isExpandedContent()
    {
        return $this->helper->isExpandedGiftCardBlock();
    }

    /**
     * @return bool
     */
    public function isPaypalExpress()
    {
        return false;
    }
}

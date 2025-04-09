<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block\PayPal\Express;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Directory\Model\Currency;
use MageWorx\GiftCards\Helper\Data as HelperData;
use Magento\Checkout\Block\Cart\AbstractCart;

class GiftCards extends AbstractCart
{
    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * GiftCards constructor.
     *
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param Currency $currency
     * @param HelperData $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        Currency $currency,
        HelperData $helper,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->currency         = $currency;
        $this->helper           = $helper;
    }

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->currency->getCurrencySymbol();
    }

    /**
     * @return bool
     */
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
     * URLs with secure/unsecure protocol switching
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        if (!array_key_exists('_secure', $params)) {
            $params['_secure'] = $this->getRequest()->isSecure();
        }

        return parent::getUrl($route, $params);
    }

    /**
     * @return bool
     */
    public function isPaypalExpress()
    {
        return true;
    }
}

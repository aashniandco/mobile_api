<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Controller\Cart;

use MageWorx\GiftCards\Model\QuoteGiftCardsDescription;
use MageWorx\GiftCards\Api\Data\GiftCardsInterface;

class DeActivateGiftCard extends \Magento\Checkout\Controller\Cart
{
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var QuoteGiftCardsDescription
     */
    protected $quoteGiftCardsDescription;

    /**
     * DeActivateGiftCard constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param QuoteGiftCardsDescription $quoteGiftCardsDescription
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        QuoteGiftCardsDescription $quoteGiftCardsDescription
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->quoteRepository           = $quoteRepository;
        $this->quoteGiftCardsDescription = $quoteGiftCardsDescription;
    }

    /**
     * Initialize coupon
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $cardCode  = trim((string)$this->getRequest()->getParam(GiftCardsInterface::CARD_CODE));
        $cartQuote = $this->cart->getQuote();
        $cardCodes = $this->quoteGiftCardsDescription->getCodes((string)$cartQuote->getMageworxGiftcardsDescription());
        $key       = array_search($cardCode, $cardCodes);

        if ($key !== false) {
            unset($cardCodes[$key]);

            $cartQuote->setMageworxGiftcardsDescription($this->quoteGiftCardsDescription->getDescription($cardCodes));
            $cartQuote->getShippingAddress()->setCollectShippingRates(true);
            $cartQuote->collectTotals();
            $this->quoteRepository->save($cartQuote);
        }

        return $this->_goBack();
    }
}

<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Controller\Cart;

use MageWorx\GiftCards\Model\GiftCards as ModelGiftCards;
use \Magento\Checkout\Controller\Cart as ControllerCart;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use MageWorx\GiftCards\Model\QuoteGiftCardsDescription;

class ActivateGiftCard extends ControllerCart
{
    const ALL_STORE_VIEWS = 0;
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Coupon factory
     *
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     * @var \Magento\Framework\Escaper
     */

    protected $escaper;

    /**
     * Gift Cards Factory
     *
     * var@ \MageWorx\GiftCards\GiftCardsRepository
     */
    protected $giftCardsRepository;

    /**
     * @var \MageWorx\GiftCards\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var QuoteGiftCardsDescription
     */
    protected $quoteGiftCardsDescription;

    /**
     * ActivateGiftCard constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \MageWorx\GiftCards\Model\GiftCardsRepository $giftCardsRepository
     * @param \Magento\Framework\Escaper $escaper
     * @param \MageWorx\GiftCards\Helper\Data $helper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param QuoteGiftCardsDescription $quoteGiftCardsDescription
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \MageWorx\GiftCards\Model\GiftCardsRepository $giftCardsRepository,
        \Magento\Framework\Escaper $escaper,
        \MageWorx\GiftCards\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
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
        $this->couponFactory             = $couponFactory;
        $this->quoteRepository           = $quoteRepository;
        $this->giftCardsRepository       = $giftCardsRepository;
        $this->escaper                   = $escaper;
        $this->helper                    = $helper;
        $this->customerSession           = $customerSession;
        $this->quoteGiftCardsDescription = $quoteGiftCardsDescription;
    }

    /**
     * Initialize coupon
     *
     * @return ResponseInterface|ResultRedirect|ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $giftCardCode = trim((string)$this->getRequest()->getParam('giftcard_code'));

        try {
            $card = $this->giftCardsRepository->getByCode($giftCardCode);

        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(
                __('Gift Card "%1" is not valid.', $this->escaper->escapeHtml($giftCardCode))
            );

            return $this->_goBack();
        }

        if ($card->getId() &&
            $card->getCardStatus() == \MageWorx\GiftCards\Model\GiftCards::STATUS_ACTIVE &&
            !$this->helper->isExpired($card) &&
            $this->checkCustomerGroup($card)
        ) {
            if (!$this->isCorrectStoreGiftCard($card)) {
                $this->messageManager->addErrorMessage(
                    __(
                        'Gift Card "%1" can not be applied on this Store View.',
                        $this->escaper->escapeHtml($giftCardCode)
                    )
                );

                return $this->_goBack();
            }

            $cartQuote = $this->cart->getQuote();
            $cardCodes = $this->quoteGiftCardsDescription->getCodes(
                (string)$cartQuote->getMageworxGiftcardsDescription()
            );

            if (in_array($card->getCardCode(), $cardCodes)) {
                $this->messageManager->addErrorMessage(
                    __('This Gift Card is already in the Quote.')
                );

                return $this->_goBack();
            }

            $cardCodes[] = $card->getCardCode();

            $cartQuote->setMageworxGiftcardsDescription($this->quoteGiftCardsDescription->getDescription($cardCodes));
            $cartQuote->getShippingAddress()->setCollectShippingRates(true);
            $cartQuote->collectTotals();

            $this->quoteRepository->save($cartQuote);

            $this->messageManager->addSuccessMessage(
                __('Gift Card "%1" was applied.', $this->escaper->escapeHtml($giftCardCode))
            );
        } else {
            if ($card->getId() && ($card->getCardStatus() == \MageWorx\GiftCards\Model\GiftCards::STATUS_USED)) {
                $this->messageManager->addErrorMessage(
                    __('Gift Card "%1" was used.', $this->escaper->escapeHtml($giftCardCode))
                );
            } elseif ($card->getId() && $this->helper->isExpired($card)) {
                $this->messageManager->addErrorMessage(
                    __('Gift Card "%1" is expired.', $this->escaper->escapeHtml($giftCardCode))
                );
            } elseif (!$this->checkCustomerGroup($card)) {
                $this->messageManager->addErrorMessage(
                    __('Unfortunately, you can not use this gift card.')
                );
            } else {
                $this->messageManager->addErrorMessage(
                    __('Gift Card "%1" is not valid.', $this->escaper->escapeHtml($giftCardCode))
                );
            }
        }

        return $this->_goBack();
    }

    /**
     * @param \MageWorx\GiftCards\Model\GiftCards $card
     * @return string
     */
    public function checkCustomerGroup($card)
    {
        if ($this->customerSession->isLoggedIn()) {
            $currentCustomerGroup = $this->customerSession->getCustomer()->getGroupId();
        } else {
            $currentCustomerGroup = \Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID;
        }

        if (array_search($currentCustomerGroup, $card->getCustomerGroupId()) === false) {
            return false;
        }

        return true;
    }

    /**
     * @param ModelGiftCards $card
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function isCorrectStoreGiftCard($card)
    {
        $giftCardAssignStoresIds = $card->getStoreId();
        $currentStoreId          = $this->_storeManager->getStore()->getId();

        foreach ($giftCardAssignStoresIds as $storeId) {
            if ($storeId == $currentStoreId || $storeId == self::ALL_STORE_VIEWS) {
                return true;
            }
        }

        return false;
    }
}

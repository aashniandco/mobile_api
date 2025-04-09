<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model\Quote;

use MageWorx\GiftCards\Helper\Price as HelperPrice;
use MageWorx\GiftCards\Helper\Data as Helper;
use MageWorx\GiftCards\Model\Source\ApplyToTotalAmounts as ApplyToTotalAmountsOptions;
use MageWorx\GiftCards\Model\QuoteGiftCardsDescription;
use MageWorx\GiftCards\Api\Data\GiftCardsInterface;

class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Discount calculation object
     *
     * @var \Magento\SalesRule\Model\Validator
     */
    protected $calculator;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \MageWorx\GiftCards\Model\Session
     */
    protected $giftcardsSession;

    /**
     * @var \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory
     */
    protected $giftcardsCollection;

    /**
     * @var array
     */
    protected $cards;

    /**
     * @var bool
     */
    protected $isCalculatedGiftCardPrice = false;

    /**
     * @var HelperPrice
     */
    protected $helperPrice;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ApplyToTotalAmountsOptions
     */
    protected $applyToTotalAmountsOptions;

    /**
     * @var QuoteGiftCardsDescription
     */
    protected $quoteGiftCardsDescription;

    /**
     * Discount constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \MageWorx\GiftCards\Model\Session $giftcardsSession
     * @param \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory $giftcardsCollection
     * @param HelperPrice $helperPrice
     * @param Helper $helper
     * @param ApplyToTotalAmountsOptions $applyToTotalAmountsOptions
     * @param QuoteGiftCardsDescription $quoteGiftCardsDescription
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageWorx\GiftCards\Model\Session $giftcardsSession,
        \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory $giftcardsCollection,
        HelperPrice $helperPrice,
        Helper $helper,
        ApplyToTotalAmountsOptions $applyToTotalAmountsOptions,
        QuoteGiftCardsDescription $quoteGiftCardsDescription

    ) {
        $this->storeManager               = $storeManager;
        $this->giftcardsSession           = $giftcardsSession;
        $this->giftcardsCollection        = $giftcardsCollection;
        $this->helperPrice                = $helperPrice;
        $this->helper                     = $helper;
        $this->applyToTotalAmountsOptions = $applyToTotalAmountsOptions;
        $this->quoteGiftCardsDescription  = $quoteGiftCardsDescription;
    }

    /**
     * Collect address discount amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $store = $this->storeManager->getStore($quote->getStoreId());
        $items = $shippingAssignment->getItems();

        if (!count($items)) {
            return $this;
        }

        $cardCodes = $this->quoteGiftCardsDescription->getCodes((string)$quote->getMageworxGiftcardsDescription());

        $quote->setUseGiftCard(false);
        $quote->setMageworxGiftcardsDescription(null);
        $quote->setMageworxGiftcardsAmount(0);
        $quote->setBaseMageworxGiftcardsAmount(0);

        $this->giftcardsSession->setActive(0);
        $this->giftcardsSession->setFrontOptions(null);
        $this->giftcardsSession->setGiftCardsIds(null);

        if ($cardCodes) {
            $cards = $this->giftcardsCollection->create()->addFieldToFilter(
                GiftCardsInterface::CARD_CODE,
                ['in' => $cardCodes]
            )->load();

            if (!count($cards)) {
                return $this;
            }

            $quoteSubtotal      = 0;
            $quoteBaseSubtotal  = 0;
            $giftcardsTotal     = 0;
            $giftcardsBaseTotal = 0;

            $excludedAmounts = $this->getExcludedAmounts();

            foreach ($total->getAllTotalAmounts() as $code => $amount) {
                if (in_array($code, $excludedAmounts)) {
                    continue;
                }

                $quoteSubtotal += $amount;
            }

            foreach ($total->getAllBaseTotalAmounts() as $code => $baseAmount) {
                if (in_array($code, $excludedAmounts)) {
                    continue;
                }

                $quoteBaseSubtotal += $baseAmount;
            }

            $quote->setUseGiftCard(true);

            foreach ($cards as $card) {
                $this->cards[$card->getId()] = [
                    'balance'       => $card->getCardBalance(),
                    'code'          => $card->getCardCode(),
                    'card_currency' => $card->getCardCurrency()
                ];
            }

            $frontData = [];
            foreach ($this->cards as $id => $card) {
                if ($quoteSubtotal > 0) {
                    $cardStoreBalance = $this->helperPrice->convertCardCurrencyToStoreCurrency(
                        $card['balance'],
                        $store,
                        $card['card_currency']
                    );
                    $cardBaseBalance  = $this->helperPrice->convertCurrencyToBaseCurrency(
                        $card['balance'],
                        $store,
                        $card['card_currency']
                    );
                    $cardApplied      = min($quoteSubtotal, $cardStoreBalance);
                    $cardBaseApplied  = min($quoteBaseSubtotal, $cardBaseBalance);

                    $quoteSubtotal     -= $cardApplied;
                    $quoteBaseSubtotal -= $cardBaseApplied;

                    $giftcardsTotal     += $cardApplied;
                    $giftcardsBaseTotal += $cardBaseApplied;

                    $baseRemaining = $cardBaseBalance - $cardBaseApplied;
                    $cardRemaining = $this->helperPrice->convertBaseCurrencyToStoreCurrency(
                        $baseRemaining,
                        $store,
                        $card['card_currency']
                    );

                    $frontData[$id]['applied']        = $cardApplied;
                    $frontData[$id]['remaining']      = $cardStoreBalance - $cardApplied;
                    $frontData[$id]['card_remaining'] = $cardRemaining;
                    $frontData[$id]['code']           = $card['code'];
                    $frontData[$id]['card_currency']  = $card['card_currency'];
                }
            }

            $descriptionArray = [];

            foreach ($this->cards as $key => $card) {
                $descriptionArray[$key] = $card['code'];
            }

            $total->setTotalAmount('mageworx_giftcards', -$giftcardsTotal);
            $total->setBaseTotalAmount('mageworx_giftcards', -$giftcardsBaseTotal);

            $quote->setMageworxGiftcardsAmount(-$giftcardsTotal);
            $quote->setBaseMageworxGiftcardsAmount(-$giftcardsBaseTotal);
            $quote->setMageworxGiftcardsDescription(
                $this->quoteGiftCardsDescription->getDescription($descriptionArray)
            );

            $quote->setSubtotalWithDiscount($total->getSubtotal() - $giftcardsTotal);
            $quote->setBaseSubtotalWithDiscount($total->getBaseSubtotal() - $giftcardsBaseTotal);

            $this->giftcardsSession->setActive(1);
            $this->giftcardsSession->setFrontOptions($frontData);
            $this->giftcardsSession->setGiftCardsIds($this->cards);
        }

        return $this;
    }

    /**
     * Add discount total information to address
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        $amount = $quote->getMageworxGiftcardsAmount();

        if ($amount != 0) {
            $description = $quote->getMageworxGiftcardsDescription();
            $result      = [
                'code'  => 'mageworx_giftcards',
                'title' => strlen($description) ? __($description) : __('Gift Card'),
                'value' => $amount,
            ];
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getExcludedAmounts()
    {
        $applyToAmounts  = array_keys($this->applyToTotalAmountsOptions->toArray());
        $selectedAmounts = $this->helper->getApplyToTotalAmounts();

        return array_diff($applyToAmounts, $selectedAmounts);
    }
}

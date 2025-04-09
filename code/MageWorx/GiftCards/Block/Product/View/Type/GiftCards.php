<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block\Product\View\Type;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use MageWorx\GiftCards\Helper\Product as HelperProduct;

/**
 * Giftcards product data view
 */
class GiftCards extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * @var int
     */
    public $cardType;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $currency;

    /**
     * @var array
     */
    protected $aAdditionalPrices;

    /**
     * @var HelperProduct
     */
    protected $helper;

    /**
     * @var  \Magento\Checkout\Helper\Data
     */
    public $checkoutHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * GiftCards constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\Directory\Model\Currency $currency
     * @param HelperProduct $helper
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Directory\Model\Currency $currency,
        HelperProduct $helper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $arrayUtils, $data);
        $this->currency        = $currency;
        $this->helper          = $helper;
        $this->checkoutHelper  = $checkoutHelper;
        $this->priceCurrency   = $priceCurrency;
    }

    /**
     * Set cardType and priceStatus
     *
     */
    public function _construct()
    {
        $product = $this->getProduct();

        if ($product) {
            $this->cardType = $product->getData('mageworx_gc_type');
        }

        parent::_construct();
    }

    /**
     * @return int
     */
    public function getCardType()
    {
        return $this->cardType;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return array
     */
    public function getAdditionalPrices()
    {
        if (!$this->aAdditionalPrices && $this->getProduct()->getData('mageworx_gc_additional_price')) {
            $this->aAdditionalPrices = explode(';', $this->getProduct()->getData('mageworx_gc_additional_price'));
        }

        return $this->aAdditionalPrices;
    }


    /**
     * @return mixed
     */
    public function getMinPrice()
    {
        $prices = $this->getAllPrices();

        $min = min($prices);
        if (!$this->getProduct()->getMageworxGcAllowOpenAmount()) {
            $min = $min > 0 ? $min : null;
        }

        return $min;
    }

    /**
     * @return mixed
     */
    public function getMaxPrice()
    {
        $prices = $this->getAllPrices();

        $max = max($prices);

        return $max > 0 ? $max : null;
    }

    /**
     * @return mixed
     */
    public function getAllPrices()
    {
        $prices  = [];
        $product = $this->getProduct();

        if ($product->getPrice() > 0) {
            $prices[] = $product->getPrice();
        }

        if ($product->getMageworxGcAllowOpenAmount()) {
            $prices[] = $product->getMageworxGcOpenAmountMin() ? $product->getMageworxGcOpenAmountMin() : 0;
            if ($product->getMageworxGcOpenAmountMax() > 0) {
                $prices[] = $product->getMageworxGcOpenAmountMax();
            }
        }

        if ($this->getAdditionalPrices()) {
            foreach ($this->getAdditionalPrices() as $additionalPrice) {
                if ($additionalPrice) {
                    $prices[] = $additionalPrice;
                }
            }
        }

        return $prices;
    }

    /**
     * @return string
     */
    public function getGiftCardPrice()
    {
        $from = '';
        $to   = '';

        $min = $this->priceCurrency->convertAndRound($this->getMinPrice());
        $max = $this->priceCurrency->convertAndRound($this->getMaxPrice());

        if ($min == $max) {
            return $this->checkoutHelper->formatPrice($min);
        }

        if ($min !== null) {
            $from = __('From ');
        }

        $product = $this->getProduct();

        if ($product->getMageworxGcAllowOpenAmount() && $product->getMageworxGcOpenAmountMax() <= 0) {
            return $from . $this->checkoutHelper->formatPrice($min);
        }

        if ($max) {
            $to = $from ? __(' to ') : __('To ');
        }

        return $from . $this->checkoutHelper->formatPrice($min) .
            $to . $this->checkoutHelper->formatPrice($max);
    }

    /**
     * @param string $price
     * @return float
     */
    public function convertPrice($price)
    {
        return $this->priceCurrency->convertAndRound($price);
    }

    /**
     * @return bool
     */
    public function isAmountButtonsDisplayMode()
    {
        return $this->getAmountDisplayMode() == \MageWorx\GiftCards\Model\Source\AmountDisplayMode::BUTTONS_MODE;
    }

    /**
     * @return mixed
     */
    public function getAmountDisplayMode()
    {
        return $this->helper->getAmountDisplayMode();
    }

    /**
     * @return string
     */
    public function getAmountPlaceholder()
    {
        $product = $this->getProduct();

        if (!$product->getMageworxGcAllowOpenAmount()) {
            return '';
        }

        $from = $this->priceCurrency->convertAndRound($product->getMageworxGcOpenAmountMin());
        $to   = $this->priceCurrency->convertAndRound($product->getMageworxGcOpenAmountMax());

        if (!$from && !$to) {
            return $this->helper->getAmountPlaceholder();
        }

        $splitter       = ' - ';
        $currencySymbol = $this->priceCurrency->getCurrencySymbol();

        if (!$to) {
            return $currencySymbol . $from . $splitter . '...';
        }

        return $currencySymbol . $from . $splitter . $currencySymbol . $to;
    }

    /**
     * @return string
     */
    public function getFromNamePlaceholder()
    {
        return $this->helper->getFromNamePlaceholder();
    }

    /**
     * @return string
     */
    public function getToNamePlaceholder()
    {
        return $this->helper->getToNamePlaceholder();
    }

    /**
     * @return string
     */
    public function getToEmailPlaceholder()
    {
        return $this->helper->getToEmailPlaceholder();
    }

    /**
     * @return string
     */
    public function getMessagePlaceholder()
    {
        return $this->helper->getMessagePlaceholder();
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        /** Escape RTL characters which are present in some locales and corrupt formatting */
        $dateFormat = preg_replace('/[^MmDdYy\/\.\-]/', '', $dateFormat);

        return $dateFormat;
    }

    /**
     * @return string
     */
    public function getCurrentCurrencySymbol()
    {
        return $this->priceCurrency->getCurrencySymbol();
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     */
    public function getAmountValidateFrom($product)
    {
        $currency      = $this->getCurrency();
        $from          = $currency->convert($this->convertPrice($product->getMageworxGcOpenAmountMin()));

        return $from ? $from : 0;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     */
    public function getAmountValidateTo($product)
    {
        $currency      = $this->getCurrency();
        $to            = $currency->convert($this->convertPrice($product->getMageworxGcOpenAmountMax()));

        return $to ? $to : 0;
    }

    /**
     * @param int $price
     * @return string
     */
    public function getConvertedProductPrice($price)
    {
        return $this->checkoutHelper->formatPrice($this->convertPrice($price));
    }
}

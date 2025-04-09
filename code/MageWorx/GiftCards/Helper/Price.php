<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context as ContextHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Price extends AbstractHelper
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Price constructor.
     *
     * @param ContextHelper $context
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        ContextHelper $context,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context);
    }

    /**
     * @param string $balance
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param string|null $cardCurrency
     * @return float
     */
    public function convertCardCurrencyToStoreCurrency($balance, $store, $cardCurrency)
    {
        if ($this->needConvertPrice($store, $cardCurrency, $balance)) {
            $rate                = $this->priceCurrency->convert($balance, $store, $cardCurrency) / $balance;
            $baseCurrencyBalance = $balance / $rate;

            $storeCurrencyBalance = $this->priceCurrency->convert(
                $baseCurrencyBalance,
                $store,
                $store->getCurrentCurrencyCode()
            );

            return $storeCurrencyBalance;
        }

        return $balance;
    }

    /**
     * @param string $balance
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param string|null $cardCurrency
     * @return float
     */
    public function convertCurrencyToBaseCurrency($balance, $store, $cardCurrency)
    {
        if (isset($cardCurrency) && $store->getBaseCurrencyCode() != $cardCurrency && $balance > 0) {
            $rate                = $this->priceCurrency->convert($balance, $store, $cardCurrency) / $balance;
            $baseCurrencyBalance = $balance / $rate;

            return $baseCurrencyBalance;
        }

        return $balance;
    }

    /**
     * @param string $balance
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param string|null $cardCurrency
     * @return float
     */
    public function convertBaseCurrencyToStoreCurrency($balance, $store, $cardCurrency)
    {
        if (isset($cardCurrency) && $store->getBaseCurrencyCode() != $cardCurrency && $balance > 0) {
            $rate                = $this->priceCurrency->convert($balance, $store, $cardCurrency) / $balance;
            $cardCurrencyBalance = $balance * $rate;

            return $cardCurrencyBalance;
        }

        return $balance;
    }

    /**
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param string|null $cardCurrency
     * @param string $balance
     * @return bool
     */
    protected function needConvertPrice($store, $cardCurrency, $balance)
    {
        return (isset($cardCurrency) && $store->getCurrentCurrencyCode() != $cardCurrency && $balance > 0);
    }
}

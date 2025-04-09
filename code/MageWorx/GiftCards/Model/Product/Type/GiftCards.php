<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

/**
 * Giftcards product type implementation
 *
 * @author     MageWorx Dev Team
 */

namespace MageWorx\GiftCards\Model\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use MageWorx\GiftCards\Api\Data\GiftCardsInterface;

class GiftCards extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    const TYPE_CODE = 'mageworx_giftcards';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * Product is possible to configure
     *
     * @var bool
     */
    protected $_canConfigure = true;

    /** var@ MageWorx\GiftCards\Model\Helper\Data */
    protected $helper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * GiftCards constructor.
     *
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Model\Product\Option $catalogProductOption
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \MageWorx\GiftCards\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageWorx\GiftCards\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {

        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository
        );
        $this->storeManager  = $storeManager;
        $this->helper        = $helper;
        $this->localeDate    = $localeDate;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $buyRequest
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        error_log("----buyRequest-----");
        $data                       = parent::processBuyRequest($product, $buyRequest);
        $data['card_amount']        = $buyRequest->getCardAmount();
        $data['card_currency']      = $buyRequest->getCardCurrency();
        $data['mail_to']            = $buyRequest->getMailTo();
        $data['mail_to_email']      = $buyRequest->getMailToEmail();
        $data['mail_from']          = $buyRequest->getMailFrom();
        $data['mail_message']       = $buyRequest->getMailMessage();
        $data['offline_country']    = $buyRequest->getOfflineCountry();
        $data['offline_state']      = $buyRequest->getOfflineState();
        $data['offline_city']       = $buyRequest->getOfflineCity();
        $data['offline_street']     = $buyRequest->getOfflineStreet();
        $data['offline_zip']        = $buyRequest->getOfflineZip();
        $data['offline_phone']      = $buyRequest->getOfflinePhone();
        $data['mail_delivery_date'] = $buyRequest->getMailDeliveryDate();

        $data['mail_delivery_date_user_value'] = $buyRequest->getMailDeliveryDateUserValue();
        // $data['image_url'] = 'abc';

        return $data;
    }

    /**
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return array|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        error_log("----buyRequest1----");
        if (!$this->setDeliveryDateValueFromUserValue($buyRequest)) {
            return $this->getSpecifyValidDeliveryDateMessage()->render();
        }

        $data     = $buyRequest->getData();
        $cardType = $product->getData('mageworx_gc_type');

        if (!isset($data['mail_to_email'])
            && $product->getMageworxGcType() == \MageWorx\GiftCards\Model\GiftCards::TYPE_EMAIL
        ) {
            return $this->getSpecifyEmailMessage()->render();
        }

        /*
         * Validate card amount
         * TODO: Need options validation
         */

        if (isset($data['card_amount'])) {
            if ($product->getMageworxGcAllowOpenAmount() && $data['card_amount'] == 'other_amount') {

                $amountMin = $this->priceCurrency->convertAndRound($product->getMageworxGcOpenAmountMin());
                $amountMax = $this->priceCurrency->convertAndRound($product->getMageworxGcOpenAmountMax());

                $isMin = $amountMin > 0 && $data['card_amount_other'] < $amountMin;
                $isMax = $amountMax > 0 && $data['card_amount_other'] > $amountMax;

                if ($isMin || $isMax) {
                    return $this->getSpecifyPriceRangeMessage()->render();
                }
                //convert other amount to base currency
                $currentCurrency = $this->storeManager->getStore()->getCurrentCurrency();
                $otherAmount     = $currentCurrency->convert(
                    $data['card_amount_other'],
                    $this->storeManager->getStore()->getBaseCurrencyCode()
                );

                $product->setData('price', $otherAmount);
                $data['card_amount'] = $otherAmount;
            } else {
                $additionalPrices = (string)$product->getMageworxGcAdditionalPrice();

                if (!in_array($data['card_amount'], explode(';', $additionalPrices))
                    && $data['card_amount'] != $product->getPrice()
                ) {
                    return $this->getSpecifyPriceMessage()->render();
                }
            }

            if ((!isset($data['card_amount']) && !$product->getData('price')) || !isset($data['mail_to'])) {
                return $this->getSpecifyOptionsMessage()->render();
            }
        }
        /*
         * Add gift card params as product custom options to product quote
         * TODO: Need options validation
         */
        $product->addCustomOption('card_type', $cardType);
        $product->addCustomOption(
            'card_amount',
            isset($data['card_amount']) ? $data['card_amount'] : $product->getPrice()
        );
        $product->addCustomOption('card_currency', $this->storeManager->getStore()->getCurrentCurrencyCode());
        $product->addCustomOption('mail_to', isset($data['mail_to']) ? $data['mail_to'] : '');
        $product->addCustomOption('mail_to_email', isset($data['mail_to_email']) ? $data['mail_to_email'] : '');
        $product->addCustomOption('mail_from', isset($data['mail_from']) ? $data['mail_from'] : '');
        $product->addCustomOption('mail_message', isset($data['mail_message']) ? $data['mail_message'] : '');
        $product->addCustomOption('offline_country', isset($data['offline_country']) ? $data['offline_country'] : '');
        $product->addCustomOption('offline_state', isset($data['offline_state']) ? $data['offline_state'] : '');
        $product->addCustomOption('offline_city', isset($data['offline_city']) ? $data['offline_city'] : '');
        $product->addCustomOption('offline_street', isset($data['offline_street']) ? $data['offline_street'] : '');
        $product->addCustomOption('offline_zip', isset($data['offline_zip']) ? $data['offline_zip'] : '');
        $product->addCustomOption('offline_phone', isset($data['offline_phone']) ? $data['offline_phone'] : '');
        $product->addCustomOption(
            'mail_delivery_date',
            !empty($data['mail_delivery_date']) ? $data['mail_delivery_date'] : null
        );
        $product->addCustomOption(
            'mail_delivery_date_user_value',
            !empty($data['mail_delivery_date_user_value']) ? $data['mail_delivery_date_user_value'] : null
        );
        $product->addCustomOption('image_url', isset($data['image_url']) ? $data['image_url'] : '');
         // $product->addCustomOption('expire_date', isset($data['expire_date']) ? $data['expire_date'] : '');

        return parent::_prepareProduct($buyRequest, $product, $processMode);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getSpecifyPriceRangeMessage(): \Magento\Framework\Phrase
    {
        return __('Card amount is not within the specified range.');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getSpecifyPriceMessage(): \Magento\Framework\Phrase
    {
        return __('Card amount is not valid.');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getSpecifyOptionsMessage(): \Magento\Framework\Phrase
    {
        return __('You need to choose options for your item.');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getSpecifyEmailMessage(): \Magento\Framework\Phrase
    {
        return __('You need to add email for your item.');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getSpecifyValidDeliveryDateMessage(): \Magento\Framework\Phrase
    {
        return __('Please specify valid delivery date.');
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isVirtual($product)
    {
        if ($product->getCustomOption('card_type')->getValue() == \MageWorx\GiftCards\Model\GiftCards::TYPE_OFFLINE) {
            return false;
        }

        return true;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return $this|void
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
        return $this;
    }

    /**
     * @param string $userDDValue
     * @return string|null
     * @throws \Exception
     */
    protected function getFormattedDeliveryDateValue(string $userDDValue)
    {
        if (!preg_match('/^\d{1,4}.+\d{1,4}.+\d{1,4}$/', $userDDValue)) {
            return null;
        }

        $userDeliveryDate = $this->localeDate->date($userDDValue, null, true, false);
        $userDDTimestamp  = $userDeliveryDate->getTimestamp();

        $date = new \DateTime();
        $date = $date->setTimestamp($userDDTimestamp);
        $year = $date->format('Y');

        if ($year > 9999 || $year < 1000) {
            return null;
        }

        return $this->localeDate->formatDate($userDeliveryDate, \IntlDateFormatter::MEDIUM);
    }

    /**
     * @param \Magento\Framework\DataObject $buyRequest
     * @return bool
     * @throws \Exception
     */
    protected function setDeliveryDateValueFromUserValue(\Magento\Framework\DataObject $buyRequest): bool
    {
        $deliveryDateValue = null;

        if (!empty($buyRequest->getData(GiftCardsInterface::MAIL_DELIVERY_DATE . '_user_value'))) {

            $userDeliveryDateValue = $buyRequest->getData(GiftCardsInterface::MAIL_DELIVERY_DATE . '_user_value');
            $deliveryDateValue     = $this->getFormattedDeliveryDateValue((string)$userDeliveryDateValue);

            if (!$deliveryDateValue) {
                return false;
            }
        }

        $buyRequest->setData(GiftCardsInterface::MAIL_DELIVERY_DATE, $deliveryDateValue);

        return true;
    }
}

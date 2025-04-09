<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Helper;

use Magento\Store\Model\ScopeInterface;

class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_PATH_PRODUCT_AMOUNT_DISPLAY_MODE   = 'mageworx_giftcards/product/amount_display_mode';
    const CONFIG_PATH_PRODUCT_AMOUNT_PLACEHOLDER    = 'mageworx_giftcards/product/amount_placeholder';
    const CONFIG_PATH_PRODUCT_FROM_NAME_PLACEHOLDER = 'mageworx_giftcards/product/from_name_placeholder';
    const CONFIG_PATH_PRODUCT_TO_NAME_PLACEHOLDER   = 'mageworx_giftcards/product/to_name_placeholder';
    const CONFIG_PATH_PRODUCT_TO_EMAIL_PLACEHOLDER  = 'mageworx_giftcards/product/to_email_placeholder';
    const CONFIG_PATH_PRODUCT_MESSAGE_PLACEHOLDER   = 'mageworx_giftcards/product/message_placeholder';

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getAmountDisplayMode($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_PRODUCT_AMOUNT_DISPLAY_MODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getAmountPlaceholder($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_PRODUCT_AMOUNT_PLACEHOLDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getFromNamePlaceholder($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_PRODUCT_FROM_NAME_PLACEHOLDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getToNamePlaceholder($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_PRODUCT_TO_NAME_PLACEHOLDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getToEmailPlaceholder($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_PRODUCT_TO_EMAIL_PLACEHOLDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getMessagePlaceholder($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_PRODUCT_MESSAGE_PLACEHOLDER,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}

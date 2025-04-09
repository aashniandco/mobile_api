<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * MageWorx data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**#@+
     * XML paths for config settings
     */
    const XML_SHOW_IN_CART                         = 'mageworx_giftcards/main/show_in_shopping_cart';
    const XML_EXPAND_GIFT_CARD_BLOCK               = 'mageworx_giftcards/main/expand_gift_card_block';
    const XML_APPLY_TO_TOTAL_AMOUNTS               = 'mageworx_giftcards/main/apply_to';
    const XML_CARD_ACTIVATION_ORDER_STATUSES       = 'mageworx_giftcards/main/orderstatus';
    const XML_ORDER_STATUSES                       = 'mageworx_giftcards/email/orderstatus';
    const XML_ADD_CODE_TO_PRODUCT                  = 'mageworx_giftcards/main/add_code_to_product';
    const XML_SUPPORT_MAIL                         = 'trans_email/ident_general/email';
    const XML_DISABLE_MULTISHIPPING                = 'mageworx_giftcards/main/disable_multishipping';
    const XML_EXPIRATION_ALERT                     = 'mageworx_giftcards/email/expiration_alert';
    const XML_USE_DEFAULT_GIFTCARDS_PICTURE        = 'mageworx_giftcards/email/giftcards_picture';
    const XML_EMAIL_TEMPLATE_IDENTIFIER            = 'mageworx_giftcards/email/email_template';
    const XML_PRINT_TEMPLATE_IDENTIFIER            = 'mageworx_giftcards/email/print_template';
    const XML_OFFLINE_TEMPLATE_IDENTIFIER          = 'mageworx_giftcards/email/offline_template';
    const XML_EXPIRED_TEMPLATE_IDENTIFIER          = 'mageworx_giftcards/email/expired_template';
    const XML_EXPIRATION_ALERT_TEMPLATE_IDENTIFIER = 'mageworx_giftcards/email/expiration_alert_template';

    /**
     * @var \Magento\Store\Model\Information
     */
    protected $storeInformation;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\Information $storeInformation
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\Information $storeInformation
    ) {
        $this->storeManager     = $storeManager;
        $this->storeInformation = $storeInformation->getStoreInformationObject($this->storeManager->getStore());
        parent::__construct($context);
    }

    public function showInCart()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_SHOW_IN_CART,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isExpandedGiftCardBlock()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_EXPAND_GIFT_CARD_BLOCK, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    public function getApplyToTotalAmounts()
    {
        $totalAmounts = $this->scopeConfig->getValue(
            self::XML_APPLY_TO_TOTAL_AMOUNTS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$totalAmounts) {
            return [];
        }

        return explode(',', $totalAmounts);
    }

    public function getStoreName()
    {
        return $this->storeInformation['name'];
    }

    public function getSupportMail()
    {
        return $this->scopeConfig->getValue(self::XML_SUPPORT_MAIL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getOrderStatuses()
    {
        return $this->scopeConfig->getValue(self::XML_ORDER_STATUSES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve comma-separated order statuses
     *
     * @return string|null
     */
    public function getCardActivationOrderStatuses()
    {
        return $this->scopeConfig->getValue(
            self::XML_CARD_ACTIVATION_ORDER_STATUSES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function getAddCodeToProduct()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_ADD_CODE_TO_PRODUCT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getStorePhone()
    {
        return $this->storeInformation['phone'];
    }

    /**
     * @return bool
     */
    public function getIsDisableMultishipping()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_DISABLE_MULTISHIPPING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $xmlPath
     * @return mixed
     */
    public function getConfigValue($xmlPath)
    {
        return $this->scopeConfig->getValue($xmlPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $item
     * @return bool
     */
    public function isExpired($item)
    { 
        if (!$item->getExpireDate()) {
            return false;
        }
        $daysLeft = $this->calculateExpireIn($item->getExpireDate());

        return $daysLeft < 0;
    }

    /**
     * @param $product
     * @return bool|string
     */
    public function getExpireDateForProduct($product)
    {
        $expireDate =date('Y-m-d', strtotime('+1 year'));
        return $expireDate;
    }

    /**
     * @param string $expireDate
     * @return float
     */
    public function calculateExpireIn($expireDate)
    {
        return round((strtotime($expireDate)) - strtotime(date('M d Y'))) / 3600 / 24;
    }

    /**
     * @return string
     */
    public function getAlertDays()
    {
        return $this->scopeConfig->getValue(
            self::XML_EXPIRATION_ALERT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function isUseDefaultGiftCardsPicture()
    {
        return $this->scopeConfig->getValue(
            self::XML_USE_DEFAULT_GIFTCARDS_PICTURE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getEmailTemplateIdentifier()
    {
        return $this->scopeConfig->getValue(
            self::XML_EMAIL_TEMPLATE_IDENTIFIER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getPrintTemplateIdentifier()
    {
        return $this->scopeConfig->getValue(
            self::XML_PRINT_TEMPLATE_IDENTIFIER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getOfflineTemplateIdentifier()
    {
        return $this->scopeConfig->getValue(
            self::XML_OFFLINE_TEMPLATE_IDENTIFIER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getExpirationAlertTemplateIdentifier()
    {
        return $this->scopeConfig->getValue(
            self::XML_EXPIRATION_ALERT_TEMPLATE_IDENTIFIER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getExpiredTemplateIdentifier()
    {
        return $this->scopeConfig->getValue(
            self::XML_EXPIRED_TEMPLATE_IDENTIFIER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}

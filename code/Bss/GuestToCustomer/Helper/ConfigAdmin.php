<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    BSS_GuestToCustomer
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GuestToCustomer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class ConfigAdmin extends AbstractHelper
{
    const BSS_CONFIG_ENABLE_MODULE = 'bss_guest_to_customer/bss_guest_to_customer_general/enable';
    const BSS_CONFIG_AUTO_CONVERT = 'bss_guest_to_customer/bss_guest_to_customer_general/auto_convert';
    const BSS_CONFIG_CUSTOMER_GROUP = 'bss_guest_to_customer/bss_guest_to_customer_general/customer_group';
    const BSS_CONFIG_ASSIGN_ORDERS = 'bss_guest_to_customer/bss_guest_to_customer_general/assign_orders';
    const BSS_CONFIG_ENABLE_SEND_EMAIL =  'bss_guest_to_customer/bss_guest_to_customer_email/enable_email';
    const BSS_CONFIG_EMAIL_SENDER = 'bss_guest_to_customer/bss_guest_to_customer_email/email_sender';
    const BSS_CONFIG_EMAIL_TEMPLATE = 'bss_guest_to_customer/bss_guest_to_customer_email/email_template';
    const BSS_CONFIG_GENDER_REQUIRE_FIELD = 'bss_guest_to_customer/bss_require_field/gender_require_field';
    const BSS_CONFIG_TAXVAT_REQUIRE_FIELD = 'bss_guest_to_customer/bss_require_field/taxvat_require_field';
    const BSS_CONFIG_SUFFIX_REQUIRE_FIELD = 'bss_guest_to_customer/bss_require_field/suffix_require_field';
    const BSS_CONFIG_PREFIX_REQUIRE_FIELD = 'bss_guest_to_customer/bss_require_field/prefix_require_field';
    const BSS_CONFIG_FAX_REQUIRE_FIELD = 'bss_guest_to_customer/bss_require_field/fax_require_field';
    const BSS_CONFIG_TELEPHONE_REQUIRE_FIELD = 'bss_guest_to_customer/bss_require_field/telephone_require_field';
    const BSS_CONFIG_COMPANY_REQUIRE_FIELD = 'bss_guest_to_customer/bss_require_field/company_require_field';
    const BSS_CONFIG_DOB_REQUIRE_FIELD = 'bss_guest_to_customer/bss_require_field/dob_require_field';
    const CUSTOMER_ADDRESS_CONFIG_TELEPHONE_REQUIRE_FIELD = 'customer/address/telephone_show';
    const BSS_CONFIG_ASYNC_ADDRESS = 'bss_guest_to_customer/bss_guest_to_customer_general/async_address';

    /**
     * ScopeConfigInterface
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ConfigAdmin constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
    }

    //General config admin

    /**
     * Get Config Enable Module
     *
     * @return string
     */
    public function getConfigEnableModule()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CONFIG_ENABLE_MODULE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Auto Convert
     *
     * @return string
     */
    public function getConfigAutoConvert()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CONFIG_AUTO_CONVERT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Customer Group
     *
     * @return string
     */
    public function getConfigCustomerGroup()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CONFIG_CUSTOMER_GROUP,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Assign Orders
     *
     * @return string
     */
    public function getConfigAssignOrders()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CONFIG_ASSIGN_ORDERS,
            ScopeInterface::SCOPE_STORE
        );
    }

    //Email config admin

    /**
     * Get Config Enable Email
     *
     * @return string
     */
    public function getConfigEnableEmail()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CONFIG_ENABLE_SEND_EMAIL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Email Sender
     *
     * @return string
     */
    public function getConfigEmailSender()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CONFIG_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Email Template
     *
     * @return string
     */
    public function getConfigEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CONFIG_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get config telephone require.
     *
     * @param array $arrData
     * @return void
     */
    public function getConfigTelephoneRequire(&$arrData)
    {
        if (!isset($arrData['telephone']) ||
            $arrData['telephone'] == null ||
            $arrData['telephone'] == '') {
            $arrData['telephone'] = '123456';
        }
    }

    /**
     * Get config async address
     *
     * @return bool
     */
    public function isAsyncAddress()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CONFIG_ASYNC_ADDRESS,
            ScopeInterface::SCOPE_STORE
        );
    }
}

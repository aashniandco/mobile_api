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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class ConfigCustomerAdmin extends AbstractHelper
{
    const BSS_CUSTOMER_CONFIG_PREFIX_REQUIRE = 'customer/address/prefix_show';
    const BSS_CUSTOMER_CONFIG_SUFFIX_REQUIRE = 'customer/address/suffix_show';
    const BSS_CUSTOMER_CONFIG_DOB_REQUIRE = 'customer/address/dob_show';
    const BSS_CUSTOMER_CONFIG_TAXVAT_REQUIRE = 'customer/address/taxvat_show';
    const BSS_CUSTOMER_CONFIG_GENDER_REQUIRE = 'customer/address/gender_show';
    const BSS_CUSTOMER_CONFIG_TELEPHONE_REQUIRE = 'customer/address/telephone_show';
    const BSS_CUSTOMER_CONFIG_COMPANY_REQUIRE = 'customer/address/company_show';
    const BSS_CUSTOMER_CONFIG_FAX_REQUIRE = 'customer/address/fax_show';

    /**
     * ScopeConfigInterface
     *
     * @var ScopeConfigInterface
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

    /**
     * Get Config Prefix Require
     *
     * @return string
     */
    public function getConfigPrefixRequire()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CUSTOMER_CONFIG_PREFIX_REQUIRE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Suffix Require
     *
     * @return string
     */
    public function getConfigSuffixRequire()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CUSTOMER_CONFIG_SUFFIX_REQUIRE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Dob Require
     *
     * @return string
     */
    public function getConfigDobRequire()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CUSTOMER_CONFIG_DOB_REQUIRE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Tax/Vat Require
     *
     * @return string
     */
    public function getConfigTaxVatRequire()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CUSTOMER_CONFIG_TAXVAT_REQUIRE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Gender Require
     *
     * @return string
     */
    public function getConfigGenderRequire()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CUSTOMER_CONFIG_GENDER_REQUIRE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Telephone Require
     *
     * @return string
     */
    public function getConfigTelephoneRequire()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CUSTOMER_CONFIG_TELEPHONE_REQUIRE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Company Require
     *
     * @return string
     */
    public function getConfigCompanyRequire()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CUSTOMER_CONFIG_COMPANY_REQUIRE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Fax Require
     *
     * @return string
     */
    public function getConfigFaxRequire()
    {
        return $this->scopeConfig->getValue(
            self::BSS_CUSTOMER_CONFIG_FAX_REQUIRE,
            ScopeInterface::SCOPE_STORE
        );
    }
}

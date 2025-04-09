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
namespace Bss\GuestToCustomer\Plugin\Checkout;

use Magento\Customer\Model\EmailNotification;
use Magento\Customer\Api\Data\CustomerInterface;

class Email
{

    /**
     * Registry
     * @var \Magento\Framework\Registry $registry
     */
    protected $registry;
    /**
     * @var inherit
     */
    protected $subject;
    /**
     * @var inherit
     */
    protected $sendemailStoreId;

    /**
     * Email constructor.
     *
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Get Param Config Send Email
     *
     * @return bool
     */
    protected function hasParamConfigSendEmail()
    {
        return $this->registry->registry('configSendEmail');
    }

    /**
     * Around New Account
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param EmailNotification $subject
     * @param \Closure $procede
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param int $storeId
     * @param bool $sendemailStoreId
     * @return \Closure
     */
    public function aroundNewAccount(
        EmailNotification $subject,
        \Closure $procede,
        CustomerInterface $customer,
        $type = EmailNotification::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = 0,
        $sendemailStoreId = null
    ) {
        $config = $this->hasParamConfigSendEmail();
        if (!$config || !isset($config)) {
            return $procede($customer, $type, $backUrl, $storeId, $sendemailStoreId);
        }
    }
}

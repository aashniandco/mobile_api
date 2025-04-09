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
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GuestToCustomer\Plugin\Model\Metadata\Form;

use \Bss\GuestToCustomer\Helper\Observer\Helper;

class ValidateValue
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * ValidateValue constructor.
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param Helper $helper
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        Helper $helper
    ) {
        $this->coreSession = $coreSession;
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * After Validate Value
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param Object $subject
     * @param Object $result
     * @return bool
     */
    public function afterValidateValue(
        $subject,
        $result
    ) {
        $guestToCustomer = $this->coreSession->getData('bss_guest_to_customer_type');
        if ($guestToCustomer == 1) {
            return true;
        }
        return $result;
    }
}

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
namespace Bss\GuestToCustomer\Helper\Account;

use Bss\GuestToCustomer\Helper\ConfigAdmin;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;

class Registration extends AbstractHelper
{

    /**
     * Http
     * @var Http $request
     */
    protected $request;

    /**
     * ConfigAdmin
     * @var ConfigAdmin $helperConfigAdmin
     */
    protected $helperConfigAdmin;

    /**
     * Registration constructor.
     * @param ConfigAdmin $helperConfigAdmin
     * @param Http $request
     * @param Context $context
     */
    public function __construct(
        ConfigAdmin $helperConfigAdmin,
        Http $request,
        Context $context
    ) {
        $this->helperConfigAdmin = $helperConfigAdmin;
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * Get Template
     *
     * @return string
     */
    public function getTemplate()
    {
        if ($this->helperConfigAdmin->getConfigEnableModule()
            && $this->helperConfigAdmin->getConfigAutoConvert()
        ) {
            $template =  'Bss_GuestToCustomer::registration.phtml';
        } else {
            $template = 'Magento_Checkout::registration.phtml';
        }

        return $template;
    }
}

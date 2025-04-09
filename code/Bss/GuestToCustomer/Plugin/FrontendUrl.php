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
 * @package    Bss_GuestToCustomer
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GuestToCustomer\Plugin;

class FrontendUrl
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $frontendUrl;

    /**
     * FrontendUrl constructor.
     * @param \Magento\Framework\UrlInterface $frontendUrl
     */
    public function __construct(
        \Magento\Framework\UrlInterface $frontendUrl
    ) {
        $this->frontendUrl = $frontendUrl;
    }

    /**
     * @return \Magento\Framework\UrlInterface
     */
    public function getFrontendUrl()
    {
        return $this->frontendUrl;
    }
}

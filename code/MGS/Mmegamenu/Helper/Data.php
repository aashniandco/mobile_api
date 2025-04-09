<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Mmegamenu\Helper;

/**
 * Contact base helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	public function getStoreConfig($node){
		return $this->scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
}
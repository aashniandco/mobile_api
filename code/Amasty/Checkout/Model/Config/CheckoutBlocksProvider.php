<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Config;

class CheckoutBlocksProvider
{
    /**
     * @return array
     */
    public function getDefaultBlockTitles()
    {
	    $first = '1. Shipping Address';
	    $second = '3. Shipping Method';
            $third = '4. Payment Method';

	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$customerSession = $objectManager->get('Magento\Customer\Model\Session');
	if(!$customerSession->isLoggedIn()) {
		$first = '1. Login';
		$second = '4. Shipping Method';
		$third = '5. Payment Method';
	}
        return [
            'shipping_address' => __($first),
            'shipping_method' => __($second),
            'delivery' => __('Delivery'),
            'payment_method' => __($third),
            'summary' => __('Order Summary'),
        ];
    }
}

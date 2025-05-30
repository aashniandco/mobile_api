<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;

class CatalogProductAdditionalPriceModifier implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product          = $observer->getEvent()->getProduct();
        $additionalPrices = $product->getMageworxGcAdditionalPrice();

        if ($additionalPrices || is_array($additionalPrices)) {
            $modifiedAdditionalPrices = '';
            if (!is_array($additionalPrices)) {
                $product->setMageworxGcAdditionalPrice(trim($additionalPrices, ';'));
            } else {
                foreach ($additionalPrices as $price) {
                    if (isset($price['delete']) || !$price['mageworx_gc_additional_price']) {
                        continue;
                    }
                    $modifiedAdditionalPrices .= $price['mageworx_gc_additional_price'] . ';';
                }
                $product->setMageworxGcAdditionalPrice(trim($modifiedAdditionalPrices, ';'));
            }
        }
    }
}

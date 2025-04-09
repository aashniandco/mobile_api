<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\GiftCards\Plugin\Adminhtml;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper as InitializationHelper;
use Magento\Catalog\Model\Product;

class CheckAdditionalPriceInPostDataPlugin
{
    /**
     * @param InitializationHelper $subject
     * @param Product $product
     * @param array $productData
     * @return array
     */
    public function beforeInitializeFromData(InitializationHelper $subject, Product $product, array $productData)
    {
        if (!isset($productData['mageworx_gc_additional_price'])) {
            $productData['mageworx_gc_additional_price'] = [];

            return [$product, $productData];
        }

        return null;
    }
}

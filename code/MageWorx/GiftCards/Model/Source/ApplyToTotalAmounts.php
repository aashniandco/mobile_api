<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model\Source;

class ApplyToTotalAmounts extends \MageWorx\GiftCards\Model\Source
{
    const SHIPPING_TOTAL_AMOUNT_CODE = 'shipping';
    const TAX_TOTAL_AMOUNT_CODE      = 'tax';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::SHIPPING_TOTAL_AMOUNT_CODE,
                'label' => __('Shipping')
            ],
            [
                'value' => self::TAX_TOTAL_AMOUNT_CODE,
                'label' => __('Tax')
            ]
        ];
    }
}

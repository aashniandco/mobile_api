<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model\Source;

class AmountDisplayMode extends \MageWorx\GiftCards\Model\Source
{
    const DEFAULT_MODE = 0;
    const BUTTONS_MODE = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::DEFAULT_MODE,
                'label' => __('Drop-down (default)')
            ],
            [
                'value' => self::BUTTONS_MODE,
                'label' => __('Buttons')
            ]
        ];
    }
}

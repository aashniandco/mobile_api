<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GeoipRedirect
 */


namespace Amasty\GeoipRedirect\Model\Source;

class PopupType implements \Magento\Framework\Option\ArrayInterface
{
    const NOTIFICATION = 0;
    const CONFIRMATION = 1;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NOTIFICATION, 'label' => __('Notification Popup')],
            ['value' => self::CONFIRMATION, 'label' => __('Confirmation Popup')]
        ];
    }

}

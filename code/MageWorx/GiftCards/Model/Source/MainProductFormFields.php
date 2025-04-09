<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model\Source;

use MageWorx\GiftCards\Api\Data\GiftCardsInterface;

class MainProductFormFields extends \MageWorx\GiftCards\Model\Source
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => GiftCardsInterface::MAIL_FROM,
                'label' => __('From Name')
            ],
            [
                'value' => GiftCardsInterface::MAIL_TO,
                'label' => __('To Name')
            ],
            [
                'value' => GiftCardsInterface::MAIL_TO_EMAIL,
                'label' => __('To E-mail')
            ],
            [
                'value' => GiftCardsInterface::MAIL_MESSAGE,
                'label' => __('Message')
            ],
            [
                'value' => GiftCardsInterface::MAIL_DELIVERY_DATE,
                'label' => __('Delivery Date')
            ]
        ];
    }
}

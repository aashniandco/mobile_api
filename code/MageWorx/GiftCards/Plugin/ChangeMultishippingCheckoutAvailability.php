<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Plugin;

class ChangeMultishippingCheckoutAvailability
{
    /**
     * @var \MageWorx\GiftCards\Helper\Data
     */
    protected $helperData;

    /**
     * ChangeMultishippingCheckoutAvailability constructor.
     *
     * @param \MageWorx\GiftCards\Helper\Data $helperData
     */
    public function __construct(\MageWorx\GiftCards\Helper\Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Multishipping\Helper\Data $subject
     * @param mixed $result
     * @return bool
     */
    public function afterIsMultishippingCheckoutAvailable(\Magento\Multishipping\Helper\Data $subject, $result)
    {
        if ($result 
            && $this->helperData->getIsDisableMultishipping()
            && $subject->getQuote()->getMageworxGiftcardsDescription()
        ) {
            $result = false;
        }

        return $result;
    }
}

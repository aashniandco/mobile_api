<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block\Sales\Order\Totals;

/**
 * Class GiftCards
 *
 * @package MageWorx\GiftCards\Block\Sales\Order\Totals
 */
class GiftCards extends \Magento\Framework\View\Element\Template
{
    /**
     * Initialize mageworx_giftcards order total
     *
     * @return $this
     */
    public function initTotals()
    {
        $total = new \Magento\Framework\DataObject(
            [
                'block_name' => $this->getNameInLayout(),
                'code'       => $this->getNameInLayout(),
                'area'       => $this->getArea(),
            ]
        );

        $this->getParentBlock()->addTotal($total);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Retrieve current order model
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return mixed
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * @return mixed
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }
}

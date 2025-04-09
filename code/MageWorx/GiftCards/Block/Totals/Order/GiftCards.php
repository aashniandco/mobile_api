<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block\Totals\Order;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Sales\Block\Order\Totals;

class GiftCards extends Totals
{
    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     *
     * @param Context $context
     * @param Registry $registry
     * @param DataObjectFactory $dataObjectFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        parent::__construct($context, $registry, $data);
    }

    /**
     * Add MageWorx Gift Cards Amount to Order
     *
     * @return void
     */
    public function initTotals()
    {
        /** @var \Magento\Sales\Block\Order\Totals $totalsBlock */
        $totalsBlock = $this->getParentBlock();
        $order       = $totalsBlock->getOrder();

        if ((float)$order->getMageworxGiftcardsAmount()) {
            $data = [
                'code'       => 'mageworx_giftcards_amount',
                'label'      => $order->getMageworxGiftcardsDescription(),
                'value'      => $order->getMageworxGiftcardsAmount(),
                'base_value' => $order->getBaseMageworxGiftcardsAmount()
            ];

            /** @var \Magento\Framework\DataObject $dataObject */
            $dataObject = $this->dataObjectFactory->create($data);

            $totalsBlock->addTotalBefore($dataObject, 'grand_total');
        }
    }
}
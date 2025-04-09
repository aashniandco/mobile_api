<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block\Totals\Creditmemo;

use Magento\Sales\Block\Order\Totals;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

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
     * Add MageWorx GiftCards Amount to Credit Memo
     *
     * @return void
     */
    public function initTotals()
    {
        /** @var \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Totals $totalsBlock */
        $totalsBlock = $this->getParentBlock();
        $creditmemo  = $totalsBlock->getCreditmemo();

        if ((float)$creditmemo->getMageworxGiftcardsAmount()) {
            $data = [
                'code'       => 'mageworx_giftcards_amount',
                'label'      => $creditmemo->getMageworxGiftcardsDescription(),
                'value'      => $creditmemo->getMageworxGiftcardsAmount(),
                'base_value' => $creditmemo->getBaseMageworxGiftcardsAmount()
            ];

            /** @var \Magento\Framework\DataObject $dataObject */
            $dataObject = $this->dataObjectFactory->create($data);

            $totalsBlock->addTotalBefore($dataObject, 'grand_total');
        }
    }
}
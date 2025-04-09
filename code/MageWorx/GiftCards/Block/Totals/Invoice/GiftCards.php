<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block\Totals\Invoice;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

class GiftCards extends \Magento\Sales\Block\Order\Totals
{
    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * GiftCards constructor.
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
     * Add MageWorx Fee Amount to Invoice
     */
    public function initTotals()
    {
        /** @var \Magento\Sales\Block\Adminhtml\Order\Invoice\Totals $totalsBlock */
        $totalsBlock = $this->getParentBlock();
        $invoice     = $totalsBlock->getInvoice();

        if ((float)$invoice->getMageworxGiftcardsAmount()) {
            $data = [
                'code'       => 'mageworx_giftcards_amount',
                'label'      => $invoice->getMageworxGiftcardsDescription(),
                'value'      => $invoice->getMageworxGiftcardsAmount(),
                'base_value' => $invoice->getBaseMageworxGiftcardsAmount()
            ];

            /** @var \Magento\Framework\DataObject $dataObject */
            $dataObject = $this->dataObjectFactory->create($data);

            $totalsBlock->addTotalBefore($dataObject, 'grand_total');
        }
    }
}
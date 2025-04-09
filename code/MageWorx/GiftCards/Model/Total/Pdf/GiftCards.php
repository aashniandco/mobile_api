<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model\Total\Pdf;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;

class GiftCards extends DefaultTotal
{
    /**
     * GiftCards constructor.
     *
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        array $data = []
    ) {
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }

    /**
     * Retrieve Total amount from source
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->getSource()->getMageworxGiftcardsAmount();
    }

    /**
     * Get array of arrays with totals information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     *
     * @return array
     * @throws LocalizedException
     */
    public function getTotalsForDisplay()
    {
        /**
         * @var \Magento\Sales\Model\Order\Invoice $source
         */
        $source   = $this->getSource();
        $amount   = (float)$source->getMageworxGiftcardsAmount();
        $totals[] = [
            'amount'    => $this->formatAmountValue($amount),
            'label'     => $source->getMageworxGiftcardsDescription(),
            'font_size' => $this->getFontSize() ? $this->getFontSize() : 7
        ];

        return $totals;
    }

    /**
     * @param float $amount
     * @return string
     */
    private function formatAmountValue($amount)
    {
        $amount = $this->getOrder()->formatPriceTxt($amount);
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }

        return $amount;
    }
}
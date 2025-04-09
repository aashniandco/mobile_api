<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model\Total\Invoice;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class GiftCards extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this|AbstractTotal
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();

        if ($order->getMageworxGiftcardsAmount() < 0) {
            $orderMageworxGiftcardsAmountInvoiced     = 0;
            $orderBaseMageworxGiftcardsAmountInvoiced = 0;
            foreach ($order->getInvoiceCollection() as $oldInvoice) {
                $orderMageworxGiftcardsAmountInvoiced     += $oldInvoice->getMageworxGiftcardsAmount();
                $orderBaseMageworxGiftcardsAmountInvoiced += $oldInvoice->getBaseMageworxGiftcardsAmount();
            }
            if (($invoice->getGrandTotal() +
                    ($order->getMageworxGiftcardsAmount() - $orderMageworxGiftcardsAmountInvoiced)
                ) > 0) {
                $invoice->setMageworxGiftcardsAmount(
                    $order->getMageworxGiftcardsAmount() - $orderMageworxGiftcardsAmountInvoiced
                );
                $invoice->setBaseMageworxGiftcardsAmount(
                    $order->getBaseMageworxGiftcardsAmount() - $orderBaseMageworxGiftcardsAmountInvoiced
                );
                $invoice->setMageworxGiftcardsDescription($order->getMageworxGiftcardsDescription());
            } else {
                //for case when gift card amount more then invoice amount
                $invoice->setMageworxGiftcardsAmount(
                    -$invoice->getGrandTotal() - $orderMageworxGiftcardsAmountInvoiced
                );
                $invoice->setBaseMageworxGiftcardsAmount(
                    -$invoice->getBaseGrandTotal() - $orderBaseMageworxGiftcardsAmountInvoiced
                );
                $invoice->setMageworxGiftcardsDescription($order->getMageworxGiftcardsDescription());
            }

            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getMageworxGiftcardsAmount());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getBaseMageworxGiftcardsAmount());
        } else {
            $invoice->setMageworxGiftcardsAmount(0);
            $invoice->setBaseMageworxGiftcardsAmount(0);
            $invoice->setMageworxGiftcardsDescription('');
        }

        return $this;
    }
}

<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class GiftCards extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this|AbstractTotal
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        if ($order->getMageworxGiftcardsAmount() < 0) {
            $orderMageworxGiftcardsAmountRefunded     = 0;
            $orderBaseMageworxGiftcardsAmountRefunded = 0;
            foreach ($order->getCreditmemosCollection() as $oldCreditmemo) {
                $orderMageworxGiftcardsAmountRefunded     += $oldCreditmemo->getMageworxGiftcardsAmount();
                $orderBaseMageworxGiftcardsAmountRefunded += $oldCreditmemo->getBaseMageworxGiftcardsAmount();
            }
            if (($creditmemo->getGrandTotal() + $order->getMageworxGiftcardsAmount()
                    - $orderMageworxGiftcardsAmountRefunded) > 0) {
                $creditmemo->setMageworxGiftcardsAmount(
                    $order->getMageworxGiftcardsAmount() - $orderMageworxGiftcardsAmountRefunded
                );
                $creditmemo->setBaseMageworxGiftcardsAmount(
                    $order->getBaseMageworxGiftcardsAmount() - $orderBaseMageworxGiftcardsAmountRefunded
                );
                $creditmemo->setMageworxGiftcardsDescription($order->getMageworxGiftcardsDescription());
            } else {
                //for case when gift card amount more then creditmemo amount
                $creditmemo->setMageworxGiftcardsAmount(
                    $order->getMageworxGiftcardsAmountRefunded() - $creditmemo->getGrandTotal()
                );
                $creditmemo->setBaseMageworxGiftcardsAmount(
                    $order->getBaseMageworxGiftcardsAmountRefunded() - $creditmemo->getBaseGrandTotal()
                );
                $creditmemo->setMageworxGiftcardsDescription($order->getMageworxGiftcardsDescription());
            }
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getMageworxGiftcardsAmount());
            $creditmemo->setBaseGrandTotal(
                $creditmemo->getBaseGrandTotal() + $creditmemo->getBaseMageworxGiftcardsAmount()
            );
        } else {
            $creditmemo->setMageworxGiftcardsAmount(0);
            $creditmemo->setBaseMageworxGiftcardsAmount(0);
            $creditmemo->setMageworxGiftcardsDetails('');
        }

        return $this;
    }
}

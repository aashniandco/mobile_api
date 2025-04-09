<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */


namespace Amasty\StoreCredit\Observer;

use Amasty\StoreCredit\Api\Data\SalesFieldInterface;

class ConvertQuoteToOrder implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getData('order');
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getData('quote');
        if ($quote->getData(SalesFieldInterface::AMSC_USE)) {
            $order->setAmstorecreditBaseAmount($quote->getAmstorecreditBaseAmount());
            $order->setAmstorecreditAmount($quote->getAmstorecreditAmount());
        }
    }
}

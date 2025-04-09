<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */


namespace Amasty\StoreCredit\Observer;

use Amasty\StoreCredit\Api\Data\SalesFieldInterface;
use Amasty\StoreCredit\Api\ManageCustomerStoreCreditInterface;
use Amasty\StoreCredit\Model\History\MessageProcessor;

class DeductStoreCredit implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Amasty\StoreCredit\Api\ManageCustomerStoreCreditInterface
     */
    private $manageCustomerStoreCredit;

    public function __construct(
        ManageCustomerStoreCreditInterface $manageCustomerStoreCredit
    ) {
        $this->manageCustomerStoreCredit = $manageCustomerStoreCredit;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getData('order');
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getData('quote');
        if ($quote->getData(SalesFieldInterface::AMSC_USE)) {
            $this->manageCustomerStoreCredit->addOrSubtractStoreCredit(
                $quote->getCustomerId(),
                -$quote->getAmstorecreditBaseAmount(),
                MessageProcessor::ORDER_PAY,
                [$order->getIncrementId()],
                $quote->getStoreId()
            );
        }
    }
}

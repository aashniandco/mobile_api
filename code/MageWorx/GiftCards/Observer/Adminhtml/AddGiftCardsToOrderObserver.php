<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\GiftCards\Observer\Adminhtml;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use MageWorx\GiftCards\Api\GiftCardManagementInterface;
use Magento\Framework\Escaper;

class AddGiftCardsToOrderObserver implements ObserverInterface
{
    /**
     * @var GiftCardManagementInterface
     */
    protected $giftCardManagement;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * AddGiftCardsToOrderObserver constructor.
     *
     * @param GiftCardManagementInterface $giftCardManagement
     * @param MessageManagerInterface $messageManager
     * @param Escaper $escaper
     */
    public function __construct(
        GiftCardManagementInterface $giftCardManagement,
        MessageManagerInterface $messageManager,
        Escaper $escaper
    ) {
        $this->giftCardManagement = $giftCardManagement;
        $this->messageManager     = $messageManager;
        $this->escaper            = $escaper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $model   = $observer->getEvent()->getOrderCreateModel();
        $request = $observer->getEvent()->getRequest();
        $quote   = $model->getQuote();

        if (!empty($request['mw_giftcard']) && $quote->getId()) {
            $this->giftCardManagement->applyToCart((int)$quote->getId(), $request['mw_giftcard'], true);

            $this->messageManager->addSuccessMessage(
                __('Gift Card "%1" was applied.', $this->escaper->escapeHtml($request['mw_giftcard']))
            );
        }

        if (!empty($request['mw_giftcard_remove']) && $quote->getId()) {
            $this->giftCardManagement->removeFromCart((int)$quote->getId(), $request['mw_giftcard_remove'], true);
        }
    }
}

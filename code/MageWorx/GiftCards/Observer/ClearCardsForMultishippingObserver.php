<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Observer;

use Magento\Framework\Event\ObserverInterface;

class ClearCardsForMultishippingObserver implements ObserverInterface
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    protected $checkout;

    /**
     * ClearCardsForMultishippingObserver constructor.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $checkout
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $checkout
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->checkout        = $checkout;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $cartQuote = $this->checkout->getQuote();

        if ($cartQuote->getMageworxGiftcardsDescription()) {
            $cartQuote->setMageworxGiftcardsDescription(null);
            $cartQuote->collectTotals();
            $this->quoteRepository->save($cartQuote);
        }

        return $this;
    }
}

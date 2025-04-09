<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\App\ProductMetadataInterface;

class AddGiftcardsBlockOnCartPageObserver implements ObserverInterface
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var bool
     */
    private $afterSibling = false;

    /**
     * @var ProductMetadataInterface $productMetadata
     */
    private $productMetadata;

    /**
     * AddGiftcardsBlockOnCartPageObserver constructor.
     *
     * @param LayoutInterface $layout
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(LayoutInterface $layout, ProductMetadataInterface $productMetadata)
    {
        $this->layout          = $layout;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // For magento versions up to 2.2.0. Magento bug: https://github.com/magento/magento2/pull/3907
        if (version_compare($this->productMetadata->getVersion(), '2.2.0', '<')
            && $observer->getEvent()->getName() == 'layout_render_before_checkout_cart_index'
        ) {
            return;
        }

        if (version_compare($this->productMetadata->getVersion(), '2.2.0', '>=')
            && $observer->getEvent()->getName() == 'controller_action_postdispatch_checkout_cart_index'
        ) {
            return;
        }

        if ($parent = $this->getParentId()) {

            $blockName = 'checkout.cart.giftcards';
            $sibling   = 'checkout.cart.coupon';

            $this->layout->getParentName('checkout.cart.coupon');

            $this->layout->addBlock(\MageWorx\GiftCards\Block\Cart\GiftCards::class, $blockName, $parent);
            $this->layout->reorderChild($parent, $blockName, $sibling, $this->afterSibling);
        }
    }

    /**
     * Get name of parent element
     *
     * @return null|string
     */
    protected function getParentId()
    {
        if ($this->layout->hasElement('cart.discount')) {
            return 'cart.discount';
        }

        if ($this->layout->hasElement('cart.bottom.border')) {
            $this->afterSibling = true;

            return 'cart.bottom.border';
        }

        if ($this->layout->hasElement('cart.summary')) {
            return 'cart.summary';
        }

        return null;
    }
}

<?php
/**
 *
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\GiftCards\Model;

use Magento\Quote\Model\QuoteIdMaskFactory;
use MageWorx\GiftCards\Api\GiftCardManagementInterface;

class GuestGiftCardManagement implements \MageWorx\GiftCards\Api\GuestGiftCardManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var GiftCardManagementInterface
     */
    private $giftCardManagement;

    /**
     * GuestGiftCardManagement constructor.
     *
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param GiftCardManagementInterface $giftCardManagement
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        GiftCardManagementInterface $giftCardManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->giftCardManagement = $giftCardManagement;
    }

    /**
     * @param string $cartId
     * @param string $giftCardCode
     * @return bool
     */
    public function applyToCart(string $cartId, string $giftCardCode): bool
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->giftCardManagement->applyToCart((int)$quoteIdMask->getQuoteId(), $giftCardCode);

    }

    /**
     * @param string $cartId
     * @param string $giftCardCode
     * @return bool
     */
    public function removeFromCart(string $cartId, string $giftCardCode): bool
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->giftCardManagement->removeFromCart((int)$quoteIdMask->getQuoteId(), $giftCardCode);
    }
}

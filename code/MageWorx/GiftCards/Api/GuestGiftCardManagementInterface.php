<?php
/**
 *
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\GiftCards\Api;

/**
 * @api
 */
interface GuestGiftCardManagementInterface
{
    /**
     * @param string $cartId
     * @param string $giftCardCode
     * @return boolean
     */
    public function applyToCart(string $cartId, string $giftCardCode): bool;

    /**
     * @param string $cartId
     * @param string $giftCardCode
     * @return boolean
     */
    public function removeFromCart(string $cartId, string $giftCardCode): bool;
}

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
interface GiftCardManagementInterface
{
    /**
     * @param int $cartId
     * @param string $giftCardCode
     * @param bool $isAdmin
     * @return bool
     */
    public function applyToCart(int $cartId, string $giftCardCode, bool $isAdmin = false): bool;

    /**
     * @param int $cartId
     * @param string $giftCardCode
     * @param bool $isAdmin
     * @return bool
     */
    public function removeFromCart(int $cartId, string $giftCardCode, bool $isAdmin = false): bool;

    /**
     * @param int $giftCardId
     * @return bool
     */
    public function sendEmailWithGiftCard(int $giftCardId): bool;
}

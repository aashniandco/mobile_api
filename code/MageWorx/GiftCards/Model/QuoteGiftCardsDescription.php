<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\GiftCards\Model;

class QuoteGiftCardsDescription
{
    /**
     * @var string
     */
    protected $prefix = 'Gift Card';

    /**
     * @param array $codes
     * @return string|null
     */
    public function getDescription(array $codes): ?string
    {
        return empty($codes) ? null : $this->prefix . ' (' . implode(',', $codes) . ')';
    }

    /**
     * @param string $description
     * @return array
     */
    public function getCodes(string $description): array
    {
        $giftCards = [];

        if ($description && stripos($description, $this->prefix . ' (') !== false) {
            $description = str_replace($this->prefix . ' (', '', $description);
            $description = substr($description, 0, -1);
            $giftCards   = array_map('trim', explode(',', $description));
        }

        return $giftCards;
    }
}

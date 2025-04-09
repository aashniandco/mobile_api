<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface GiftCardsSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \MageWorx\GiftCards\Api\Data\GiftCardsInterface[]
     */
    public function getItems();

    /**
     * @param \MageWorx\GiftCards\Api\Data\GiftCardsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

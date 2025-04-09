<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;
use MageWorx\GiftCards\Api\Data\GiftCardsOrderInterface;

interface GiftCardsOrderSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return GiftCardsOrderInterface[]
     */
    public function getItems();

    /**
     * @param GiftCardsOrderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

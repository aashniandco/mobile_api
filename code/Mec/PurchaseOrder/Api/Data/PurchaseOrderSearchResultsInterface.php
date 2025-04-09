<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mec\PurchaseOrder\Api\Data;

interface PurchaseOrderSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get purchase_order list.
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface[]
     */
    public function getItems();

    /**
     * Set po_number list.
     * @param \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}


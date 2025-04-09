<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mec\PurchaseOrder\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface PurchaseOrderRepositoryInterface
{

    /**
     * Save purchase_order
     * @param \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface $purchaseOrder
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface $purchaseOrder
    );

    /**
     * Retrieve purchase_order
     * @param string $purchaseOrderId
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($purchaseOrderId);

    /**
     * Retrieve purchase_order matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Mec\PurchaseOrder\Api\Data\PurchaseOrderSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete purchase_order
     * @param \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface $purchaseOrder
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Mec\PurchaseOrder\Api\Data\PurchaseOrderInterface $purchaseOrder
    );

    /**
     * Delete purchase_order by ID
     * @param string $purchaseOrderId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($purchaseOrderId);
}


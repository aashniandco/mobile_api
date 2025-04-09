<?php
/**
 *
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use MageWorx\GiftCards\Api\Data\GiftCardsOrderSearchResultsInterface;

/**
 * @api
 */
interface GiftCardsOrderRepositoryInterface
{
    /**
     * Create Gift Card Order
     *
     * @param \MageWorx\GiftCards\Api\Data\GiftCardsOrderInterface $order
     * @param bool $saveOptions
     * @return \MageWorx\GiftCards\Api\Data\GiftCardsOrderInterface
     */
    public function save(\MageWorx\GiftCards\Api\Data\GiftCardsOrderInterface $order, $saveOptions = false);

    /**
     * Get Gift Card Order by id
     *
     * @param int $id
     * @param bool $editMode
     * @param int|null $storeId
     * @param bool $forceReload
     * @return \MageWorx\GiftCards\Api\Data\GiftCardsOrderInterface
     */
    public function get($id, $editMode = false, $storeId = null, $forceReload = false);

    /**
     * Get Gift Card Order by Gift Card Code
     *
     * @param string $cardCode
     * @param bool $editMode
     * @param int|null $storeId
     * @param bool $forceReload
     * @return \MageWorx\GiftCards\Api\Data\GiftCardsOrderInterface
     */
    public function getByGiftCardCode($giftCardCode, $editMode = false, $storeId = null, $forceReload = false);

    /**
     * Get Gift Card Order by Gift Card Id
     *
     * @param int $giftCardId
     * @param bool $editMode
     * @param int|null $storeId
     * @param bool $forceReload
     * @return \MageWorx\GiftCards\Api\Data\GiftCardsOrderInterface
     */
    public function getByGiftCardId($giftCardId, $editMode = false, $storeId = null, $forceReload = false);

    /**
     * Retrieve Gift Card Order list which match a specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return GiftCardsOrderSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Gift Card Order
     *
     * @param \MageWorx\GiftCards\Api\Data\GiftCardsOrderInterface $order
     * @return bool Will returned True if deleted
     */
    public function delete(\MageWorx\GiftCards\Api\Data\GiftCardsOrderInterface $order);

    /**
     * Delete Gift Card Order by id
     *
     * @param int $id
     * @return bool Will returned True if deleted
     */
    public function deleteById($id);

    /**
     * Delete Gift Card Order by Gift Card Id
     *
     * @param int $giftCardId
     * @return bool Will returned True if deleted
     */
    public function deleteByGiftCardId($giftCardId);

    /**
     * Delete Gift Card Order by Gift Card Code
     *
     * @param string $giftCardCode
     * @return bool Will returned True if deleted
     */
    public function deleteByGiftCardCode($giftCardCode);
}

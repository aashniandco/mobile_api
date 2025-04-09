<?php
/**
 *
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 */
interface GiftCardsRepositoryInterface
{
    /**
     * Create GiftCard with automatic generation of unique code or update GiftCard
     *
     * @param \MageWorx\GiftCards\Api\Data\GiftCardsInterface $giftCard
     * @param bool $saveOptions
     * @return \MageWorx\GiftCards\Api\Data\GiftCardsInterface
     */
    public function save(\MageWorx\GiftCards\Api\Data\GiftCardsInterface $giftCard, $saveOptions = false);

    /**
     * Create GiftCard with the specified code
     *
     * Required gift card data:
     * - card_code;          Example: "card_code": "1B3D5-A2C4E-1BCD5"
     * - card_amount;        Example: "card_amount": "100"
     *
     * Additional gift card data:
     * - card_type;          Print Type is default, "card_type": "2"
     * - storeview_ids;      All Store Views is default, "storeview_ids": [0]; multiple values - "storeview_ids": [2,5]
     * - card_status;        Inactive is default, "card_status": "0"
     * - card_balance;       Example: "card_balance": "100", the default value is equal to the card_amount value
     * - expire_date;        Example: "expire_date": "2018-07-31"
     * - customer_group_ids; Example: "customer_group_ids": [0]; multiple values - "customer_group_ids": [2,5]
     *
     * Additional data for Email Gift Card:
     * - mail_to;
     * - mail_to_email;
     * - mail_from;
     * - mail_message;
     *
     * @param \MageWorx\GiftCards\Api\Data\GiftCardsInterface $giftCard
     * @return \MageWorx\GiftCards\Api\Data\GiftCardsInterface
     */
    public function createWithSpecifiedCode(\MageWorx\GiftCards\Api\Data\GiftCardsInterface $giftCard);

    /**
     * Get GiftCard by Card Code
     *
     * @param string $giftCardCode
     * @param bool $editMode
     * @param int|null $storeId
     * @param bool $forceReload
     * @return \MageWorx\GiftCards\Api\Data\GiftCardsInterface
     */
    public function getByCode($giftCardCode, $editMode = false, $storeId = null, $forceReload = false);

    /**
     * Get Gift Card by id
     *
     * @param int $giftCardId
     * @param bool $editMode
     * @param int|null $storeId
     * @param bool $forceReload
     * @return \MageWorx\GiftCards\Api\Data\GiftCardsInterface
     */
    public function get($giftCardId, $editMode = false, $storeId = null, $forceReload = false);

    /**
     * Delete Gift Card
     *
     * @param \MageWorx\GiftCards\Api\Data\GiftCardsInterface $giftCard
     * @return bool Will returned True if deleted
     */
    public function delete(\MageWorx\GiftCards\Api\Data\GiftCardsInterface $giftCard);

    /**
     * Delete Gift Card by Card Code
     *
     * @param string $giftCardCode
     * @return bool Will returned True if deleted
     */
    public function deleteByCode($giftCardCode);

    /**
     * Delete Gift Card by Card Id
     *
     * @param string $gifCardId
     * @return bool Will returned True if deleted
     */
    public function deleteById($giftCardId);

    /**
     * @param string $giftCardCode
     * @return float|null
     */
    public function getBalanceByCode($giftCardCode);

    /**
     * @param string $giftCardCode
     * @return string
     */
    public function getStatusByCode($giftCardCode);

    /**
     * @param string $giftCardCode
     * @return string
     */
    public function getExpireDateByCode($giftCardCode);

    /**
     * Retrieve gift cards matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param bool $returnRawObjects
     * @return \MageWorx\GiftCards\Api\Data\GiftCardsSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria, $returnRawObjects = false);
}

<?php
/**
 *
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Api\Data;

interface GiftCardsOrderInterface
{

    const ENTITY_ID          = 'entity_id';
    const GIFTCARD_ID        = 'giftcard_id';
    const GIFTCARD_CODE      = 'giftcard_code';
    const ORDER_ID           = 'order_id';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const DISCOUNTED         = 'discounted';
    const CREATED_TIME       = 'created_time';
    const UPDATED_AT         = 'updated_at';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getCardId();

    /**
     * @return string|null
     */
    public function getCardCode();

    /**
     * @return int|null
     */
    public function getOrderId();

    /**
     * @return string|null
     */
    public function getOrderIncrementId();

    /**
     * @return float|null
     */
    public function getDiscounted();

    /**
     * @return date|null
     */
    public function getCreatedTime();

    /**
     * @return date|null
     */
    public function getUpdatedAt();

    /**
     * @param int
     * @return $this
     */
    public function setId($id);

    /**
     * @param int
     * @return $this
     */
    public function setCardId($cardId);

    /**
     * @param string $cardCode
     * @return $this
     */
    public function setCardCode($cardCode);

    /**
     * @param int
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * @param string
     * @return $this
     */
    public function setOrderIncrementId($orderIncrementId);

    /**
     * @param float
     * @return $this
     */
    public function setDiscounted($discounted);

    /**
     * @param date
     * @return $this
     */
    public function setCreatedTime($createdTime);

    /**
     * @param date
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}

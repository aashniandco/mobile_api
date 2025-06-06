<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model;

use MageWorx\GiftCards\Api\Data\GiftCardsOrderInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Order extends \Magento\Framework\Model\AbstractModel implements GiftCardsOrderInterface, IdentityInterface
{
    protected $cacheTag = 'mageworx_giftcards_order';

    protected $eventPrefix = 'mageworx_giftcards_order';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('MageWorx\GiftCards\Model\ResourceModel\Order');
    }

    public function getIdentities()
    {
        return [$this->cacheTag . '_' . $this->getId()];
    }

    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function getCardId()
    {
        return $this->getData(self::GIFTCARD_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getCardCode()
    {
        return $this->getData(self::GIFTCARD_CODE);
    }

    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderIncrementId()
    {
        return $this->getData(self::ORDER_INCREMENT_ID);
    }

    public function getDiscounted()
    {
        return $this->getData(self::DISCOUNTED);
    }

    public function getCreatedTime()
    {
        return $this->getData(self::CREATED_TIME);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    public function setCardId($cardId)
    {
        return $this->setData(self::GIFTCARD_ID, $cardId);
    }

    /**
     * {@inheritdoc}
     */
    public function setCardCode($cardCode)
    {
        return $this->setData(self::GIFTCARD_CODE, $cardCode);
    }

    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderIncrementId($orderIncrementId)
    {
        return $this->setData(self::ORDER_INCREMENT_ID, $orderIncrementId);
    }

    public function setDiscounted($discounted)
    {
        return $this->setData(self::DISCOUNTED, $discounted);
    }

    public function setCreatedTime($createdTime)
    {
        return $this->setData(self::CREATED_TIME, $createdTime);
    }

    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}

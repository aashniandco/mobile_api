<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model;

use MageWorx\GiftCards\Api\Data\GiftCardDetailsInterface;

/**
 * Class GiftCardDetails
 *
 * @package MageWorx\GiftCards\Model
 */
class GiftCardDetails implements GiftCardDetailsInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var float
     */
    private $baseAmount;

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @inheritdoc
     */
    public function getBaseAmount()
    {
        return $this->baseAmount;
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setBaseAmount($baseAmount)
    {
        $this->baseAmount = $baseAmount;

        return $this;
    }
}

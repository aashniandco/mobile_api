<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Api\Data;

/**
 * Interface GiftCardDetailsInterface
 *
 * @api
 */
interface GiftCardDetailsInterface
{
    /**
     * Get Id
     *
     * @return int
     */
    public function getId();

    /**
     * Get Code
     *
     * @return string
     */
    public function getCode();

    /**
     * Get Amount
     *
     * @return float
     */
    public function getAmount();

    /**
     * Get Base Amount
     *
     * @return float
     */
    public function getBaseAmount();

    /**
     * Set Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set Code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Set Amount
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Set Base Amount
     *
     * @param float $baseAmount
     * @return $this
     */
    public function setBaseAmount($baseAmount);
}

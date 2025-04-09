<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\GiftCards\Api\Data;

interface GiftCardOptionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getCardAmount(): string;

    /**
     * @param string $cardAmount
     * @return GiftCardOptionInterface
     */
    public function setCardAmount($cardAmount): GiftCardOptionInterface;

    /**
     * @return float
     */
    public function getCardAmountOther(): float;

    /**
     * @param float $cardAmountOther
     * @return GiftCardOptionInterface
     */
    public function setCardAmountOther($cardAmountOther): GiftCardOptionInterface;

    /**
     * @return string
     */
    public function getMailFrom(): string;

    /**
     * @param string $mailFrom
     * @return GiftCardOptionInterface
     */
    public function setMailFrom($mailFrom): GiftCardOptionInterface;

    /**
     * @return string
     */
    public function getMailTo(): string;

    /**
     * @param string $mailTo
     * @return GiftCardOptionInterface
     */
    public function setMailTo($mailTo): GiftCardOptionInterface;

    /**
     * @return string
     */
    public function getMailToEmail(): string;

    /**
     * @param string $mailToEmail
     * @return GiftCardOptionInterface
     */
    public function setMailToEmail($mailToEmail): GiftCardOptionInterface;

    /**
     * @return string
     */
    public function getImageUrl(): string;

    /**
     * @param string $imageUrl
     * @return GiftCardOptionInterface
     */
    public function setImageUrl($imageUrl): GiftCardOptionInterface;

     /**
     * @return string
     */
    public function getExpireDate(): string;

    /**
     * @param string $expireDate
     * @return GiftCardOptionInterface
     */
    public function setExpireDate($expireDate): GiftCardOptionInterface;

    /**
     * @return string
     */
    public function getMailMessage(): string;

    /**
     * @param string $mailMessage
     * @return GiftCardOptionInterface
     */
    public function setMailMessage($mailMessage): GiftCardOptionInterface;

    /**
     * @return string
     */
    public function getMailDeliveryDateUserValue(): string;

    /**
     * @param string $mailDeliveryDateUserValue
     * @return GiftCardOptionInterface
     */
    public function setMailDeliveryDateUserValue($mailDeliveryDateUserValue): GiftCardOptionInterface;

    /**
     * @return string
     */
    public function getMailDeliveryDate(): string;

    /**
     * @param string $mailDeliveryDate
     * @return GiftCardOptionInterface
     */
    public function setMailDeliveryDate($mailDeliveryDate): GiftCardOptionInterface;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \MageWorx\GiftCards\Api\Data\GiftCardOptionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \MageWorx\GiftCards\Api\Data\GiftCardOptionExtensionInterface $extensionAttributes
     * @return GiftCardOptionInterface
     */
    public function setExtensionAttributes(
        \MageWorx\GiftCards\Api\Data\GiftCardOptionExtensionInterface $extensionAttributes
    );
}

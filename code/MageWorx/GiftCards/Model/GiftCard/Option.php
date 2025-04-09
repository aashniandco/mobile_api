<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\GiftCards\Model\GiftCard;

use Magento\Framework\Model\AbstractExtensibleModel;
use MageWorx\GiftCards\Api\Data\GiftCardOptionInterface;
use MageWorx\GiftCards\Api\Data\GiftCardsInterface;

class Option extends AbstractExtensibleModel implements GiftCardOptionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCardAmount(): string
    {
        return (string)$this->getData(GiftCardsInterface::CARD_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCardAmount($cardAmount): GiftCardOptionInterface
    {
        return $this->setData(GiftCardsInterface::CARD_AMOUNT, $cardAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getCardAmountOther(): float
    {
        return (float)$this->getData('card_amount_other');
    }

    /**
     * {@inheritdoc}
     */
    public function setCardAmountOther($cardAmountOther): GiftCardOptionInterface
    {
        return $this->setData('card_amount_other', $cardAmountOther);
    }

    /**
     * {@inheritdoc}
     */
    public function getMailFrom(): string
    {
        return (string)$this->getData(GiftCardsInterface::MAIL_FROM);
    }

    /**
     * {@inheritdoc}
     */
    public function setMailFrom($mailFrom): GiftCardOptionInterface
    {
        return $this->setData(GiftCardsInterface::MAIL_FROM, $mailFrom);
    }

    /**
     * {@inheritdoc}
     */
    public function getMailTo(): string
    {
        return (string)$this->getData(GiftCardsInterface::MAIL_TO);
    }

    /**
     * {@inheritdoc}
     */
    public function setMailTo($mailTo): GiftCardOptionInterface
    {
        return $this->setData(GiftCardsInterface::MAIL_TO, $mailTo);
    }

    /**
     * {@inheritdoc}
     */
    public function getMailToEmail(): string
    {
         error_log(" ------getMailToEmail----------");
        return (string)$this->getData(GiftCardsInterface::MAIL_TO_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setMailToEmail($mailToEmail): GiftCardOptionInterface
    {
        error_log(" set email to----------");
        return $this->setData(GiftCardsInterface::MAIL_TO_EMAIL, $mailToEmail);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageUrl(): string
    {
         error_log(" ------getImageUrl----------");
        return (string)$this->getData(GiftCardsInterface::IMAGE_URL);
    }

    /**
     * {@inheritdoc}
     */
    public function setImageUrl($imageUrl): GiftCardOptionInterface
    {
        error_log(" ------setImageUrl--new--------".$imageUrl);
        return $this->setData(GiftCardsInterface::IMAGE_URL, $imageUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function getExpireDate(): string
    {
         error_log(" ------getexpiredate----------");
        return (string)$this->getData(GiftCardsInterface::EXPIRE_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setExpireDate($expireDate): GiftCardOptionInterface
    {
        error_log(" ------setexpiredate--new--------".$expireDate);
        return $this->setData(GiftCardsInterface::EXPIRE_DATE, $expireDate);
    }

    /**
     * {@inheritdoc}
     */
    public function getMailMessage(): string
    {
        return (string)$this->getData(GiftCardsInterface::MAIL_MESSAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setMailMessage($mailMessage): GiftCardOptionInterface
    {
        return $this->setData(GiftCardsInterface::MAIL_MESSAGE, $mailMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function getMailDeliveryDateUserValue(): string
    {
        return (string)$this->getData('mail_delivery_date_user_value');
    }

    /**
     * {@inheritdoc}
     */
    public function setMailDeliveryDateUserValue($mailDeliveryDateUserValue): GiftCardOptionInterface
    {
        return $this->setData('mail_delivery_date_user_value', $mailDeliveryDateUserValue);
    }

    /**
     * {@inheritdoc}
     */
    public function getMailDeliveryDate(): string
    {
        return (string)$this->getData(GiftCardsInterface::MAIL_DELIVERY_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setMailDeliveryDate($mailDeliveryDate): GiftCardOptionInterface
    {
        return $this->setData(GiftCardsInterface::MAIL_DELIVERY_DATE, $mailDeliveryDate);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \MageWorx\GiftCards\Api\Data\GiftCardOptionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
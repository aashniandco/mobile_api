<?php
/**
 *
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Api\Data;

interface GiftCardsInterface
{
    const CARD_ID                     = 'card_id';
    const CARD_CODE                   = 'card_code';
    const CUSTOMER_ID                 = 'customer_id';
    const CUSTOMER_GROUP_ID           = 'customer_group_id';
    const ORDER_ID                    = 'order_id';
    const PRODUCT_ID                  = 'product_id';
    const CARD_CURRENCY               = 'card_currency';
    const CARD_AMOUNT                 = 'card_amount';
    const CARD_BALANCE                = 'card_balance';
    const CARD_STATUS                 = 'card_status';
    const CARD_TYPE                   = 'card_type';
    const MAIL_FROM                   = 'mail_from';
    const MAIL_TO                     = 'mail_to';
    const MAIL_TO_EMAIL               = 'mail_to_email';
    const IMAGE_URL                   = 'image_url';
    const MAIL_MESSAGE                = 'mail_message';
    const OFFLINE_COUNTRY             = 'offline_country';
    const OFFLINE_STATE               = 'offline_state';
    const OFFLINE_CITY                = 'offline_city';
    const OFFLINE_STREET              = 'offline_street';
    const OFFLINE_ZIP                 = 'offline_zip';
    const OFFLINE_PHONE               = 'offline_phone';
    const MAIL_DELIVERY_DATE          = 'mail_delivery_date';
    const DELIVERY_STATUS             = 'delivery_status';
    const CREATED_TIME                = 'created_time';
    const UPDATED_AT                  = 'updated_at';
    const STORE_ID                    = 'store_id';
    const STORE_ID_FOR_EMAIL          = 'store_id_for_email';
    const STORE_CODE                  = 'store_code';
    const GROUP_ID                    = 'group_id';
    const GROUP_NAME                  = 'group_name';
    const EXPIRE_DATE                 = 'expire_date';
    const EXPIRED_EMAIL_SEND          = 'expired_email_send';
    const EXPIRATION_ALERT_EMAIL_SEND = 'expiration_alert_email_send';
    const ALL                         = 'ALL';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return string|null
     */
    public function getCardCode();

    /**
     * @return int|null
     */
    public function getCustomerId();

    /**
     * @return int|null
     */
    public function getOrderId();

    /**
     * @return int|null
     */
    public function getProductId();

    /**
     * @return string|null
     */
    public function getCardCurrency();

    /**
     * @return float|null
     */
    public function getCardAmount();

    /**
     * @return float|null
     */
    public function getCardBalance();

    /**
     * @return int|null
     */
    public function getCardStatus();

    /**
     * @return int|null
     */
    public function getCardType();

    /**
     * @return string|null
     */
    public function getMailFrom();

    /**
     * @return string|null
     */
    public function getMailTo();

    /**
     * @return string|null
     */
    public function getMailToEmail();


    /**
     * @return string|null
     */
    public function getImageUrl();


    /**
     * @return string|null
     */
    public function getMailMessage();

    /**
     * @return string|null
     */
    public function getOfflineCountry();

    /**
     * @return string|null
     */
    public function getOfflineState();

    /**
     * @return string|null
     */
    public function getOfflineCity();

    /**
     * @return string|null
     */
    public function getOfflineStreet();

    /**
     * @return string|null
     */
    public function getOfflineZip();

    /**
     * @return string|null
     */
    public function getOfflinePhone();

    /**
     * @return string|date|null
     */
    public function getMailDeliveryDate();

    /**
     * @return int|null
     */
    public function getDeliveryStatus();

    /**
     * @return string|date|null
     */
    public function getCreatedTime();

    /**
     * @return string|date|null
     */
    public function getUpdatedAt();

    /**
     * @return string[]
     */
    public function getStoreviewIds();

    /**
     * @return int
     */
    public function getStoreIdForEmailSending();

    /**
     * @return string[]
     */
    public function getCustomerGroupIds();

    /**
     * @return string
     */
    public function getExpireDate();

    /**
     * @return boolean
     */
    public function getExpiredEmailSend();

    /**
     * @return boolean
     */
    public function getExpirationAlertEmailSend();

    /**
     * @param int
     * @return $this
     */
    public function setId($id);

    /**
     * @param string
     * @return $this
     */
    public function setCardCode($cardCode);

    /**
     * @param int
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * @param int
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * @param int
     * @return $this
     */
    public function setProductId($productId);

    /**
     * @param string
     * @return $this
     */
    public function setCardCurrency($cardCurrency);

    /**
     * @param float
     * @return $this
     */
    public function setCardAmount($cardAmount);

    /**
     * @param float
     * @return $this
     */
    public function setCardBalance($cardBalance);

    /**
     * @param smallint
     * @return $this
     */
    public function setCardStatus($cardStatus);

    /**
     * @param smallint
     * @return $this
     */
    public function setCardType($cardType);

    /**
     * @param string
     * @return $this
     */
    public function setMailFrom($mailFrom);

    /**
     * @param string
     * @return $this
     */
    public function setMailTo($mailTo);

    /**
     * @param string
     * @return $this
     */
    public function setMailToEmail($mailToEmail);


    /**
     * @param string
     * @return $this
     */
    public function setImageUrl($imageUrl);

    /**
     * @param text
     * @return $this
     */
    public function setMailMessage($mailMessage);

    /**
     * @param string
     * @return $this
     */
    public function setOfflineCountry($offlineCountry);

    /**
     * @param string
     * @return $this
     */
    public function setOfflineState($offlineState);

    /**
     * @param string
     * @return $this
     */
    public function setOfflineCity($offlineCity);

    /**
     * @param string
     * @return $this
     */
    public function setOfflineStreet($offlineStreet);

    /**
     * @param string
     * @return $this
     */
    public function setOfflineZip($offlineZip);

    /**
     * @param string
     * @return $this
     */
    public function setOfflinePhone($offlinePhone);

    /**
     * @param date
     * @return $this
     */
    public function setMailDeliveryDate($mailDeliveryDate);

    /**
     * @param int $deliveryStatus
     * @return $this
     */
    public function setDeliveryStatus($deliveryStatus);

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

    /**
     * @param string|array
     * @return $this
     */
    public function setStoreviewIds($storeviewIds);

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreIdForEmailSending($storeId);

    /**
     * @param string|array
     * @return $this
     */
    public function setCustomerGroupIds($customerGroupIds);

    /**
     * @param $expireDate
     * @return $this
     */
    public function setExpireDate($expireDate);

    /**
     * @param $expireEmailSend
     * @return $this
     */
    public function setExpiredEmailSend($expireEmailSend);

    /**
     * @param $expirationAlertEmailSend
     * @return $this
     */
    public function setExpirationAlertEmailSend($expirationAlertEmailSend);
}

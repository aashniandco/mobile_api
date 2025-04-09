<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\MailException;
use MageWorx\GiftCards\Model\GiftCards;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime;

class CheckoutSubmitAllAfterObserver implements ObserverInterface
{
    protected $giftCardsSession;
    protected $giftCardsCollection;
    protected $giftCardsOrderCollection;
    protected $giftCardsRepository;
    protected $giftCardsOrderRepository;
    protected $giftCardsFactory;
    protected $giftCardsOrderFactory;
    protected $helper;
    protected $logger;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * CheckoutSubmitAllAfterObserver constructor.
     *
     * @param \MageWorx\GiftCards\Model\Session $giftcardsSession
     * @param \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory $giftcardsCollection
     * @param \MageWorx\GiftCards\Model\ResourceModel\Order\CollectionFactory $giftcardsOrderCollection
     * @param \MageWorx\GiftCards\Model\GiftCardsRepository $giftCardsRepository
     * @param \MageWorx\GiftCards\Model\OrderRepository $giftCardsOrderRepository
     * @param \MageWorx\GiftCards\Model\GiftCardsFactory $giftCardsFactory
     * @param \MageWorx\GiftCards\Model\OrderFactory $giftCardsOrderFactory
     * @param \MageWorx\GiftCards\Helper\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        \MageWorx\GiftCards\Model\Session $giftcardsSession,
        \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory $giftcardsCollection,
        \MageWorx\GiftCards\Model\ResourceModel\Order\CollectionFactory $giftcardsOrderCollection,
        \MageWorx\GiftCards\Model\GiftCardsRepository $giftCardsRepository,
        \MageWorx\GiftCards\Model\OrderRepository $giftCardsOrderRepository,
        \MageWorx\GiftCards\Model\GiftCardsFactory $giftCardsFactory,
        \MageWorx\GiftCards\Model\OrderFactory $giftCardsOrderFactory,
        \MageWorx\GiftCards\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        TimezoneInterface $localeDate
    ) {
        $this->giftCardsSession         = $giftcardsSession;
        $this->giftCardsCollection      = $giftcardsCollection;
        $this->giftCardsOrderCollection = $giftcardsOrderCollection;
        $this->giftCardsRepository      = $giftCardsRepository;
        $this->giftCardsOrderRepository = $giftCardsOrderRepository;
        $this->giftCardsFactory         = $giftCardsFactory;
        $this->giftCardsOrderFactory    = $giftCardsOrderFactory;
        $this->helper                   = $helper;
        $this->logger                   = $logger;
        $this->localeDate               = $localeDate;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();

        if ($quote->getMageworxGiftcardsProcessed()) {
            return $this;
        }

        $order = $observer->getOrder();

        if (!$order && $observer->getOrders()) {
            $orders = $observer->getOrders();
            $order  = $orders[0];
        }

        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            if ($product->getTypeId() == 'mageworx_giftcards') {
                $options        = $product->getCustomOptions();
                $optionsDataMap = [
                    'card_type',
                    'mail_to',
                    'mail_to_email',
                    'mail_from',
                    'mail_message',
                    'offline_country',
                    'offline_state',
                    'offline_city',
                    'offline_street',
                    'offline_zip',
                    'offline_phone',
                    'mail_delivery_date',
                    'card_currency',
                    'image_url' 
                ];

                $data = [];
                foreach ($optionsDataMap as $field) {
                    if (isset($options[$field])) {
                        $data[$field] = $options[$field]->getValue();
                    }
                }
                
                $data['card_amount']                 = $item->getCalculationPrice() + $item->getTaxAmount();
                $data['product_id']                  = $product->getId();
                $data['card_status']                 = GiftCards::STATUS_INACTIVE;
                $data['order_id']                    = $order->getId();
                $data['expire_date']                 = $this->helper->getExpireDateForProduct($product);
                $data['store_id']                    = [$order->getStoreId()];
                $data[GiftCards::STORE_ID_FOR_EMAIL] = $order->getStoreId();
                $data['customer_id']                 = $order->getCustomerId();
                $data['customer_group_id']           = explode(',', $product->getMageworxGcCustomerGroups());
               
                if (!empty($data[GiftCards::MAIL_DELIVERY_DATE])) {
                    $data[GiftCards::MAIL_DELIVERY_DATE] = $this->localeDate
                        ->date($data[GiftCards::MAIL_DELIVERY_DATE], null, true, false)
                        ->format(DateTime::DATE_PHP_FORMAT);
                }

                $orderSendEmailStatuses  = explode(',', $this->helper->getOrderStatuses());
                $orderActivationStatuses = explode(',', $this->helper->getCardActivationOrderStatuses());

                $orderItem   = $order->getItemByQuoteItemId($item->getId());
                $productName = $orderItem->getName();
                for ($i = 0; $i < $item->getQty(); $i++) {
                    $model = $this->giftCardsFactory->create();
                    $model->setData($data);

                    if (in_array($order->getStatus(), $orderActivationStatuses)) {
                        $model->setCardStatus(GiftCards::STATUS_ACTIVE);
                    } else {
                        $model->setCardStatus(GiftCards::STATUS_INACTIVE);
                    }

                    $giftCard = null;

                    if (in_array($order->getStatus(), $orderSendEmailStatuses)) {

                        if ($model->hasValidMailDeliveryDate($order->getStore())) {
                            $model->setDeliveryStatus(GiftCards::STATUS_NEED_SEND_BY_CRON);
                        } else {
                            $giftCard = $this->giftCardsRepository->save($model);

                            try {
                                $giftCard->send($order);
                                $giftCard->setDeliveryStatus(GiftCards::STATUS_DELIVERED);
                            } catch (MailException $e) {
                                $giftCard->setMailDeliveryDate(null);
                                $giftCard->setDeliveryStatus(GiftCards::STATUS_NEED_SEND_BY_CRON);
                                $this->logger->critical($e);
                            }
                            $giftCard = $this->giftCardsRepository->save($giftCard);
                        }
                    }

                    if (is_null($giftCard)) {
                        $giftCard = $this->giftCardsRepository->save($model);
                    }

                    $productName .= ', ' . $giftCard->getCardCode();
                }
                if ($this->helper->getAddCodeToProduct()) {
                    $orderItem->setName($productName);
                    $orderItem->save();
                }
            }
        }

        if ((bool)$this->giftCardsSession->getActive() === true
            && $giftCardsIds = $this->giftCardsSession->getGiftCardsIds()
        ) {
            $ids          = array_keys($giftCardsIds);
            $frontOptions = $this->giftCardsSession->getFrontOptions();

            foreach ($ids as $card_id) {
                $card = $this->giftCardsRepository->get($card_id);

                /** @var \MageWorx\GiftCards\Model\Order $oGiftCardOrder */
                $oGiftCardOrder = $this->giftCardsOrderFactory->create();

                $useAmount = $frontOptions[$card_id]['applied'];

                if ($useAmount > 0) {
                    $card->setCardBalance($frontOptions[$card->getId()]['card_remaining']);

                    if ($card->getCardBalance() == 0) {
                        $card->setCardStatus(
                            GiftCards::STATUS_USED
                        ); //set status to 'used' when gift card balance is 0;
                    } else {
                        $card->setCardStatus(GiftCards::STATUS_ACTIVE);
                    }

                    $card = $this->giftCardsRepository->save($card);

                    $oGiftCardOrder->setGiftcardId($card->getId());
                    $oGiftCardOrder->setCardCode($card->getCardCode());
                    $oGiftCardOrder->setOrderId($order->getId());
                    $oGiftCardOrder->setOrderIncrementId($order->getIncrementId());
                    $oGiftCardOrder->setDiscounted((float)$useAmount);
                    $this->giftCardsOrderRepository->save($oGiftCardOrder);
                }
            }
            $order->save();
        }
        $this->giftCardsSession->setActive(0);
        $this->giftCardsSession->setFrontOptions(null);
        $this->giftCardsSession->setGiftCardsIds(null);

        $quote->setMageworxGiftcardsProcessed(true);

        return $this;
    }
}

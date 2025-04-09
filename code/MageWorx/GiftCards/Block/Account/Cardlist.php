<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block\Account;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use MageWorx\GiftCards\Model\ResourceModel\Order\CollectionFactory as GiftCardsOrderCollectionFactory;
use MageWorx\GiftCards\Model\ResourceModel\Order\Collection as GiftCardsOrderCollection;

/**
 * Giftcards history block
 */
class Cardlist extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory
     */
    protected $giftcardsCollectionFactory;

    /**
     * @var GiftCardsOrderCollectionFactory
     */
    private $giftcardOrderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    protected $giftcards;

    /**
     * @var GiftCardsOrderCollection
     */
    private $orders;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \MageWorx\GiftCards\Helper\Price
     */
    protected $helperPrice;

    /**
     * Cardlist constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory $giftcardsCollectionFactory
     * @param GiftCardsOrderCollectionFactory $giftcardOrderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param \MageWorx\GiftCards\Helper\Price $helperPrice
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory $giftcardsCollectionFactory,
        GiftCardsOrderCollectionFactory $giftcardOrderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        PriceCurrencyInterface $priceCurrency,
        \MageWorx\GiftCards\Helper\Price $helperPrice,
        array $data = []
    ) {
        $this->giftcardsCollectionFactory     = $giftcardsCollectionFactory;
        $this->giftcardOrderCollectionFactory = $giftcardOrderCollectionFactory;
        $this->customerSession                = $customerSession;
        $this->orderRepository                = $orderRepository;
        $this->priceCurrency                  = $priceCurrency;
        $this->helperPrice                    = $helperPrice;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Gift Cards'));
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getGiftcards()
    {
        if (!$this->customerSession->getCustomerId()) {
            return false;
        }
        $customerEmail = $this->customerSession->getCustomer()->getEmail();

        if (!$this->giftcards) {
            $this->giftcards = $this->giftcardsCollectionFactory->create()->addFieldToFilter(
                'mail_to_email',
                $customerEmail
            )->setOrder(
                'created_time',
                'desc'
            );
        }

        return $this->giftcards;
    }

    /**
     * @return bool|GiftCardsOrderCollection
     */
    public function getOrders()
    {
        if (!$this->customerSession->getCustomerId()) {
            return false;
        }

        $customerEmail = $this->customerSession->getCustomer()->getEmail();

        if (!$this->orders) {
            $this->orders = $this->giftcardOrderCollectionFactory->create();

            $this->orders
                ->addFieldToFilter(
                    'discounted',
                    ['notnull' => true]
                )->join(
                    ['giftcards' => $this->orders->getTable('mageworx_giftcards_card')],
                    'giftcard_id = giftcards.card_id',
                    'mail_to_email'
                )->addFieldToFilter(
                    'mail_to_email',
                    $customerEmail
                )->setOrder(
                    'created_time',
                    'desc'
                )->join(
                    ['orders' => $this->orders->getTable('sales_order')],
                    'main_table.order_id = orders.entity_id',
                    'base_currency_code'
                );
        }

        return $this->orders;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getGiftcards()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'giftcards.list.pager'
            )->setCollection(
                $this->getGiftcards()
            );
            $this->setChild('pager', $pager);
            $this->getGiftcards()->load();
        }

        if ($this->getOrders()) {
            $ordersPager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'giftcard.order.list.pager'
            );

            $ordersPager
                ->setLimitVarName('orders_limit')
                ->setPageVarName('orders_p')
                ->setCollection($this->getOrders());

            $this->setChild('orders_pager', $ordersPager);
            $this->getOrders()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getOrdersPagerHtml()
    {
        return $this->getChildHtml('orders_pager');
    }

    /**
     * @param object $order
     * @return string
     */
    public function getViewUrl($orderId)
    {
        if ($orderId > 0) {
            return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
        } else {
            return $this->getUrl('*/*/*');
        }
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * @param $orderId
     * @return null|string
     */
    public function getRealOrderId($orderId)
    {
        if ($orderId > 0) {
            $order = $this->orderRepository->get($orderId);

            if ($order && $order->getId()) {
                return $order->getIncrementId();
            }
        }

        return '';
    }

    /**
     * @param $price
     * @param string $cardCurrency
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function formatPrice($price, $cardCurrency = null)
    {
        if (isset($cardCurrency)) {
            $price = $this->helperPrice->convertCardCurrencyToStoreCurrency(
                $price,
                $this->_storeManager->getStore(),
                $cardCurrency
            );
        }

        return $this->priceCurrency->format(
            $price,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            0
        );
    }
}
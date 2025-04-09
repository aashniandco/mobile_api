<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block;

use Magento\Sales\Api\OrderRepositoryInterface;


class PrintCard extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var mixed
     */
    protected $giftCard;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \MageWorx\GiftCards\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $productImageModel;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \MageWorx\GiftCards\Helper\Price
     */
    protected $helperPrice;

    /**
     * PrintCard constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \MageWorx\GiftCards\Helper\Data $helper
     * @param \Magento\Catalog\Helper\Image $productImageModel
     * @param OrderRepositoryInterface $orderRepository
     * @param \MageWorx\GiftCards\Helper\Price $helperPrice
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \MageWorx\GiftCards\Helper\Data $helper,
        \Magento\Catalog\Helper\Image $productImageModel,
        OrderRepositoryInterface $orderRepository,
        \MageWorx\GiftCards\Helper\Price $helperPrice,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry          = $registry;
        $this->giftCard          = $this->registry->registry('mageworx_print_giftcard');
        $this->checkoutHelper    = $checkoutHelper;
        $this->helper            = $helper;
        $this->orderRepository   = $orderRepository;
        $this->helperPrice       = $helperPrice;
        $this->escaper           = $context->getEscaper();
        $this->productImageModel = $productImageModel;
        $this->assetRepo         = $context->getAssetRepository();
    }

    public function getGiftCard()
    {
        return $this->giftCard;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAmount()
    {
        $price = $this->helperPrice->convertCardCurrencyToStoreCurrency(
            $this->giftCard->getCardAmount(),
            $this->_storeManager->getStore(),
            $this->giftCard->getCardCurrency()
        );

        return $this->checkoutHelper->formatPrice($price);
    }

    public function getFrontendName()
    {
        return $this->helper->getStoreName();
    }

    public function getOrder()
    {
        $order = null;
        if ($orderId = $this->giftCard->getOrderId()) {
            $order = $this->orderRepository->get($orderId);
        }

        return $order;
    }

    public function getProductImageUrl($orderItem)
    {
        $product  = $orderItem->getProduct();
        $imageUrl = $this->productImageModel->init($product, 'image')->getUrl();

        return $imageUrl;
    }

    public function getDefaultImageUrl()
    {
        return $this->assetRepo->getUrl('MageWorx_GiftCards::images/giftcard.png');
    }

    public function getSupportMail()
    {
        return $this->helper->getSupportMail();
    }

    public function getPictureHtml()
    {
        return '<img src="' . $this->giftCard->getPictureUrl() . '" alt="" />';
    }
}

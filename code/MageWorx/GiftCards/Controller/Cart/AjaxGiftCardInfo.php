<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Controller\Cart;

use MageWorx\GiftCards\Helper\Price as HelperPrice;

/**
 * Class AjaxGiftCardInfo
 *
 * @package MageWorx\GiftCards\Controller\Cart
 */
class AjaxGiftCardInfo extends \Magento\Framework\App\Action\Action
{
    /**
     * Gift Cards Factory
     *
     * var@ \MageWorx\GiftCards\GiftCardsRepository
     */
    protected $giftCardsRepository;

    /**
     * @var  \Magento\Checkout\Helper\Data
     */
    public $checkoutHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var HelperPrice
     */
    protected $helperPrice;

    /**
     * AjaxGiftCardInfo constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \MageWorx\GiftCards\Model\GiftCardsRepository $giftCardsRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param HelperPrice $helperPrice
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \MageWorx\GiftCards\Model\GiftCardsRepository $giftCardsRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Escaper $escaper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        HelperPrice $helperPrice
    ) {
        parent::__construct($context);
        $this->giftCardsRepository = $giftCardsRepository;
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->escaper             = $escaper;
        $this->checkoutHelper      = $checkoutHelper;
        $this->storeManager        = $storeManager;
        $this->helperPrice         = $helperPrice;
    }

    /**
     * Initialize coupon
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $result      = $this->resultJsonFactory->create();
            $arrayResult = [];

            $giftCardCode = trim((string)$this->getRequest()->getParam('giftcard_code'));
            error_log($giftCardCode."giftcard_code");
            try {
                $card = $this->giftCardsRepository->getByCode($giftCardCode);
                if ($card->getId()) {
                    $cardBalance = $this->helperPrice->convertCardCurrencyToStoreCurrency(
                        $card->getCardBalance(),
                        $this->storeManager->getStore(),
                        $card->getCardCurrency()
                    );

                    $arrayResult = [
                        'success'   => true,
                        'status'    => __($card->getCardStatusLabel()),
                        'balance'   => $this->checkoutHelper->formatPrice($cardBalance),
                        'validTill' => $card->getExpireDate() ? $card->getExpireDate() : __('Unlimited'),
                    ];
                
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $arrayResult = [
                    'success' => false,
                    'message' => __('Gift Card "%1" is not valid.', $this->escaper->escapeHtml($giftCardCode))
                ];
            }

            return $result->setData($arrayResult);
        }
    }
}

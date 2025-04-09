<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Controller\Cart;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class AjaxGiftCardInfo
 *
 * @package MageWorx\GiftCards\Controller\Cart
 */
class RemainingGiftCardInfo extends \Magento\Framework\App\Action\Action
{
    /**
     * Gift Cards Factory
     *
     * var@ \MageWorx\GiftCards\Model\Session
     */
    protected $giftCardSession;

    /**
     * RemainingGiftCardInfo constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param \MageWorx\GiftCards\Model\Session $giftCardSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Escaper $escaper,
        \MageWorx\GiftCards\Model\Session $giftCardSession
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->escaper           = $escaper;
        $this->giftCardSession   = $giftCardSession;
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
            $giftcards   = [];
            $data        = [];

            $frontOptions = $this->giftCardSession->getFrontOptions();

            try {
                if ($frontOptions && is_array($frontOptions)) {
                    foreach ($frontOptions as $key => $option) {
                        $data            = [
                            'remaining' => $option['remaining'],
                            'code'      => $option['code'],
                            'applied'   => $option['applied']
                        ];
                        $giftcards[$key] = $data;
                    }
                }
                $arrayResult = [
                    'success'   => true,
                    'giftcards' => $giftcards
                ];
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $arrayResult = [
                    'success'   => true,
                    'giftcards' => []
                ];
            }

            return $result->setData($arrayResult);
        }
    }
}
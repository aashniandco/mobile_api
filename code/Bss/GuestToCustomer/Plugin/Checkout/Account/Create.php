<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    BSS_GuestToCustomer
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GuestToCustomer\Plugin\Checkout\Account;

use Bss\GuestToCustomer\Model\ResourceModel\Guest;

class Create
{
    /**
     * Checkout Session
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * CustomerSession
     * @var \Magento\Customer\Model\Session $customerSession
     */
    protected $customerSession;

    /**
     * Order Factory
     * @var \Magento\Sales\Model\OrderFactory $orderFactory
     */
    protected $orderFactory;

    /**
     * Resource Guest
     * @var Guest $resourceGuest
     */
    protected $resourceGuest;

    /**
     * JsonFactory
     * @var JsonFactory $resultJson
     */
    protected $resultJson;

    /**
     * Create constructor.
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJson
     * @param Guest $resourceGuest
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson,
        Guest $resourceGuest
    ) {
        $this->customerSession = $customerSession;
        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $checkoutSession;
        $this->resourceGuest = $resourceGuest;
        $this->resultJson = $resultJson;
    }

    /**
     * After Execute
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \Magento\Checkout\Controller\Account\Create $subject
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function afterExecute(\Magento\Checkout\Controller\Account\Create $subject)
    {
        if (!$this->customerSession->isLoggedIn()) {
            $orderId = $this->checkoutSession->getLastOrderId();

            if ($orderId) {
                $order = $this->orderFactory->create()->load($orderId);
                $emailCustomer = $order->getData('customer_email');
                $where = [
                    'email = ?' => (string)$emailCustomer
                ];
                $this->resourceGuest->deleteGuest($where);
                return $this->resultJson->create()->setData(
                    [
                        'errors' => false,
                        'message' => __('A letter with further instructions will be sent to your email.')
                    ]
                );
            }
        }
        if ($this->customerSession->isLoggedIn()) {
            return $this->resultJson->create()->setData(
                [
                    'errors' => true,
                    'message' => __('Customer is already registered')
                ]
            );
        }
        return $this->resultJson->create()->setData(
            [
                'errors' => true,
                'message' => __('Customer is already registered')
            ]
        );
    }
}

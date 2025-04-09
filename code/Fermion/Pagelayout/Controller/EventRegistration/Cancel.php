<?php

namespace Fermion\Pagelayout\Controller\EventRegistration;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\ResourceConnection;

class Cancel extends \PayUIndia\Payu\Controller\PayuAbstract {
    protected $resultRedirectFactory;
    protected $resource;

    public function __construct(
        Context $context,
        ResourceConnection $resource,
        RedirectFactory $resultRedirectFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Psr\Log\LoggerInterface $logger,
        \PayUIndia\Payu\Model\Payu $paymentMethod,
        \PayUIndia\Payu\Helper\Payu $checkoutHelper,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resource = $resource;
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $quoteRepository,
            $orderFactory,
            $logger,
            $paymentMethod,
            $checkoutHelper,
            $cartManagement,
            $resultJsonFactory
        );
    }

    public function execute() {
        $params = $this->getRequest()->getParams();
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('event_registration');
        $transactionId = $params['txnid']; // Use txnid to identify the entry

        $connection->update(
                $tableName,
                ['status' => 'canceled'],
                ['transaction_id = ?' => $transactionId]
            );

        $this->messageManager->addErrorMessage(__('Your payment was canceled.'));

        // Redirect the user back to the event registration page or a specific page
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('pagelayout/eventregistration/visitorform');

        return $resultRedirect;
    }

}

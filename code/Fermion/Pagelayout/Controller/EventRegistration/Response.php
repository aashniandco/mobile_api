<?php

namespace Fermion\Pagelayout\Controller\EventRegistration;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\ResourceConnection;

class Response extends \PayUIndia\Payu\Controller\PayuAbstract {
    protected $resultRedirectFactory;
    protected $resource;

    public function __construct(
        Context $context,
        RedirectFactory $resultRedirectFactory,
        ResourceConnection $resource,
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

    public function execute()
    {
        // Get PayU response parameters
        $params = $this->getRequest()->getParams();
        $status = isset($params['status']) ? $params['status'] : null;

        // Load your custom registration data using the transaction ID
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('event_registration');
        $transactionId = $params['txnid']; // Use txnid to identify the entry

        if ($status === 'success') {
            // Handle success
            $connection->update(
                $tableName,
                ['status' => 'paid'],
                ['transaction_id = ?' => $transactionId]
            );

            sendSuccessEmail($email, $username, $eventName, $eventDate, $transactionId, $scopeConfig, $transportBuilder, $storeManager);

            $this->messageManager->addSuccessMessage(__('Your payment was successful. Thank you for registering!'));
            // Redirect to a success page
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('pagelayout/eventregistration/visitorform');

        } else {
            // Handle failure
            $connection->update(
                $tableName,
                ['status' => 'failed'],
                ['transaction_id = ?' => $transactionId]
            );

            $this->messageManager->addErrorMessage(__('Payment failed. Please try again.'));
            // Redirect back to the form or a failure page
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('pagelayout/eventregistration/visitorform');
        }

        return $resultRedirect;
    }

    function sendSuccessEmail($email, $username, $eventName, $eventDate, $transactionId, $scopeConfig, $transportBuilder, $storeManager)
        {
            $templateId = 29; 

            $vars = [
                'username' => $username,
                'eventName' => $eventName,
                'eventDate' => $eventDate,
                'transactionId' => $transactionId,
            ];

            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeManager->getStore()->getId()
            ];

            $storeName = $scopeConfig->getValue('trans_email/ident_sales/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $storeEmail = $scopeConfig->getValue('trans_email/ident_sales/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $sender = [
                'name' => $storeName,
                'email' => $storeEmail
            ];

            try {
                $transport = $transportBuilder
                    ->setTemplateIdentifier($templateId)
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($vars)
                    ->setFrom($sender)
                    ->addTo($email)
                    ->getTransport();

                $transport->sendMessage();
                error_log("Success email sent successfully to {$email}.");
            } catch (\Exception $e) {
                error_log('Error sending success email: ' . $e->getMessage());
            }
        }



}

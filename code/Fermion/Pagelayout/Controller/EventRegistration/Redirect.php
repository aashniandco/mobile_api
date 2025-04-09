<?php

namespace Fermion\Pagelayout\Controller\EventRegistration;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;

class Redirect extends \PayUIndia\Payu\Controller\PayuAbstract {
    protected $_resource;

    public function __construct(
        Context $context,
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

        // Include other dependencies you might need
    ) {
        $this->_resource = $resource;
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
        // Check if this request is for the event registration form
        if ($this->getRequest()->getParam('is_event_registration')) {

            $connection = $this->_resource->getConnection();
            $tableName = $this->_resource->getTableName('event_registration');

            $select = $connection->select()
                ->from($tableName, ['transaction_id'])
                ->order('registration_id DESC') 
                ->limit(1);

            $latestTransactionId = $connection->fetchOne($select);
            // Extract the numeric part of the latest transaction ID
            $latestNumber = (int) filter_var($latestTransactionId, FILTER_SANITIZE_NUMBER_INT);

            // Increment the numeric part to get the new transaction ID
            $newTransactionId = 'TESTEVENT' . ($latestNumber + 1);

            $txnid = $newTransactionId;
            $firstname = $this->getRequest()->getParam('firstname');
            $lastname = $this->getRequest()->getParam('lastname');
            $user_email = $this->getRequest()->getParam('user_email');
            $ticket_qty = $this->getRequest()->getParam('ticket_qty');
            $amount = $this->getRequest()->getParam('amount');
            $phone = $this->getRequest()->getParam('phone');
            $country = $this->getRequest()->getParam('country');
            $sourceOfInfo = $this->getRequest()->getParam('sourceOfInfo');
            $city = $this->getRequest()->getParam('city');
            $udf5 = 'Magento_v.2.1.3';
            $key = 'cZBBGW';
            $productinfo = $txnid;
            $ticket_price = 2500;

            $expected_amount = $ticket_qty * $ticket_price;

            if ($ticket_qty <= 0 || $amount != $expected_amount) {
                $this->messageManager->addErrorMessage(__('Invalid quantity or amount. Please check and try again.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('pagelayout/eventregistration/visitorform');
                exit;
            }


            // Store form data into your custom table
            $connection = $this->_resource->getConnection();
            $tableName = $this->_resource->getTableName('event_registration');

            $data = [
                'transaction_id' => $txnid,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'currency' => 'INR',
                'status' => 'pending', 
                'created_at' => date('Y-m-d H:i:s'),
                'user_email' => $user_email,
                'amount' => $amount,
                'user_phone' => $phone,
                'country' => $country,
                'sourceOfInfo' => $sourceOfInfo,
                'city' => $city,
                'ticket_qty' => $ticket_qty,
            ];

            $connection->insert($tableName, $data);

            // Prepare the PayU payment request for the event registration
            $params = [];
            $params["fields"] = [
                'key' => $key,
                'txnid' => $txnid,
                'amount' => $amount,
                'productinfo' => $productinfo,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'country' => $country,
                'email' => $user_email,
                'udf5' => $udf5,
                'phone' => $phone,
                'curl' => 'https://orders.aashniandco.com/pagelayout/eventregistration/cancel/',
                'furl' => 'https://orders.aashniandco.com/pagelayout/eventregistration/response/',
                'surl' => 'https://orders.aashniandco.com/pagelayout/eventregistration/response/',
                'hash' => $this->getPaymentMethod()->generatePayuHash($txnid, $amount, $productinfo, $firstname, $user_email, $udf5)
            ];

            $params["url"] = $this->getPaymentMethod()->getCgiUrl();

            // Return the JSON response with PayU payment form fields
            return $this->resultJsonFactory->create()->setData($params);

        }

    }

}

<?php

namespace Fermion\Pagelayout\Controller\EventRegistration;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Action\Action;

class SurveyAjax extends Action {
    protected $_resource;

    public function __construct(
        Context $context,
        ResourceConnection $resource
        // Include other dependencies you might need
    ) {
        $this->_resource = $resource;
        parent::__construct(
            $context
        );
    }

    public function execute() {
        // Check if this request is for the event registration form
        if ($this->getRequest()->getParam('is_event_survey_registration')) {

            $connection = $this->_resource->getConnection();
            $tableName = $this->_resource->getTableName('event_survey_registration');

            $select = $connection->select()
                ->from($tableName, ['transaction_id'])
                ->order('registration_id DESC') 
                ->limit(1);

            $latestTransactionId = $connection->fetchOne($select);
            // Extract the numeric part of the latest transaction ID
            $latestNumber = (int) filter_var($latestTransactionId, FILTER_SANITIZE_NUMBER_INT);

            // Increment the numeric part to get the new transaction ID
            $newTransactionId = 'TESTSUVEY' . ($latestNumber + 1);

            $txnid = $newTransactionId;
            $firstname = $this->getRequest()->getParam('firstname');
            $lastname = $this->getRequest()->getParam('lastname');
            $user_email = $this->getRequest()->getParam('user_email');
            $ticket_qty = $this->getRequest()->getParam('ticket_qty');
            $phone = $this->getRequest()->getParam('phone');
            $country = $this->getRequest()->getParam('country');
            $sourceOfInfo = $this->getRequest()->getParam('sourceOfInfo');
            $city = $this->getRequest()->getParam('city');
            $groupCount = $this->getRequest()->getParam('groupCount');



            // Store form data into your custom table
            $connection = $this->_resource->getConnection();
            $tableName = $this->_resource->getTableName('event_survey_registration');

            $data = [
                'transaction_id' => $txnid,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'currency' => 'INR',
                'status' => 'pending', 
                'created_at' => date('Y-m-d H:i:s'),
                'user_email' => $user_email,
                'user_phone' => $phone,
                'country' => $country,
                'sourceOfInfo' => $sourceOfInfo,
                'city' => $city,
                'ticket_qty' => $ticket_qty,
                'groupCount' => $groupCount,
            ];

            $connection->insert($tableName, $data);

            // Return the JSON response with PayU payment form fields
            return;

        }

    }

}

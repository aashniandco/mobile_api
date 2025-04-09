<?php
namespace Fermion\LoyaltyPoint\Controller\Adminhtml\Loyalty;


use Magento\Framework\Exception\LocalizedException;
use Fermion\LoyaltyPoint\Model\Loyalty;

class Save extends \Fermion\LoyaltyPoint\Controller\Adminhtml\Loyalty{
   

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
 // print_r($data);die;
        if ($data) {
            try {
                // Get the customer ID directly from the form data
                $customerId = $data['customer_id'];
               
                // Create a Loyalty model instance and save it to the custom table
                $loyaltyModel = $this->_objectManager->create(Loyalty::class);
                // print_r($loyaltyModel);
                $loyaltyModel->setCustomerId($customerId);
                $loyaltyModel->setCustomerEmail($data['customer_email']);
                $loyaltyModel->setLoyaltyPoints($data['loyalty_points']);
                $loyaltyModel->setBalanceLoyaltyPoints($data['balance_loyaltypoints']);
                 // print_r($loyaltyModel);die;
                $loyaltyModel->save();


                // Add a success message and redirect to the index grid page
                $this->messageManager->addSuccess(__('Customer data has been saved.'));
                $this->_redirect('*/*/index');
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }
        }

        // Redirect back to the form page if there was an issue
        $this->_redirect('*/*/edit', ['id' => $customerId]);
    }
}

<?php
namespace Fermion\LoyaltyPoint\Controller\Adminhtml\Loyalty;

class Edit extends \Fermion\LoyaltyPoint\Controller\Adminhtml\Loyalty {

    public function execute() {  
     
        $id = $this->getRequest()->getParam('id');        
        $model = $this->_objectManager->create('Fermion\LoyaltyPoint\Model\Loyalty');        
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('fermion/*');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_loyaltypoint', $model);        
        $resultPage = $this->resultPageFactory->create();
        if (!empty($model->getId())) {
            $title = "Edit " . $model->getData("title");
        } else {
            $title = "Add LoyaltyPoint";
        }
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }
}

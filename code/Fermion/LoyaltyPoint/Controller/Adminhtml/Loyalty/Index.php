<?php
namespace Fermion\LoyaltyPoint\Controller\Adminhtml\Loyalty;

class Index extends \Fermion\LoyaltyPoint\Controller\Adminhtml\Loyalty {
    public function execute() {
        // die("index");
         /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Fermion_LoyaltyPoint::Fermion');
        $resultPage->getConfig()->getTitle()->prepend(__('Loyalty Point'));
        return $resultPage;
    }
}
?>


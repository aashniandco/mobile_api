<?php
/**
 * Copyright © 2015 Fermion. All rights reserved.
 */

namespace Fermion\Pagelayout\Controller\Adminhtml\Report;

class Index extends \Magento\Framework\App\Action\Action   {

//     /**
//      * Items list.
//      *
//      * @return \Magento\Backend\Model\View\Result\Page
//      */
     public function __construct(
        \Magento\Backend\App\Action\Context $context,        
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
        
    ) { 
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;        
        $this->resultPageFactory = $resultPageFactory;               
       
    }
    protected function _initAction() {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Fermion_Pagelayout::report')->_addBreadcrumb(__('Report'), __('Report'));
        return $this;
    }
    /**
     * Initiate action
     *
     * @return this
     */
    public function execute() {                 
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Fermion_Pagelayout::report');
        $resultPage->getConfig()->getTitle()->prepend(__('Report'));
        return $resultPage;
    }

  
}

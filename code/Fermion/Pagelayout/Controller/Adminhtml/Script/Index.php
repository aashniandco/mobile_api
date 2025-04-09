<?php
/**
 * Copyright © 2015 Fermion. All rights reserved.
 */

namespace Fermion\Pagelayout\Controller\Adminhtml\Script;

class Index extends \Magento\Backend\App\Action   {
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
     public function __construct(
        \Magento\Backend\App\Action\Context $context,        
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
        //\Fermion\Admin\Helper\ScriptHelper $scriptHelper
    ) { 
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;        
        $this->resultPageFactory = $resultPageFactory;               
       // $this->script_helper = $scriptHelper;
    }
    protected function _initAction() {
        $this->_view->loadLayout();
         //$this->_setActiveMenu('Fermion_Pagelayout::script')->_addBreadcrumb(__('Script'), __('Script'));
        return $this;
    }
    /**
     * Initiate action
     *
     * @return this
     */
    public function execute() {                 
        $resultPage = $this->resultPageFactory->create();
       // $resultPage->setActiveMenu('Fermion_Pagelayout::script');
        //$resultPage->getConfig()->getTitle()->prepend(__('Script'));
        return $resultPage;
    }

  
}

<?php

namespace Eleadtech\AlphabetOption\Controller\Adminhtml\Aboption;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class UpdateAttribute extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPagee;
    protected $helper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Eleadtech\AlphabetOption\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $model = $this->_objectManager->create('Eleadtech\AlphabetOption\Model\Aboption');
        $resultRedirect = $this->resultRedirectFactory->create();
        if(!$this->helper->isEnabled()){
            $this->messageManager->addErrorMessage(__('Please enable this module at first'));
            return $resultRedirect->setPath('*/*/');
        }
        if($model->loadAttributes()){
            $this->messageManager->addSuccessMessage(__('Attributes were updated'));
        }
        else{
            $this->messageManager->addErrorMessage(__('Attributes could not update'));
        }
        /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */


        return $resultRedirect->setPath('*/*/');
    }
}
?>

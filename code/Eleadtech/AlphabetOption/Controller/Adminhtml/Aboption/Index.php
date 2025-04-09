<?php

namespace Eleadtech\AlphabetOption\Controller\Adminhtml\Aboption;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPagee;
    protected $helper;
    protected $notifierPool;

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
        $resultPage->setActiveMenu('Eleadtech_AlphabetOption::aboption');
        $resultPage->addBreadcrumb(__('Eleadtech'), __('Eleadtech'));
        $resultPage->addBreadcrumb(__('Manage item'), __('Manage Alphabet option'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Alphabet'));
        if(!$this->helper->isEnabled()){
            $this->messageManager->addError(__('Please enable this module at first'));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('adminhtml/system_config/edit/section/alphabetoption');
        }
        return $resultPage;
    }
}
?>

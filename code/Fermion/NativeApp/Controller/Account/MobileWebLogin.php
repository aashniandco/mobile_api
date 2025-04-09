<?php 

namespace Fermion\NativeApp\Controller\Account;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Customer\Controller\AbstractAccount;

class MobileWebLogin extends Action
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ){
        $this->pageFactory = $pageFactory;
        $this->session = $customerSession;
        return parent::__construct($context);
    }

    public function execute()
    {
        if ($this->session->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/');
            return $resultRedirect;
        }
        $resultPage = $this->pageFactory->create();
        return $resultPage;
    }
}
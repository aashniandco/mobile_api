<?php 

namespace Fermion\Pagelayout\Controller\Account;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
class Delete extends \Magento\Customer\Controller\AbstractAccount implements HttpGetActionInterface
{
    private $pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ){
        $this->pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        return $resultPage;
    }
}
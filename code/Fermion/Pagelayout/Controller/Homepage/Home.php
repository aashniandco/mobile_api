<?php
namespace Fermion\Pagelayout\Controller\Homepage;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

class Home extends Action
{
    protected $_pageFactory;
    protected $_storeManager;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $currentStore = $this->_storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $resultPage = $this->_pageFactory->create();

        $headerHtml = $resultPage->getLayout()
            ->createBlock('Magento\Framework\View\Element\Template')
            ->setTemplate('Fermion_Pagelayout::homepage/header.phtml')
            ->setCacheable(false)
            ->toHtml();

        $contentHtml = $resultPage->getLayout()
            ->createBlock('Magento\Framework\View\Element\Template')
            ->setMediaUrl($mediaUrl)
            ->setTemplate('Fermion_Pagelayout::homepage/homepage.phtml')
            ->toHtml();

        $footerHtml = $resultPage->getLayout()
            ->createBlock('Magento\Framework\View\Element\Template')
            ->setTemplate('Fermion_Pagelayout::homepage/footer.phtml')
            ->toHtml();

        $this->getResponse()
            ->setHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 3600), true) // 1 hour
            ->setBody($headerHtml . $contentHtml . $footerHtml);
    }
}

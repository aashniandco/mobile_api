<?php
namespace Fermion\Pagelayout\Block;
class Home extends \Magento\Framework\View\Element\Template
{
    protected $_catdata = null;
    protected $_solrFilterData = null;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory      
        
    ) {
        $this->_pageFactory = $pageFactory;          
        
        return parent::__construct($context);
    }

    public function getBaseUrl()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        return $baseUrl;
    }

    /* get media url */
    public function getMediaUrl() {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

    }
}
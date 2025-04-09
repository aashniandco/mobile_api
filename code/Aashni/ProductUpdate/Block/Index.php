<?php
namespace Aashni\ProductUpdate\Block ;
use \Magento\Framework\View\Element\Template ;
use \Magento\Framework\View\Element\Template\Context ;

class Index extends Template
{
    /**
     * Constructor
     *
     * @param Context $context
     * @param Data $helper
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Get Base Url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->helper->getBaseUrl();
    }

    /**
     * Get Current Url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->helper->getCurrentUrl();
    }

  
}

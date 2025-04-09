<?php

namespace Eleadtech\Bcore\Block;
use Magento\Framework\View\Element\Template\Context;

class Bcore  extends  \Magento\Framework\View\Element\Template
{
    protected $context;
    
    public function __construct(
        Context $context
        
    ) {
        parent::__construct($context);
        $this->context = $context;
    }
    
    public function getFullAction(){
        return $this->context->getRequest()->getFullActionName();
    }
    
    public function getUrl($path = "", $params = []){
        if(empty($params)){
            $isSecure = false;
            if ($this->context->getStoreManager()->getStore()->isCurrentlySecure()) {
                $isSecure = true;
            }
            $params['_secure'] = $isSecure;
        }
        return parent::getUrl($path, $params);
    }
}


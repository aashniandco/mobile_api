<?php

namespace MGS\StoreLocator\Controller\Index;

use MGS\StoreLocator\Model\StoreFactory;
use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action {

    protected $_storeFactory;
    
    public function __construct(Context $context, StoreFactory $storeFactory) {
        parent::__construct($context);
        $this->_storeFactory = $storeFactory;
    }

    public function execute() {

        $url = 'https://aashniandco.com/no-route';
        return $this->_redirect->redirect($this->_response, $url);

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Store Locator'));
        $this->_view->renderLayout();
    }

}

<?php

namespace Mec\SuggestedProducts\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
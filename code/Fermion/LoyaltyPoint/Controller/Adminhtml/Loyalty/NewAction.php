<?php
namespace Fermion\LoyaltyPoint\Controller\Adminhtml\Loyalty;

class NewAction extends \Fermion\LoyaltyPoint\Controller\Adminhtml\Loyalty {

    public function execute() {    	
        $this->_forward('edit');
    }
}

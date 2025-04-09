<?php
namespace Fermion\LoyaltyPoint\Block\Adminhtml;

class Loyalty extends \Magento\Backend\Block\Widget\Grid\Container {
    protected function _construct() {
        $this->_controller = 'Loyalty';    
        //$this->_addButtonLabel = __('Add New Loyalty');
        
        parent::_construct();
        $this->buttonList->remove('add');
    }
}

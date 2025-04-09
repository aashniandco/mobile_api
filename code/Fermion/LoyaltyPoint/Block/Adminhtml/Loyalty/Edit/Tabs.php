<?php
namespace Fermion\LoyaltyPoint\Block\Adminhtml\Loyalty\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs {
    
    protected function _construct() {
        parent::_construct();
        $this->setId('loyaltypoint_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('LoyaltyPoint'));
    }
}

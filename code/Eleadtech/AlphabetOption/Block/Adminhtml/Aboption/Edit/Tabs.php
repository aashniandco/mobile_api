<?php
namespace Eleadtech\AlphabetOption\Block\Adminhtml\Aboption\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('aboption_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Alphabet Option Information'));
    }
}

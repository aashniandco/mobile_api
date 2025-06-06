<?php
namespace Mec\Enquire\Block\Adminhtml\Enquire\Edit;

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
        $this->setId('enquire_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Enquire Information'));
    }
}
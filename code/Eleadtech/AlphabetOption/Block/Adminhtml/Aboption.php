<?php

namespace Eleadtech\AlphabetOption\Block\Adminhtml;

class Aboption extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'aboption/aboption.phtml';

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Widget\Context $context,array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'reload',
            'label' => __('Update Attributes'),
            'class' => 'primary',
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')",
        ];

        $this->buttonList->add('reload', $addButtonProps);


        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Eleadtech\AlphabetOption\Block\Adminhtml\Aboption\Grid', 'eleadtech.aboption.grid')
        );

        return parent::_prepareLayout();
    }

    /**
     *
     *
     * @return array
     */
    protected function _getAddButtonOptions()
    {

        $splitButtonOptions[] = [
            'label' => __('Add New'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];

        return $splitButtonOptions;
    }

    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            'alphabetoption/*/updateAttribute'
        );
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

}

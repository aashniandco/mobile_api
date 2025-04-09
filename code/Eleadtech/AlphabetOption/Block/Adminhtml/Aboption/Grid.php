<?php
namespace Eleadtech\AlphabetOption\Block\Adminhtml\Aboption;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Eleadtech\AlphabetOption\Model\aboptionFactory
     */
    protected $_aboptionFactory;

    /**
     * @var \Eleadtech\AlphabetOption\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Eleadtech\AlphabetOption\Model\aboptionFactory $aboptionFactory
     * @param \Eleadtech\AlphabetOption\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Eleadtech\AlphabetOption\Model\AboptionFactory $AboptionFactory,
        \Eleadtech\AlphabetOption\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_aboptionFactory = $AboptionFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('attribute_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_aboptionFactory->create()->getCollection();
        $collection->addAttributeInfo();
        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'attribute_id',
            [
                'header' => __('Attribute ID'),
                'type' => 'number',
                'index' => 'attribute_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'attribute_code',
            [
                'header' => __('Attribute Code'),
                'index' => 'attribute_code'
            ]
        );

        $this->addColumn(
            'frontend_label',
            [
                'header' => __('Frontend Label'),
                'index' => 'frontend_label'
            ]
        );

        $this->addColumn(
            'sort',
            [
                'header' => __('Sort'),
                'index' => 'sort',
                'type' => 'options',
                'options' => \Eleadtech\AlphabetOption\Model\SortType::getOptionArray()
            ]
        );

        //$this->addColumn(
            //'edit',
            //[
                //'header' => __('Edit'),
                //'type' => 'action',
                //'getter' => 'getId',
                //'actions' => [
                    //[
                        //'caption' => __('Edit'),
                        //'url' => [
                            //'base' => '*/*/edit'
                        //],
                        //'field' => 'attribute_id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }


    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('attribute_id');
        //$this->getMassactionBlock()->setTemplate('Eleadtech_AlphabetOption::aboption/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('aboption');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('alphabetoption/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        return $this;
    }


    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('alphabetoption/*/index', ['_current' => true]);
    }

    /**
     * @param \Eleadtech\AlphabetOption\Model\aboption|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {

        return $this->getUrl(
            'alphabetoption/*/edit',
            ['attribute_id' => $row->getId()]
        );

    }



}

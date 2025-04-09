<?php
namespace Mec\Enquire\Block\Adminhtml\Enquire;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Mec\Enquire\Model\enquireFactory
     */
    protected $_enquireFactory;

    /**
     * @var \Mec\Enquire\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Mec\Enquire\Model\enquireFactory $enquireFactory
     * @param \Mec\Enquire\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Mec\Enquire\Model\EnquireFactory $EnquireFactory,
        \Mec\Enquire\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_enquireFactory = $EnquireFactory;
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
        $this->setDefaultSort('enquiry_id');
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
        $collection = $this->_enquireFactory->create()->getCollection();
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
            'enquiry_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'enquiry_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );


		
				$this->addColumn(
					'designer_name',
					[
						'header' => __('Designer Name'),
						'index' => 'designer_name',
					]
				);
				
				$this->addColumn(
					'sku',
					[
						'header' => __('Product Sku'),
						'index' => 'sku',
					]
				);
				
				$this->addColumn(
					'customer_name',
					[
						'header' => __('Customer Name'),
						'index' => 'customer_name',
					]
				);
				
				$this->addColumn(
					'customer_email',
					[
						'header' => __('Customer Email'),
						'index' => 'customer_email',
					]
				);

                $this->addColumn(
                    'country',
                    [
                        'header' => __('Country'),
                        'index' => 'country',
                    ]
                );

                $this->addColumn(
                    'country_code',
                    [
                        'header' => __('Country Code'),
                        'index' => 'country_code',
                    ]
                );

				
				$this->addColumn(
					'customer_phone',
					[
						'header' => __('Phone'),
						'index' => 'customer_phone',
					]
				);
				
				$this->addColumn(
					'query',
					[
						'header' => __('Query'),
						'index' => 'query',
					]
				);
				
				$this->addColumn(
					'remote_ip',
					[
						'header' => __('Ip'),
						'index' => 'remote_ip',
					]
				);
				
				$this->addColumn(
					'product_desc',
					[
						'header' => __('Product Short Description'),
						'index' => 'product_desc',
					]
				);
                $this->addColumn(
                    'created_at',
                    [
                        'header' => __('Created At'),
                        'index' => 'created_at',
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
                        //'field' => 'enquiry_id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);
		

		
		   $this->addExportType($this->getUrl('enquire/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('enquire/*/exportExcel', ['_current' => true]),__('Excel XML'));

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

        $this->setMassactionIdField('enquiry_id');
        //$this->getMassactionBlock()->setTemplate('Mec_Enquire::enquire/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('enquire');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('enquire/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('enquire/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );


        return $this;
    }
		

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('enquire/*/index', ['_current' => true]);
    }

    /**
     * @param \Mec\Enquire\Model\enquire|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'enquire/*/edit',
            ['enquiry_id' => $row->getId()]
        );
		
    }

	

}
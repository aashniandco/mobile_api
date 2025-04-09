<?php

namespace Mec\Enquire\Block\Adminhtml\Enquire\Edit\Tab;

/**
 * Enquire edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Mec\Enquire\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Mec\Enquire\Model\Status $status,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Mec\Enquire\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('enquire');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('enquiry_id', 'hidden', ['name' => 'enquiry_id']);
        }

		
        $fieldset->addField(
            'designer_name',
            'text',
            [
                'name' => 'designer_name',
                'label' => __('Designer Name'),
                'title' => __('Designer Name'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'sku',
            'text',
            [
                'name' => 'sku',
                'label' => __('Product Sku'),
                'title' => __('Product Sku'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'customer_name',
            'text',
            [
                'name' => 'customer_name',
                'label' => __('Customer Name'),
                'title' => __('Customer Name'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'customer_email',
            'text',
            [
                'name' => 'customer_email',
                'label' => __('Customer Email'),
                'title' => __('Customer Email'),
				
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'country',
            'text',
            [
                'name' => 'country',
                'label' => __('Country'),
                'title' => __('Country'),
                
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'country_code',
            'text',
            [
                'name' => 'country_code',
                'label' => __('Country_code'),
                'title' => __('Country_code'),
                
                'disabled' => $isElementDisabled
            ]
        );
        					
        $fieldset->addField(
            'customer_phone',
            'text',
            [
                'name' => 'customer_phone',
                'label' => __('Phone'),
                'title' => __('Phone'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'query',
            'text',
            [
                'name' => 'query',
                'label' => __('Query'),
                'title' => __('Query'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'remote_ip',
            'text',
            [
                'name' => 'remote_ip',
                'label' => __('Ip'),
                'title' => __('Ip'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'product_desc',
            'text',
            [
                'name' => 'product_desc',
                'label' => __('Product Short Description'),
                'title' => __('Product Short Description'),
				
                'disabled' => $isElementDisabled
            ]
        );
					

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Item Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Item Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    public function getTargetOptionArray(){
    	return array(
    				'_self' => "Self",
					'_blank' => "New Page",
    				);
    }
}

<?php

namespace Fermion\LoyaltyPoint\Block\Adminhtml\Loyalty\Edit\Tab;


use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface { 


 
    public function __construct(
        \Magento\Backend\Block\Template\Context $context, 
        \Magento\Framework\Registry $registry, 
        \Magento\Framework\Data\FormFactory $formFactory,          
        array $data = []
    ) {      
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('LoyaltyPoint');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('LoyaltyPoint');
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

    protected function _prepareForm()
    {
        
        $model = $this->_coreRegistry->registry('current_loyaltypoint');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('item_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('LoyaltyPoint')]);                
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
         $fieldset->addField(
            'order_id',
            'text',
            ['name' => 'order_id', 'label' => __('Order Id'), 'title' => __('Order Id'), 'required' => false]
        );
        $fieldset->addField(
            'customer_id',
            'text',
            ['name' => 'customer_id', 'label' => __('Customer Id'), 'title' => __('Customer Id'), 'required' => false]
        );
        $fieldset->addField(
            'customer_email',
            'text',
            ['name' => 'customer_email', 'label' => __('Customer Email'), 'title' => __('Customer Email'), 'required' => false]
        );
         $fieldset->addField(
            'loyalty_points',
            'text',
            [
                'name' => 'loyalty_points',
                'label' => __('Loyalty Points'),
                'title' => __('Loyalty Points'),
                'required'  => false
            ]
        );
        $fieldset->addField(
            'balance_loyaltypoints',
            'text',
            ['name' => 'balance_loyaltypoints', 'label' => __('Balance Loyalty points'), 'title' => __('Balance Loyalty points'), 'required' => false]
        );                        
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}

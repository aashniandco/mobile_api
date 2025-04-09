<?php

namespace Eleadtech\AlphabetOption\Block\Adminhtml\Aboption\Edit\Tab;

/**
 * Aboption edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Eleadtech\AlphabetOption\Model\Status
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
        \Eleadtech\AlphabetOption\Model\Status $status,
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
        /* @var $model \Eleadtech\AlphabetOption\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('aboption');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $connection = $model->getResource()->getConnection();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $eavModel = $objectManager->create("Magento\Catalog\Model\ResourceModel\Eav\Attribute");
            $attr = $eavModel->load($model->getId());
            $model->setAttributeLabel($attr->getFrontendLabel() . " (" . $attr->getAttributeCode() . ")");
            $fieldset->addField('attribute_id', 'hidden', ['name' => 'attribute_id']);
        }

        $fieldset->addField(
            'attribute_label',
            'label',
            [
                'name' => 'attribute_label',
                'label' => __('Attribute Label'),
                'title' => __('Attribute Label'),

                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'sort',
            'select',
            [
                'name' => 'sort',
                'label' => __('Sort'),
                'title' => __('Sort'),
                'options' => \Eleadtech\AlphabetOption\Model\SortType::getOptionArray(),
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

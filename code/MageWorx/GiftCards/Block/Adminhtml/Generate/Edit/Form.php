<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block\Adminhtml\Generate\Edit;

use MageWorx\GiftCards\Api\Data\GiftCardsInterface;
use MageWorx\GiftCards\Model\GiftCards\Source\CurrenciesOptionProvider;
use MageWorx\GiftCards\Block\Adminhtml\Form\Field\StoreIdForEmail as StoreIdForEmailField;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \MageWorx\GiftCards\Model\GiftCards\Source\TypesOptionProvider
     */
    protected $cardType;

    /**
     * @var \MageWorx\GiftCards\Model\GiftCards\Source\StatusesOptionProvider
     */
    protected $cardStatus;

    /**
     * @var \MageWorx\GiftCards\Model\GiftCards\Source\CustomerGroups
     */
    protected $customerGroups;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var CurrenciesOptionProvider
     */
    protected $cardCurrency;

    /**
     * @var StoreIdForEmailField
     */
    protected $storeIdForEmailFiled;

    /**
     * Form constructor.
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \MageWorx\GiftCards\Model\GiftCards\Source\TypesOptionProvider $cardType
     * @param \MageWorx\GiftCards\Model\GiftCards\Source\StatusesOptionProvider $cardStatus
     * @param \MageWorx\GiftCards\Model\GiftCards\Source\CustomerGroups $customerGroups
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param CurrenciesOptionProvider $cardCurrency
     * @param StoreIdForEmailField $storeIdForEmailFiled
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \MageWorx\GiftCards\Model\GiftCards\Source\TypesOptionProvider $cardType,
        \MageWorx\GiftCards\Model\GiftCards\Source\StatusesOptionProvider $cardStatus,
        \MageWorx\GiftCards\Model\GiftCards\Source\CustomerGroups $customerGroups,
        \Magento\Store\Model\System\Store $systemStore,
        CurrenciesOptionProvider $cardCurrency,
        StoreIdForEmailField $storeIdForEmailFiled,
        array $data = []
    ) {
        $this->cardType             = $cardType;
        $this->cardStatus           = $cardStatus;
        $this->customerGroups       = $customerGroups;
        $this->systemStore          = $systemStore;
        $this->cardCurrency         = $cardCurrency;
        $this->storeIdForEmailFiled = $storeIdForEmailFiled;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setId('generate_form');
        $this->setTitle(__('Gift Card Information'));
    }

    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'     => 'edit_form',
                    'method' => 'post',
                    'action' => $this->getData('action'),
                ]
            ]
        );

        $form->setHtmlIdPrefix('giftcard_generate_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Generate Gift Cards')]);

        $fieldset->addField(
            'giftcards_count',
            'text',
            [
                'name'     => 'giftcards_count',
                'label'    => __('Giftcards Count'),
                'title'    => __('Giftcards Count'),
                'required' => true,
                'class'    => 'validate-not-negative-number'
            ]
        );

        $fieldset->addField(
            'giftcards_amount',
            'text',
            [
                'name'     => 'giftcards_amount',
                'label'    => __('Giftcards Amount'),
                'title'    => __('Giftcards Amount'),
                'required' => true,
                'class'    => 'validate-not-negative-number',
                'required' => false,
            ]
        );

        $fieldset->addField(
            'card_currency',
            'select',
            [
                'name'     => 'card_currency',
                'label'    => __('Gift Card Currency'),
                'title'    => __('Gift Card Currency'),
                'required' => true,
                'options'  => $this->cardCurrency->getAllOptions(),
            ]
        );

        $field    = $fieldset->addField(
            'store_id',
            'multiselect',
            [
                'label'    => __('Store Views'),
                'title'    => __('Store Views'),
                'name'     => 'store_id',
                'required' => true,
                'values'   => $this->systemStore->getStoreValuesForForm(false, true),
                'note'     => 'Please select store view(s) you want to allow using the gift card code on.'
            ]
        );
        $renderer = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
        );
        $field->setRenderer($renderer);

        $fieldset->addField(
            GiftCardsInterface::STORE_ID_FOR_EMAIL,
            'select',
            [
                'name'               => GiftCardsInterface::STORE_ID_FOR_EMAIL,
                'label'              => __('Send Email From'),
                'title'              => __('Send Email From'),
                'after_element_html' => $this->storeIdForEmailFiled->getAfterElementHtml('giftcard_generate_'),
                'note'               => __('Choose a store view, which will be used to send a gift card email.')
            ]
        );

        $fieldset->addField(
            'giftcards_type',
            'select',
            [
                'label'    => __('Gift Card Type'),
                'title'    => __('Gift Card Type'),
                'name'     => 'giftcards_type',
                'options'  => $this->cardType->getAllOptions(),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'expire_date',
            'date',
            [
                'name'         => 'expire_date',
                'label'        => __('Expiration Date'),
                'title'        => __('Expiration Date'),
                'format'       => 'yyyy-MM-dd',
                'date_format'  => 'yyyy-MM-dd',
                'input_format' => 'yyyy-MM-dd',
                'required'     => false,
            ]
        );

        $fieldset->addField(
            'customer_group_id',
            'multiselect',
            [
                'label'    => __('Available for Customer Groups'),
                'title'    => __('Available for Customer Groups'),
                'name'     => 'customer_group_id',
                'required' => false,
                'values'   => $this->customerGroups->getAllOptions()
            ]
        );

        $fieldset->addField(
            'giftcards_status',
            'select',
            [
                'name'    => 'giftcards_status',
                'label'   => __('Giftcards Status'),
                'title'   => __('Giftcards Status'),
                'options' => $this->cardStatus->getAllOptions(),
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
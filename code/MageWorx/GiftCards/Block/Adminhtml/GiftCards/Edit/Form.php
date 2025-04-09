<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block\Adminhtml\GiftCards\Edit;

use MageWorx\GiftCards\Block\Adminhtml\Form\Field\StoreIdForEmail as StoreIdForEmailField;
use MageWorx\GiftCards\Model\GiftCards\Source\CurrenciesOptionProvider;
use MageWorx\GiftCards\Model\GiftCards\Source\TypesOptionProvider;
use MageWorx\GiftCards\Model\GiftCards\Source\StatusesOptionProvider;
use MageWorx\GiftCards\Model\GiftCards\Source\CustomerGroups;
use MageWorx\GiftCards\Api\Data\GiftCardsInterface;
use Magento\Framework\Stdlib\DateTime;

/**
 * Adminhtml giftcard edit form block
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var array
     */
    protected $formValues = [];

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var TypesOptionProvider
     */
    protected $cardType;

    /**
     * @var StatusesOptionProvider
     */
    protected $cardStatus;

    /**
     * @var CurrenciesOptionProvider
     */
    protected $cardCurrency;

    /**
     * @var CustomerGroups
     */
    protected $customerGroups;

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
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param TypesOptionProvider $cardType
     * @param StatusesOptionProvider $cardStatus
     * @param CurrenciesOptionProvider $cardCurrency
     * @param CustomerGroups $customerGroups
     * @param StoreIdForEmailField $storeIdForEmailFiled
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        TypesOptionProvider $cardType,
        StatusesOptionProvider $cardStatus,
        CurrenciesOptionProvider $cardCurrency,
        CustomerGroups $customerGroups,
        StoreIdForEmailField $storeIdForEmailFiled,
        array $data = []
    ) {
        $this->systemStore          = $systemStore;
        $this->cardType             = $cardType;
        $this->cardStatus           = $cardStatus;
        $this->cardCurrency         = $cardCurrency;
        $this->customerGroups       = $customerGroups;
        $this->storeIdForEmailFiled = $storeIdForEmailFiled;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('giftcard_form');
        $this->setTitle(__('Gift Card Information'));
    }

    /**
     * @return $this|\Magento\Backend\Block\Widget\Form\Generic
     */
    protected function _initFormValues()
    {
        $model = $this->_getModel();

        if ($model && $model->getId()) {
            $this->_formValues = [
                GiftCardsInterface::CARD_ID            => $model->getId(),
                GiftCardsInterface::CARD_CODE          => $model->getCardCode(),
                GiftCardsInterface::CARD_AMOUNT        => $model->getCardAmount(),
                GiftCardsInterface::CARD_BALANCE       => $model->getCardBalance(),
                GiftCardsInterface::STORE_ID           => $model->getStoreId(),
                GiftCardsInterface::STORE_ID_FOR_EMAIL => $model->getStoreIdForEmailSending(),
                GiftCardsInterface::CARD_CURRENCY      => $model->getCardCurrency(),
                GiftCardsInterface::CARD_TYPE          => $model->getCardType(),
                GiftCardsInterface::CARD_STATUS        => $model->getCardStatus(),
                GiftCardsInterface::MAIL_TO            => $model->getMailTo(),
                GiftCardsInterface::MAIL_TO_EMAIL      => $model->getMailToEmail(),
                GiftCardsInterface::IMAGE_URL          => $model->getImageUrl(),
                GiftCardsInterface::MAIL_FROM          => $model->getMailFrom(),
                GiftCardsInterface::MAIL_MESSAGE       => $model->getMailMessage(),
                GiftCardsInterface::CUSTOMER_GROUP_ID  => $model->getCustomerGroupId(),
                GiftCardsInterface::EXPIRE_DATE        => $model->getExpireDate(),
                GiftCardsInterface::MAIL_DELIVERY_DATE => $model->getMailDeliveryDate()
            ];
        } else {
            $this->_formValues = [
                GiftCardsInterface::CARD_ID            => '',
                GiftCardsInterface::CARD_CODE          => '',
                GiftCardsInterface::CARD_AMOUNT        => '',
                GiftCardsInterface::CARD_BALANCE       => '',
                GiftCardsInterface::STORE_ID           => [0],
                GiftCardsInterface::STORE_ID_FOR_EMAIL => '',
                GiftCardsInterface::CARD_CURRENCY      => '',
                GiftCardsInterface::CARD_TYPE          => '',
                GiftCardsInterface::CARD_STATUS        => '',
                GiftCardsInterface::MAIL_TO            => '',
                GiftCardsInterface::MAIL_TO_EMAIL      => '',
                GiftCardsInterface::IMAGE_URL          => '',
                GiftCardsInterface::MAIL_FROM          => '',
                GiftCardsInterface::MAIL_MESSAGE       => '',
                GiftCardsInterface::CUSTOMER_GROUP_ID  => '',
                GiftCardsInterface::EXPIRE_DATE        => '',
                GiftCardsInterface::MAIL_DELIVERY_DATE => $model->getMailDeliveryDate()
            ];
        }

        return $this;
    }

    /**
     * Prepare the form.
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareForm()
    {
        error_log("===giftcard expiredate3===");
        $this->_initFormValues();

        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'     => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                ],
            ]
        );

        $form->setHtmlIdPrefix('giftcard_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Gift Card Info')]);

        $fieldset->addField(
            'card_id',
            'hidden',
            [
                'name'  => 'card_id',
                'value' => $this->_formValues['card_id']
            ]
        );

        $fieldset->addField(
            'giftcard_id',
            'hidden',
            [
                'name'  => 'giftcard_id',
                'value' => $this->_formValues['card_id']
            ]
        );

        $fieldset->addField(
            'card_code',
            'text',
            [
                'label'    => __('Gift Card Code'),
                'title'    => __('Gift Card Code'),
                'name'     => 'card_code',
                'required' => false,
                'disabled' => true,
                'value'    => $this->_formValues['card_code']
            ]
        );

        $fieldset->addField(
            'card_type',
            'select',
            [
                'label'   => __('Gift Card Type'),
                'title'   => __('Gift Card Type'),
                'name'    => 'card_type',
                'options' => $this->cardType->getAllOptions(),
                'value'   => $this->_formValues['card_type'],
            ]
        );

        $fieldset->addField(
            'card_amount',
            'text',
            [
                'label'    => __('Initial value'),
                'title'    => __('Initial value'),
                'name'     => 'card_amount',
                'required' => true,
                'disabled' => false,
                'value'    => $this->_formValues['card_amount']
            ]
        );

        $fieldset->addField(
            'card_balance',
            'text',
            [
                'label'    => __('Current Balance'),
                'title'    => __('Current Balance'),
                'name'     => 'card_balance',
                'required' => false,
                'disabled' => false,
                'value'    => $this->_formValues['card_balance']
            ]
        );

        $fieldset->addField(
            'card_currency',
            'select',
            [
                'label'   => __('Gift Card Currency'),
                'title'   => __('Gift Card Currency'),
                'name'    => 'card_currency',
                'options' => $this->cardCurrency->getAllOptions(),
                'value'   => $this->_formValues['card_currency'],
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
                'value'    => $this->_formValues['store_id'],
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
                'after_element_html' => $this->storeIdForEmailFiled->getAfterElementHtml(
                    'giftcard_',
                    (int)$this->_formValues[GiftCardsInterface::STORE_ID_FOR_EMAIL]
                ),
                'note'               => __('Choose a store view, which will be used to send a gift card email.')
            ]
        );

        $fieldset->addField(
            'expire_date',
            'date',
            [
                'name'         => 'expire_date',
                'label'        => __('Expiration Date'),
                'title'        => __('Expiration Date'),
                'format'       => DateTime::DATE_INTERNAL_FORMAT,
                'date_format'  => DateTime::DATE_INTERNAL_FORMAT,
                'input_format' => DateTime::DATE_INTERNAL_FORMAT,
                'required'     => false,
                'value'        => $this->_formValues['expire_date'],
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
                'value'    => $this->_formValues['customer_group_id'],
                'values'   => $this->customerGroups->getAllOptions()
            ]
        );

        $fieldset->addField(
            'card_status',
            'select',
            [
                'label'   => __('Gift Card Status'),
                'title'   => __('Gift Card Status'),
                'name'    => 'card_status',
                'options' => $this->cardStatus->getAllOptions(),
                'value'   => $this->_formValues['card_status'],
            ]
        );

        $recFieldset = $form->addFieldset('recipient_fieldset', ['legend' => __('Recipient Info')]);

        $recFieldset->addField(
            'mail_to',
            'text',
            [
                'label' => __('To Name'),
                'title' => __('To Name'),
                'name'  => 'mail_to',
                'value' => $this->_formValues['mail_to']
            ]
        );

        $recFieldset->addField(
            'mail_to_email',
            'text',
            [
                'label' => __('To Email'),
                'title' => __('To Email'),
                'name'  => 'mail_to_email',
                'value' => $this->_formValues['mail_to_email']
            ]
        );

        $recFieldset->addField(
            'image_url',
            'text',
            [
                'label' => __('To Image Url'),
                'title' => __('To Image Url'),
                'name'  => 'image_url',
                'value' => $this->_formValues['image_url']
            ]
        );

        $recFieldset->addField(
            'mail_from',
            'text',
            [
                'label' => __('From Name'),
                'title' => __('From Name'),
                'name'  => 'mail_from',
                'value' => $this->_formValues['mail_from']
            ]
        );

        $recFieldset->addField(
            'mail_message',
            'textarea',
            [
                'label' => __('Mail Message'),
                'title' => __('Mail Message'),
                'name'  => 'mail_message',
                'cols'  => 20,
                'rows'  => 5,
                'value' => $this->_formValues['mail_message'],
                'wrap'  => 'soft'
            ]
        );

        $recFieldset->addField(
            GiftCardsInterface::MAIL_DELIVERY_DATE,
            'date',
            [
                'label'       => __('Delivery Date'),
                'title'       => __('Delivery Date'),
                'disabled'    => true,
                'name'        => GiftCardsInterface::MAIL_DELIVERY_DATE,
                'value'       => $this->_formValues[GiftCardsInterface::MAIL_DELIVERY_DATE],
                'date_format' => DateTime::DATE_INTERNAL_FORMAT,
            ]
        );

        $form->setValues($this->_formValues);
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Get Giftcard model instance
     *
     * @return \MageWorx\GiftCards\Model\GiftCards
     */
    protected function _getModel()
    {
        return $this->_coreRegistry->registry('mageworx_current_giftcard');
    }

    public function getTabLabel()
    {
        return __('Main Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Main Information');
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
}

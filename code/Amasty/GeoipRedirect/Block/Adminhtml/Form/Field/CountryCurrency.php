<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GeoipRedirect
 */


namespace Amasty\GeoipRedirect\Block\Adminhtml\Form\Field;

class CountryCurrency extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    /**
     * @var \Amasty\GeoipRedirect\Block\Adminhtml\Form\Field\Country
     */
    protected $countryRenderer = null;

    /**
     * @var \Amasty\GeoipRedirect\Block\Adminhtml\Form\Field\Currency
     */
    protected $currencyRenderer = null;

    protected function _prepareToRender()
    {
        $this->addColumn(
            'country_currency',
            [
                'label'     => __('Country'),
                'renderer'  => $this->getCountryRenderer(),
            ]
        );
        $this->addColumn('currency',
            [
                'label' => __('Currency'),
                'renderer'  => $this->getCurrencyRenderer()
            ]
        );
        $this->_addAfter = false;
    }

    /**
     * Returns renderer for country element
     *
     * @return \Amasty\GeoipRedirect\Block\Adminhtml\Form\Field\Country
     */
    protected function getCountryRenderer()
    {
        if (!$this->countryRenderer) {
            $this->countryRenderer = $this->getLayout()->createBlock(
                '\Amasty\GeoipRedirect\Block\Adminhtml\Form\Field\Country',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->countryRenderer;
    }

    /**
     * @return \Amasty\GeoipRedirect\Block\Adminhtml\Form\Field\Currency
     */
    protected function getCurrencyRenderer()
    {
        if (!$this->currencyRenderer) {
            $this->currencyRenderer = $this->getLayout()->createBlock(
                '\Amasty\GeoipRedirect\Block\Adminhtml\Form\Field\Currency',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->currencyRenderer;
    }

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $countries = $row->getCountryCurrency();
        $options = [];
        if ($countries) {
            $countries = explode(',', $countries);
            foreach ($countries as $country) {
                $options['option_' . $this->getCountryRenderer()->calcOptionHash($country)]
                    = 'selected="selected"';
            }

            $currency = $row->getCurrency();

            $options['option_' . $this->getCurrencyRenderer()->calcOptionHash($currency)]
                = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
        return;
    }
}

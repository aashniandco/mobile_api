<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GeoipRedirect
 */


namespace Amasty\GeoipRedirect\Block\Adminhtml\Form\Field;

class CountryUrl extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    /**
     * @var \Amasty\GeoipRedirect\Block\Adminhtml\Form\Field\Country
     */
    protected $countryRenderer = null;


    protected function _prepareToRender()
    {
        $this->addColumn(
            'country_url',
            [
                'label'     => __('Country'),
                'renderer'  => $this->getCountryRenderer(),
            ]
        );
        $this->addColumn(
            'url_mapping',
            [
                'label' => __('Url')
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


    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $countries = $row->getCountryUrl();
        $options = [];
        if ($countries) {
            $countries = explode(',', $countries);
            foreach ($countries as $country) {
                $options['option_' . $this->getCountryRenderer()->calcOptionHash($country)]
                    = 'selected="selected"';
            }
        }
        $row->setData('option_extra_attrs', $options);
        return;
    }
}

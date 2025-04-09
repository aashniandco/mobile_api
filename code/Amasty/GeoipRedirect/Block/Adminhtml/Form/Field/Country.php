<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GeoipRedirect
 */


namespace Amasty\GeoipRedirect\Block\Adminhtml\Form\Field;

class Country extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    public $country;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Directory\Model\Config\Source\Country $country,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->country = $country;
    }


    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        $this->setExtraParams('multiple="multiple"');
        $this->setOptions($this->country->toOptionArray(true));

        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value . '[]');
    }
}

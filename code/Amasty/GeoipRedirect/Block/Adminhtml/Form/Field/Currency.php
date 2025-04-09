<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GeoipRedirect
 */


namespace Amasty\GeoipRedirect\Block\Adminhtml\Form\Field;

class Currency extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var \Magento\CurrencySymbol\Model\System\Currencysymbol
     */
    public $currency;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\CurrencySymbol\Model\System\Currencysymbol $currency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->currency = $currency;
    }

    protected function getCurrency()
    {
        return $this->currency->getCurrencySymbolsData();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        foreach ($this->getCurrency() as $code => $currency) {
            if (isset($currency['displayName']) && $currency['displayName']) {
                $this->addOption($code, $currency['displayName']);
            }
        }
        array_unshift($this->_options, ['value' => '', 'label' => __('--Please Select--')]);

        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GeoipRedirect
 */


namespace Amasty\GeoipRedirect\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

class Editor extends Field
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var WysiwygConfig
     */
    private $wysiwygConfig;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        Context $context,
        WysiwygConfig $wysiwygConfig,
        RequestInterface $request,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->wysiwygConfig = $wysiwygConfig;
        $this->request = $request;
    }

    /**
     * Retrieve element HTML
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $params = $this->request->getParams();
        if (isset($params[ScopeInterface::SCOPE_STORE])) {
            $scope = ScopeInterface::SCOPE_STORE;
            $scopeId = $params[ScopeInterface::SCOPE_STORE];
        } elseif (isset($params[ScopeInterface::SCOPE_WEBSITE])) {
            $scope = ScopeInterface::SCOPE_WEBSITE;
            $scopeId = $params[ScopeInterface::SCOPE_WEBSITE];
        } else {
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
            $scopeId = null;
        }

        $configRedirectionDecline = $this->_scopeConfig->getValue(
            'amgeoipredirect/general/redirection_decline',
            $scope,
            $scopeId
        );
        $element->setWysiwyg(true);
        $wysiwygConfig = $this->wysiwygConfig->getConfig($element);
        if (!$configRedirectionDecline) {
            $wysiwygConfig->setData('hidden', true);
            $element->setConfig($wysiwygConfig);

            return parent::_getElementHtml($element);
        }
        $configRedirectionType = $this->_scopeConfig->getValue(
            'amgeoipredirect/general/decline_redirection_type',
            $scope,
            $scopeId
        );

        $fieldConfig = $element->getFieldConfig();

        if (isset($fieldConfig['depends']['fields']['decline_redirection_type']['value'])
            && $configRedirectionType != $fieldConfig['depends']['fields']['decline_redirection_type']['value']
        ) {
            $wysiwygConfig->setData('hidden', true);
        }

        $element->setConfig($wysiwygConfig);

        return parent::_getElementHtml($element);
    }
}

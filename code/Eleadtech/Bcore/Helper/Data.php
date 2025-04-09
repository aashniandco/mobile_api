<?php

namespace Eleadtech\Bcore\Helper;

use Eleadtech\Bcore\Logger\Logger;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\State;

class Data extends AbstractHelper
{
    protected $logger;
    protected $logFile = "var/log/nbs_core.log";
    protected $context;
    protected $_productFactory;
    protected $_scopeConfig;
    protected $state;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        $productFactory = null,
        $state = null
    ) {
        $this->context = $context;
        $this->_productFactory = $productFactory;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->state = $state;
        parent::__construct($context);
    }

    public function writeLog($message){

        if(!$this->logger){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->logger = $objectManager->create('\Eleadtech\Bcore\Logger\Logger');
        }
        $this->logger->writeLog($message, $this->logFile);
    }
    public function getContext(){
        return $this->context;
    }

    public static function getTextByCartType($cartType){
        if(strtolower($cartType) == "vi"){
            return "Visa";
        }
        if(strtolower($cartType) == "di"){
            return "Discover";
        }
        if(strtolower($cartType) == "ae"){
            return "American Express";
        }
        if(strtolower($cartType) == "mc"){
            return "Master Card";
        }
        return $cartType;
    }
    public function fputcsv(&$handle, $fields = array(), $delimiter = ',', $enclosure = '"')
    {
        $str = '';
        $escape_char = '\\';
        foreach ($fields as $value)
        {
            if (strpos($value, $delimiter) !== false ||
                    strpos($value, $enclosure) !== false ||
                    strpos($value, "\n") !== false ||
                    strpos($value, "\r") !== false ||
                    strpos($value, "\t") !== false ||
                    strpos($value, ' ') !== false)
            {
                $str2 = $enclosure;
                $escaped = 0;
                $len = strlen($value);
                for ($i = 0; $i < $len; $i++)
                {
                    if ($value[$i] == $escape_char)
                    {
                        $escaped = 1;
                    } else if (!$escaped && $value[$i] == $enclosure)
                    {
                        $str2 .= $enclosure;
                    } else
                    {
                        $escaped = 0;
                    }
                    $str2 .= $value[$i];
                }
                $str2 .= $enclosure;
                $str .= $str2 . $delimiter;
            } else
            {
                $str .= $enclosure . $value . $enclosure . $delimiter;
            }
        }
        $str = substr($str, 0, -1);
        $str .= "\n";

        //$str= utf8_encode($str);
        return fwrite($handle, $str);
    }

    public function getUrlBuilder(){
        return $this->_urlBuilder;
    }

    public function getOptionLabelFromOptionId($attributeCode, $optionId){

        if($this->_productFactory){
            $poductReource = $this->_productFactory->create();
            $attribute = $poductReource->getAttribute($attributeCode);
            if($attribute){
                if ($attribute->usesSource()) {
                    return  $option_Text = $attribute->getSource()->getOptionText($optionId);
                }
            }
        }
        return "";
    }
    public function getOptionIdByOptionLabel($attributeCode, $label){

        if($this->_productFactory){
            $poductReource = $this->_productFactory->create();
            $attribute = $poductReource->getAttribute($attributeCode);
            if($attribute){
                if ($attribute->usesSource()) {
                    return $option_Text = $attribute->getSource()->getOptionId($label);
                }
            }
        }
        return "";
    }

    public function getSalesSenderEmailInfo(){
        $name = $this->_scopeConfig->getValue(
            'trans_email/ident_sales/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $email = $this->_scopeConfig->getValue(
            'trans_email/ident_sales/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return ['name' =>$name, "email" =>$email];
    }

    public function getConfigValue($path, $storeId=0)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getFullAction()
    {
        return $this->context->getRequest()->getFullActionName();
    }
    public function isAdmin()
    {
        return $this->isArea(Area::AREA_ADMINHTML);
    }

    /**
     * @param string $area
     *
     * @return mixed
     */
    public function isArea($area = Area::AREA_FRONTEND)
    {
        if(empty($this->state)){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->state = $objectManager->get('\Magento\Framework\App\State');
        }
        if (!isset($this->isArea[$area])) {
            try {
                $this->isArea[$area] = ($this->state->getAreaCode() == $area);
            } catch (Exception $e) {
                $this->isArea[$area] = false;
            }
        }

        return $this->isArea[$area];
    }
    public function isNormalBrowser(){
        $isWebView = false;
        if((strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile/') !== false) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari/') == false)){
            $isWebView =  true;
        }
        elseif(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
            $isWebView =  true;
        }
        return $isWebView;
    }
    public function getUrl($route, $params){
        return $this->_getUrl($route, $params);
    }
    public function isFrontend(){
        return $this->isArea($area = Area::AREA_FRONTEND);
    }

}

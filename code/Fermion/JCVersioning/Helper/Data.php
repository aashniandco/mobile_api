<?php

namespace Fermion\JCVersioning\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
    /*
     * @param null
     * @return array of location details 
     * function gives array of location details like city,country,network address,isp etc
     *  
     */

    public function formattedVersionString() {
        $versionString = '';
        $enabled = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('general/jcversioning_setting/active');
        $jsversioning_version = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('general/jcversioning_setting/version_no');
        if ($enabled) {
            $versionString = '?v=' .$jsversioning_version;
            return $versionString;
        }
        return $versionString;
    }

    public function getVersionString() {
        return $this->formattedVersionString();
    }

}

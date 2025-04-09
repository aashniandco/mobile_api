<?php

namespace Eleadtech\AlphabetOption\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Eleadtech\Bcore\Helper\Data as BcoreData;


class Data extends BcoreData
{
    protected $logFile = "var/log/alphabetoption.log";
    const ENABLED = "enabled";
    const ATTRIBUTES = "attributes";

    public function writeLog($message)
    {
        parent::writeLog($message);
    }
    public function createGeneralConfigurationPath($name){
        return "alphabetoption/general/".$name;
    }
    public function isEnabled(){
        return $this->getConfigValue($this->createGeneralConfigurationPath(self::ENABLED));
    }
    public function getAttributeLoading(){
        $exceptAttributes = $this->getConfigValue($this->createGeneralConfigurationPath(self::ATTRIBUTES));
        $exceptAttributes = explode(",",$exceptAttributes);
        if(!empty($exceptAttributes)){
            return array_map("trim",$exceptAttributes);
        }
        return [];
    }
}

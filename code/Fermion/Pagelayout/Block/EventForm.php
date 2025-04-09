<?php

namespace Fermion\Pagelayout\Block;

class EventForm extends \Magento\Framework\View\Element\Template {


    public function getCountryOptions()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get("\Magento\Framework\App\ResourceConnection");
        $connection = $resource->getConnection();

        $sql = "SELECT DISTINCT a.name, a.dial_code FROM msp_tfa_country_codes a WHERE a.dial_code !='' ORDER BY CASE WHEN a.name = 'India' THEN 0 ELSE 1 END, a.name;";

        $contry  = $connection->fetchAll($sql);
        return $contry;
    }

}
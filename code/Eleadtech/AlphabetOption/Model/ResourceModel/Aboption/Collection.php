<?php

namespace Eleadtech\AlphabetOption\Model\ResourceModel\Aboption;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eleadtech\AlphabetOption\Model\Aboption', 'Eleadtech\AlphabetOption\Model\ResourceModel\Aboption');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }
    public function addAttributeInfo(){

        $this->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)->columns("*")
            ->join(["eav_attribute" => $this->getConnection()->getTableName("eav_attribute")],"eav_attribute.attribute_id = main_table.attribute_id",
                ["frontend_label" => "frontend_label","attribute_code" =>"attribute_code","frontend_input" =>"frontend_input"]);

    }

}
?>

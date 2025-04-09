<?php
namespace Eleadtech\AlphabetOption\Model\ResourceModel;

class Aboption extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    const PRODUCT_ENTITY_ID = 4;
    protected $helper = null;

    protected function _construct()
    {
        $this->_init('alphabet_option', 'attribute_id');
    }
    public function loadAttributes(){
        $allAttributes = $this->loadAllSelectAndMultiSelectAttributes();
        $nonExisting = $this->findOutNonExistingAttributes($allAttributes);
        return $this->insertNewAttribute($nonExisting);

    }
    protected function loadAllSelectAndMultiSelectAttributes(){
        $eavAttributeTable = $this->getTable("eav_attribute");
        $select = $this->getConnection()->select();
        $select->from($eavAttributeTable)->where("frontend_input IN (?)",['select','multiselect']);
        $select->where("entity_type_id = (?)",self::PRODUCT_ENTITY_ID);
        $select->where("attribute_id IN (?)", $this->getAttributeLoading());
        $select->reset(\Zend_Db_Select::COLUMNS)->columns("attribute_id");
        return $this->getConnection()->fetchCol($select);
    }
    protected function findOutNonExistingAttributes($allAttributes){
        $select = $this->getConnection()->select();
        $select->from($this->getMainTable())->columns("attribute_id");
        $currentAttributeIds = $this->getConnection()->fetchCol($select);
        return array_diff($allAttributes,$currentAttributeIds);
    }
    protected function insertNewAttribute($nonExistingAttributeIds){
        try{
            $data = [];
            if(!empty($nonExistingAttributeIds)){
                foreach ($nonExistingAttributeIds as $attributeId ){
                    $data [] = ['attribute_id' => $attributeId, 'sort' => \Eleadtech\AlphabetOption\Model\SortType::POSITION];
                }
                $this->getConnection()->insertMultiple($this->getMainTable(),$data);

            }
        }
        catch (\Exception $ex){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $objectManager->create('Eleadtech\AlphabetOption\Helper\Data')->writeLog($ex->getMessage());
            return false;
        }
        return true;
    }
    public function setHelper(\Eleadtech\AlphabetOption\Helper\Data $helper){
        $this->helper = $helper;
    }
    public function getHelper(){
        if(!$this->helper){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->helper = $objectManager->create('Eleadtech\AlphabetOption\Helper\Data');
        }
        return $this->helper;
    }
    protected function getAttributeLoading(){
        return $this->getHelper()->getAttributeLoading();
    }

    public function loadAllSelectAndMultiSelectAttributesInfo(){
        $eavAttributeTable = $this->getTable("eav_attribute");
        $select = $this->getConnection()->select();
        $select->from($eavAttributeTable)->where("frontend_input IN (?)",['select','multiselect']);
        $select->where("entity_type_id = (?)",self::PRODUCT_ENTITY_ID);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns(["attribute_id","frontend_label","attribute_code"]);
        return $this->getConnection()->fetchAll($select);
    }
    public function updatePositionByAttributeId($attributeId,$dir){
        try{
            $this->getConnection()->beginTransaction();
            $optionInfo =  $this->getOptionInforByAttributeId($attributeId,$dir);
            $tableOption = $this->getConnection()->getTableName("eav_attribute_option");
            if(!empty($optionInfo)){
                $i=0;
                foreach ($optionInfo as $item){
                    $updatedValues = ['sort_order' =>$i];
                    $where = ['option_id = ?' => $item['option_id']];
                    $this->getConnection()->update($tableOption, $updatedValues,$where);
                    $i++;
                }
            }
            $this->getConnection()->commit();
        }
        catch (\Exception $ex){
            $this->getConnection()->rollBack();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $objectManager->create('Eleadtech\AlphabetOption\Helper\Data')->writeLog($ex->getMessage());
            return false;
        }
        return true;
    }
    public function getOptionInforByAttributeId($attributeId,$dir){
        $tableOption = $this->getConnection()->getTableName("eav_attribute_option");
        $tableOptionValue = $this->getConnection()->getTableName("eav_attribute_option_value");
        $select = $this->getConnection()->select();
        $select->from($tableOption)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->joinInner($tableOptionValue,"$tableOption.option_id = $tableOptionValue.option_id and attribute_id = $attributeId",
                ["value" =>"value","option_id" =>"$tableOptionValue.option_id"])
            ->order("value $dir");
        return $this->getConnection()->fetchAll($select);
    }
}
?>

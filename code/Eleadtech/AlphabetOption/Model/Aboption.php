<?php
namespace Eleadtech\AlphabetOption\Model;

class Aboption extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eleadtech\AlphabetOption\Model\ResourceModel\Aboption');
    }
    public function loadAttributes(){
        return $this->getResource()->loadAttributes();
    }

    public function getAlphaAttributesList(){
        $alphabetList = [];
        $collection = $this->getCollection()
            ->addFieldToFilter("sort",["in" => [\Eleadtech\AlphabetOption\Model\SortType::ASC,\Eleadtech\AlphabetOption\Model\SortType::DESC]]);

        if(!empty($collection)){
            foreach ($collection as $item){
                if(!empty($item->getAttributeId())){
                    $alphabetList[$item->getAttributeId()] = $item->getSort();
                }
            }
            return $alphabetList;
        }
        return [];
    }
    public function loadAllSelectAndMultiSelectAttributesInfo(){
        return $this->getResource()->loadAllSelectAndMultiSelectAttributesInfo();
    }
    public function afterSave()
    {
        $sort = $this->getSort();
        $attributeId = $this->getAttributeId();
        if(!$sort==\Eleadtech\AlphabetOption\Model\SortType::POSITION && !empty($attributeId)){
            $dir = \Eleadtech\AlphabetOption\Model\SortType::getOptionText($sort);
            $this->updatePositionByAttributeId($attributeId,$dir);
        }
    }
    public function updatePositionByAttributeId($attributeId,$dir){
        return $this->getResource()->updatePositionByAttributeId($attributeId,$dir);
    }

}
?>

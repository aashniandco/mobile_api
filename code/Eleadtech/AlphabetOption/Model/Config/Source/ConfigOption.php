<?php
namespace Eleadtech\AlphabetOption\Model\Config\Source;

class ConfigOption implements \Magento\Framework\Option\ArrayInterface
{
    protected $alphabetOptionService;
    public function __construct(
        \Eleadtech\AlphabetOption\Service\AlphabetOption $alphabetOptionService
    ){
        $this->alphabetOptionService = $alphabetOptionService;
    }
    public function toOptionArray()
    {
        $result = $this->alphabetOptionService->loadAllSelectAndMultiSelectAttributesInfo();
        $data = [];
        if(!empty($result)){
            $i=0;
            foreach ($result as $item){
                $data[$i]["value"] = $item['attribute_id'];
                $data[$i]["label"] = $item['frontend_label'] . " (" . $item['attribute_code'] . ")";
                $i++;
            }
            return $data;
        }
        return $data;
    }
}
?>

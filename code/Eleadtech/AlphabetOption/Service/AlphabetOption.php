<?php
namespace Eleadtech\AlphabetOption\Service;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;

class AlphabetOption
{
    protected $resource;
    protected $connection;
    protected $aboptionFactory;
    protected $helper;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Eleadtech\AlphabetOption\Helper\Data $helper,
        \Eleadtech\AlphabetOption\Model\AboptionFactory $aboptionFactory
    )
    {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->aboptionFactory = $aboptionFactory;
        $this->helper = $helper;
    }

    public function getHelper(){
        return $this->helper;
    }
    public function getAlphabetFactory(){
        return $this->aboptionFactory;
    }
    public function getAlphabetOptionObject(){
        return $this->aboptionFactory->create();
    }

    public function getAlphaAttributesList(){
        return $this->getAlphabetOptionObject()->getAlphaAttributesList();
    }

    public function writeLog($message){
        $this->helper->writeLog($message);
    }
    public function loadAllSelectAndMultiSelectAttributesInfo(){
        return $this->getAlphabetOptionObject()->loadAllSelectAndMultiSelectAttributesInfo();
    }

    public function updatePositionByAttributeId($attributeId){
        $alphaObject = $this->getAlphabetOptionObject()->load($attributeId);
        $sort = $alphaObject->getSort();
        if(!$sort==\Eleadtech\AlphabetOption\Model\SortType::POSITION){
            $sortText = \Eleadtech\AlphabetOption\Model\SortType::getOptionText($sort);
            return $this->getAlphabetOptionObject()->updatePositionByAttributeId($attributeId,$sortText);
        }
        return false;
    }
    public function updatePosition(){
        $attributeInfo = $this->getAlphaAttributesList();
        if(!empty($attributeInfo)){
            foreach ($attributeInfo as $attributeId => $sort){
                $dir = \Eleadtech\AlphabetOption\Model\SortType::getOptionText($sort);
                $this->getAlphabetOptionObject()->updatePositionByAttributeId($attributeId,$dir);
            }
        }
    }
}
?>

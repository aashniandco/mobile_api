<?php
namespace Fermion\LoyaltyPoint\Model\ResourceModel\Loyalty;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
    protected $_idFieldName = 'entity_id';
    
    protected function _construct() {
        $this->_init(
            'Fermion\LoyaltyPoint\Model\Loyalty',
            'Fermion\LoyaltyPoint\Model\ResourceModel\Loyalty'
        );
    } 
}
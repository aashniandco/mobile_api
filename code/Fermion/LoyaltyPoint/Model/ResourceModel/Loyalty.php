<?php
namespace Fermion\LoyaltyPoint\Model\ResourceModel;

class Loyalty extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
    protected function _construct() {
        $this->_init('order_loyalty_points', 'entity_id');
    }
}
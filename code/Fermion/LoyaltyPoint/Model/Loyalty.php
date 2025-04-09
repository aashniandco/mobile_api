<?php
namespace Fermion\LoyaltyPoint\Model;

use Magento\Framework\Model\AbstractModel;

class Loyalty extends AbstractModel {
    protected function _construct() {
        $this->_init('Fermion\LoyaltyPoint\Model\ResourceModel\Loyalty');
    }
}
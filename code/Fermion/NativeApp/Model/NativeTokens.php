<?php
namespace Fermion\NativeApp\Model;

use Magento\Framework\Model\AbstractModel;

class NativeTokens extends AbstractModel {
    protected function _construct() {
        $this->_init('Fermion\NativeApp\Model\ResourceModel\NativeTokens');
    }
}
<?php
namespace Fermion\NativeApp\Model\ResourceModel;

class NativeTokens extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
    protected function _construct() {
        $this->_init('native_app_tokens', 'entity_id');
    }
}
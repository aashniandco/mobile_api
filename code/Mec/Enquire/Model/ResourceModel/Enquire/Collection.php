<?php

namespace Mec\Enquire\Model\ResourceModel\Enquire;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mec\Enquire\Model\Enquire', 'Mec\Enquire\Model\ResourceModel\Enquire');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>
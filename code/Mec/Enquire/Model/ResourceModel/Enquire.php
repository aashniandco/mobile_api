<?php
namespace Mec\Enquire\Model\ResourceModel;

class Enquire extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('enquiry', 'enquiry_id');
    }
}
?>
<?php
namespace Mec\Enquire\Model;

class Enquire extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mec\Enquire\Model\ResourceModel\Enquire');
    }
}
?>
<?php
namespace Aashni\Invoice\Model\ResourceModel\StripeInvoice;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(\Aashni\Invoice\Model\StripeInvoice::class, \Aashni\Invoice\Model\ResourceModel\StripeInvoice::class);
    }
}

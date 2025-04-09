<?php
namespace Aashni\Invoice\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class StripeInvoice extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('stripe_invoice', 'entity_id');
    }
}

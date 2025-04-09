<?php
namespace Aashni\Invoice\Model;

use Magento\Framework\Model\AbstractModel;

class StripeInvoice extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Aashni\Invoice\Model\ResourceModel\StripeInvoice::class);
    }
}

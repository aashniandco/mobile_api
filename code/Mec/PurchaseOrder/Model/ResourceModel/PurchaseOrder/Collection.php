<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mec\PurchaseOrder\Model\ResourceModel\PurchaseOrder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'purchase_order_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Mec\PurchaseOrder\Model\PurchaseOrder::class,
            \Mec\PurchaseOrder\Model\ResourceModel\PurchaseOrder::class
        );
    }
}


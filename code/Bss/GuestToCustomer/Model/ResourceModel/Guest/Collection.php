<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    BSS_GuestToCustomer
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GuestToCustomer\Model\ResourceModel\Guest;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * Id Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'guest_id';

    /**
     * Constructor
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Bss\GuestToCustomer\Model\Guest::class,
            \Bss\GuestToCustomer\Model\ResourceModel\Guest::class
        );
        $this->_map['fields']['guest_id'] = 'main_table.guest_id';
    }
}

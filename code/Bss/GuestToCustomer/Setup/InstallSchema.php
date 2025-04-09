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
 * @package    Bss_GuestToCustomer
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GuestToCustomer\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * Install
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        /**
         * Create table 'bss_guest_to_customer'
         */
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('bss_guest_to_customer'))
            ->addColumn(
                'guest_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Id guest'
            )
            ->addColumn(
                'email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Email Guest'
            )
            ->addColumn(
                'first_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                500,
                ['nullable' => false, 'default' => ''],
                'First Name'
            )
            ->addColumn(
                'last_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                500,
                ['nullable' => false, 'default' => ''],
                'Last Name'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                500,
                ['nullable' => false, 'default' => ''],
                'Store Id'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                500,
                ['nullable' => false, 'default' => ''],
                'Website Id'
            )
            ->addColumn(
                'shipping_address',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                ['nullable' => false, 'default' => ''],
                'Shipping Address'
            )
            ->addColumn(
                'billing_address',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                ['nullable' => false, 'default' => ''],
                'Billing Address'
            )
            ->addIndex(
                $installer->getIdxName('bss_guest_to_customer', ['guest_id']),
                ['guest_id']
            )
            ->setComment("Guest To Customer");
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}

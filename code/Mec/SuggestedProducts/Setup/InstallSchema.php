<?php
namespace Mec\SuggestedProducts\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('mec_suggested_products_recently_viewed')) {
        $table = $installer->getConnection()->newTable(
        $installer->getTable('mec_suggested_products_recently_viewed')
        )
        ->addColumn(
        'recently_viewed_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
        null,
        [
        'identity' => true,
        'nullable' => false,
        'primary'  => true,
        'unsigned' => true,
        ],
        'Recently Viewed ID'
        )
        ->addColumn(
        'visitor_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        [],
        'Visitor ID'
        )
        ->addColumn(
        'customer_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        [],
        'Customer ID'
        )
        ->addColumn(
        'product_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        null,
        [],
        'Product ID'
        )
        ->addColumn(
        'store_id',
        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
        255,
        [],
        'Store ID'
        )
        ->addColumn(
        'created_at',
        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        null,
        ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
        'Created At'
        )->addColumn(
        'updated_at',
        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        null,
        ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
        'Updated At')
        ->setComment('Mec Suggested Products Recently Viewed Table');
        $installer->getConnection()->createTable($table);

        /*$installer->getConnection()->addIndex(
        $installer->getTable('mec_suggested_products_recently_viewed'),
        $setup->getIdxName(
        $installer->getTable('mec_suggested_products_recently_viewed'),
        ['recently_viewed_id','visitor_id','customer_id','product_id','store_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
        ),
        ['recently_viewed_id','visitor_id','customer_id','product_id','store_id'],
        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
        );*/
        }
        $installer->endSetup();
    }
}
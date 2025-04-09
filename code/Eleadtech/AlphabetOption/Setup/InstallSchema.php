<?php
namespace Eleadtech\AlphabetOption\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;


class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->installTable($setup);
        $this->createForeignKey($setup);

        $setup->endSetup();
    }
    protected function installTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('alphabet_option'))
            ->addColumn(
                'attribute_id',
                Table::TYPE_SMALLINT,
                5,
                ['unsigned' => true, 'nullable' => false],
                'Attribute Id'
            )
            ->addColumn(
                'sort',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Sort'
            );
            /*
            ->addColumn(
                'frontend_apply',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Frontend Apply'
            );*/

        $setup->getConnection()->createTable($table);

    }

    protected function createForeignKey(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $table = $connection->getTableName("eav_attribute");
        $connection->addForeignKey(
            $setup->getFkName('alphabet_option','attribute_id',$table,'attribute_id'),
            'alphabet_option',
            'attribute_id',
            $table,
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
    }
}

<?php

namespace Sam\CaptureAdminOrder\Setup\Patch\Schema;


use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


class QuoteOrderAdminColumn implements SchemaPatchInterface
{
    private $moduleDataSetup;


    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }


    public static function getDependencies()
    {
        return [];
    }


    public function getAliases()
    {
        return [];
    }


    public function apply()
    {
        $this->moduleDataSetup->startSetup();


        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable('sales_order'),
            'admin_username',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment'  => 'Admin User Name',
            ]
        );
        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable('sales_order'),
            'admin_userid',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment'  => 'Admin User Id',
            ]
        );

        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable('quote'),
            'admin_username',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment'  => 'Admin User Name',
            ]
        );
        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable('quote'),
            'admin_userid',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment'  => 'Admin User Id',
            ]
        );

        $this->moduleDataSetup->endSetup();
    }
}

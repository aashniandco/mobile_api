<?php

namespace Mec\Enquire\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.0') < 0){

		$installer->run('create table enquiry(enquiry_id int not null auto_increment, designer_name varchar(100), sku varchar(100),product_desc varchar(100), customer_name varchar(100), customer_email varchar(100),customer_phone varchar(100), query TEXT ,remote_ip varchar(100),created_at DATETIME DEFAULT CURRENT_TIMESTAMP, primary key(enquiry_id))');


		

		}

        $installer->endSetup();

    }
}
<?php

namespace Mec\PriceModifier\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ResourceConnection;

class Data extends AbstractHelper
{

    protected $resourceConnection;
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ResourceConnection $resourceConnection
    )
    {
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);

    }

    public function getProductIdBySku($sku)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('catalog_product_entity');
        $sql = "SELECT entity_id FROM $tableName WHERE sku = '$sku' LIMIT 1";
        $result = $connection->fetchOne($sql);
        return $result;
    }

    public function getChildProductId($quote_id,$item_id)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('quote_item');
        $sql = "SELECT product_id FROM $tableName WHERE parent_item_id = '$item_id' AND quote_id = '$quote_id' LIMIT 1";
        $result = $connection->fetchOne($sql);
        return $result;
    }

    public function getMainPrice($id)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('catalog_product_entity_decimal');
        $sql = "SELECT value FROM $tableName WHERE attribute_id = '77' AND entity_id = '$id' LIMIT 1";
        $result = $connection->fetchOne($sql);
        return $result;
    }

    public function getWorldPriceRate($id)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('catalog_product_entity_varchar');
        $sql = "SELECT value FROM $tableName WHERE attribute_id = '178' AND entity_id = '$id' LIMIT 1";
        $result = $connection->fetchOne($sql);
        return $result;
    }

    public function getUsPriceRate($id)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('catalog_product_entity_varchar');
        $sql = "SELECT value FROM $tableName WHERE attribute_id = '183' AND entity_id = '$id' LIMIT 1";
        $result = $connection->fetchOne($sql);
        return $result;
    }

    public function getUsSpecialPrice($id)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('catalog_product_entity_varchar');
        $sql = "SELECT value FROM $tableName WHERE attribute_id = '213' AND entity_id = '$id' LIMIT 1";
        $result = $connection->fetchOne($sql);
        return $result;
    }

    public function getWorldSpecialPrice($id)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('catalog_product_entity_varchar');
        $sql = "SELECT value FROM $tableName WHERE attribute_id = '214' AND entity_id = '$id' LIMIT 1";
        $result = $connection->fetchOne($sql);
        return $result;
    }


}

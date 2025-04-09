<?php 

namespace Fermion\Pagelayout\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Checkout\Model\Cart;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;

class ProductSizesOnCart extends AbstractHelper {
    
    /**
     * @var Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    public function __construct(
        Context $context,
        Cart $cart,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        ResourceConnection $resource
        ) {
        parent::__construct($context);
        $this->cart = $cart;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->resource = $resource;
    }


    public function getAvailableChildProducts($productId){
        $sizeArr = array();
        $connection = $this->resource->getConnection(); 
        $sql = "SELECT cpr.product_id,e.sku,eaov2.value AS size,eaov2.option_id FROM catalog_product_super_link cpr 
            INNER JOIN catalog_product_entity e ON cpr.product_id = e.entity_id
            INNER JOIN cataloginventory_stock_status stock ON e.entity_id = stock.product_id AND website_id = 0 AND stock_status = 1
            LEFT JOIN `catalog_product_entity_int` AS `cpei3`
            ON (`cpei3`.`entity_id` = `e`.`entity_id`) AND
            (`cpei3`.`attribute_id` = 141) AND `cpei3`.`store_id` = 0
            LEFT JOIN `eav_attribute_option_value` AS `eaov2`
            ON (`eaov2`.`option_id` = `cpei3`.`value`)
            AND `eaov2`.`store_id` = 0  
            WHERE cpr.parent_id =".$productId;
        $result = $connection->fetchAll($sql);
        if(!empty($result)){
            foreach ($result as $key => $value) {
                $childId = isset($value['product_id']) ? $value['product_id'] : '';
                $sku = isset($value['sku']) ? $value['sku'] : '';
                $size = isset($value['size']) ? $value['size'] : '';
                $optionId = isset($value['option_id']) ? $value['option_id'] : '';
                $sizeArr[$key]['sku'] = $sku;
                $sizeArr[$key]['childProductId'] = $childId;
                $sizeArr[$key]['childProductSize']['label'] = $size;
                $sizeArr[$key]['childProductSize']['value'] = $optionId;
            }
        }
        return $sizeArr;
    }
}
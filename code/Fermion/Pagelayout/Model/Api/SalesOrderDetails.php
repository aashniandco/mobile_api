<?php 

namespace Fermion\Pagelayout\Model\Api;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class SalesOrderDetails{

    protected $logger;
    protected $resource;
    protected $productRepository;
    protected $categoryRepository;

    public function __construct(
        LoggerInterface $logger,
        ResourceConnection $resource,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository
    ){
        $this->logger = $logger;
        $this->resource = $resource;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function getOrders(){
        
        $respArr = array();
        $connection = $this->resource->getConnection();

        try{

            $query = "SELECT order_currency_code, entity_id, increment_id, status, customer_id, customer_email, customer_firstname, customer_middlename, customer_lastname, coupon_code,base_subtotal, subtotal, base_shipping_amount,shipping_amount, discount_amount,base_discount_amount, base_grand_total,grand_total,amstorecredit_base_amount,amstorecredit_amount FROM sales_order WHERE created_at > (now() - INTERVAL 20 DAY)";
            $result = $connection->fetchAll($query);

            if(!empty($result)){
                foreach ($result as $key => $value) {
                    $order_id = $value['entity_id'];
                    $respArr[$key]['order_currency_code'] = isset($value['order_currency_code']) ? $value['order_currency_code'] : '';
                    $respArr[$key]['order_increment_id'] = isset($value['increment_id']) ? $value['increment_id'] : '';
                    $respArr[$key]['status'] = isset($value['status']) ? $value['status'] : '';
                    $respArr[$key]['coupon_code'] = isset($value['coupon_code']) ? $value['coupon_code'] : '';
                    $respArr[$key]['subtotal'] = isset($value['subtotal']) ? $value['subtotal'] : '';
                    $respArr[$key]['base_subtotal'] = isset($value['base_subtotal']) ? $value['base_subtotal'] : '';
                    $respArr[$key]['shipping_amount'] = isset($value['shipping_amount']) ? $value['shipping_amount'] : '';
                    $respArr[$key]['base_shipping_amount'] = isset($value['base_shipping_amount']) ? $value['base_shipping_amount'] : '';
                    $respArr[$key]['discount_amount'] = isset($value['discount_amount']) ? $value['discount_amount'] : '';
                    $respArr[$key]['base_discount_amount'] = isset($value['base_discount_amount']) ? $value['base_discount_amount'] : '';
                    $respArr[$key]['store_credit_amount'] = isset($value['amstorecredit_amount']) ? $value['amstorecredit_amount'] : '';
                    $respArr[$key]['base_store_credit_amount'] = isset($value['amstorecredit_base_amount']) ? $value['amstorecredit_base_amount'] : '';
                    $respArr[$key]['grand_total'] = isset($value['grand_total']) ? $value['grand_total'] : '';
                    $respArr[$key]['base_grand_total'] = isset($value['base_grand_total']) ? $value['base_grand_total'] : '';
                    $respArr[$key]['customer']['customer_email'] = isset($value['customer_email']) ? $value['customer_email'] : '';
                    $respArr[$key]['customer']['customer_firstname'] = isset($value['customer_firstname']) ? $value['customer_firstname'] : '';
                    $respArr[$key]['customer']['customer_middlename'] = isset($value['customer_middlename']) ? $value['customer_middlename'] : '';
                    $respArr[$key]['customer']['customer_lastname'] = isset($value['customer_lastname']) ? $value['customer_lastname'] : '';

                    $itemQuery = "SELECT product_id, sku, name, qty_ordered,price, base_price,original_price, base_original_price FROM sales_order_item WHERE (product_type IN ('configurable', 'mageworx_giftcards') AND order_id = $order_id) OR (product_type = 'simple' AND parent_item_id IS NULL AND order_id = $order_id)";
                    $items = $connection->fetchAll($itemQuery);

                    foreach ($items as $k => $val) {
                        $respArr[$key]['items'][$k]['sku'] = isset($val['sku']) ? $val['sku'] : '';
                        $respArr[$key]['items'][$k]['name'] = isset($val['name']) ? $val['name'] : '';
                        $respArr[$key]['items'][$k]['qty_ordered'] = isset($val['qty_ordered']) ? $val['qty_ordered'] : '';
                        $respArr[$key]['items'][$k]['price'] = isset($val['price']) ? $val['price'] : '';
                        $respArr[$key]['items'][$k]['base_price'] = isset($val['base_price']) ? $val['base_price'] : '';
                        $respArr[$key]['items'][$k]['original_price'] = isset($val['original_price']) ? $val['original_price'] : '';
                        $respArr[$key]['items'][$k]['base_original_price'] = isset($val['base_original_price']) ? $val['base_original_price'] : '';
                        $respArr[$key]['items'][$k]['special_price'] = $val['original_price'] > $val['price'] ? $val['price'] : ''; 
                        $respArr[$key]['items'][$k]['base_special_price'] = $val['base_original_price'] > $val['base_price'] ? $val['base_price'] : ''; 
                        if(isset($val['product_id'])){
                            
                            try{
                                $product = $this->productRepository->getById($val['product_id']);
                                $childproduct = $this->productRepository->get($val['sku']);
                            }catch(\Exception $e){
                                $respArr[$key]['items'][$k]['error'] = $e->getMessage(); 
                                continue;
                            }
                          

                            $stockType = '';
                            $colorNames = '';
                            $themes = '';
                            $edits = '';
                            $bridals = '';
                            $occassions = '';
                            $tags = '';
                            $patterns = '';
                            $designer = '';
                            $size = '';
                            $gender = '';
                            $kids = '';

                            $stockTypeObj = $product->getResource()->getAttribute('stock_type');
                            if($stockTypeObj){
                                $stockType = $stockTypeObj->getFrontend()->getValue($product);
                            }
                            $colorObj = $product->getResource()->getAttribute('color_multiple');
                            if ($colorObj) {
                                $colorNames = $colorObj->getFrontend()->getValue($product);
                            }
                            $themeObj = $product->getResource()->getAttribute('theme');
                            if($themeObj){
                                $themes = $themeObj->getFrontend()->getValue($product);
                            }
                            $editObj = $product->getResource()->getAttribute('edits');
                            if($editObj){
                                $edits = $editObj->getFrontend()->getValue($product);
                            }
                            $bridalObj = $product->getResource()->getAttribute('bridal');
                            if($bridalObj){
                                $bridals = $bridalObj->getFrontend()->getValue($product);
                            }
                            $occassionObj = $product->getResource()->getAttribute('occasions');
                            if($occassionObj){
                                $occassions = $occassionObj->getFrontend()->getValue($product);
                            }
                            $tagObj = $product->getResource()->getAttribute('tags');
                            if($tagObj){
                                $tags = $tagObj->getFrontend()->getValue($product);
                            }
                            $patternObj = $product->getResource()->getAttribute('patterns');
                            if($patternObj){
                                $patterns = $patternObj->getFrontend()->getValue($product);
                            }
                            $designerObj = $product->getResource()->getAttribute('designer');
                            if($designerObj){
                                $designer = $designerObj->getFrontend()->getValue($product);
                            }
                            $sizeObj = $childproduct->getResource()->getAttribute('size');
                            if($sizeObj){
                                $size = $sizeObj->getFrontend()->getValue($childproduct);
                            }
                            $genderObj = $product->getResource()->getAttribute('men_women');
                            if($genderObj){
                                $gender = $genderObj->getFrontend()->getValue($product);
                            }
                            $kidObj = $product->getResource()->getAttribute('kids');
                            if($kidObj){
                                $kids = $kidObj->getFrontend()->getValue($product);
                            }

                            

                            $cust_tax_obj = $product->getResource()->getAttribute('customer_taxes');
                            if($cust_tax_obj){
                                $customer_taxes = $cust_tax_obj->getFrontend()->getValue($product);
                            }
                            $prod_cat_obj = $product->getResource()->getAttribute('product_category');
                            if($prod_cat_obj){
                                $product_category = $prod_cat_obj->getFrontend()->getValue($product);
                            }

                            $respArr[$key]['items'][$k]['product_category'] = $product_category;
                            $respArr[$key]['items'][$k]['stock_type'] = $stockType;
                            $respArr[$key]['items'][$k]['color'] = $colorNames;
                            $respArr[$key]['items'][$k]['theme'] = $themes;
                            $respArr[$key]['items'][$k]['edits'] = $edits;
                            $respArr[$key]['items'][$k]['bridal'] = $bridals;
                            $respArr[$key]['items'][$k]['occasions'] = $occassions;
                            $respArr[$key]['items'][$k]['tags'] = $tags;
                            $respArr[$key]['items'][$k]['patterns'] = $patterns;
                            $respArr[$key]['items'][$k]['designer'] = $designer;
                            $respArr[$key]['items'][$k]['size'] = $size;
                            $respArr[$key]['items'][$k]['internal_reference'] = isset($val['sku']) ? $val['sku'] : '';
                            $respArr[$key]['items'][$k]['markdown'] = $product->getData('markdown');
                            $respArr[$key]['items'][$k]['production_time'] = $product->getData('delivery_days');
                            $respArr[$key]['items'][$k]['short_description'] = $product->getData('short_description');
                            $respArr[$key]['items'][$k]['gender'] = $gender;
                            $respArr[$key]['items'][$k]['kids'] = $kids;
                            $respArr[$key]['items'][$k]['composition'] = $product->getData('composition');
                            $respArr[$key]['items'][$k]['care'] = $product->getData('care');
                            $respArr[$key]['items'][$k]['vendor_code'] = $product->getData('designer_code');
                            $respArr[$key]['items'][$k]['hsn_sac_code'] = $product->getData('hsn_sac_code');
                            $respArr[$key]['items'][$k]['vendors'] = $designer;
                            $respArr[$key]['items'][$k]['customer_taxes'] = $customer_taxes;
                        }
                    }

                    $addressQuery = "SELECT region, postcode, city, street, telephone, country_id, address_type FROM sales_order_address WHERE parent_id = $order_id";
                    $addressData = $connection->fetchAll($addressQuery);

                    foreach ($addressData as $k => $val) {
                        $address_type = $val['address_type'];
                        $respArr[$key]['address'][$k][$address_type]['region'] = isset($val['region']) ? $val['region'] : '';
                        $respArr[$key]['address'][$k][$address_type]['postcode'] = isset($val['postcode']) ? $val['postcode'] : '';
                        $respArr[$key]['address'][$k][$address_type]['city'] = isset($val['city']) ? $val['city'] : '';
                        $respArr[$key]['address'][$k][$address_type]['street'] = isset($val['region']) ? $val['street'] : '';
                        $respArr[$key]['address'][$k][$address_type]['telephone'] = isset($val['telephone']) ? $val['telephone'] : '';
                        $respArr[$key]['address'][$k][$address_type]['country_id'] = isset($val['country_id']) ? $val['country_id'] : '';
                    }

                }
            }
        }
        catch(Exception $e){
            $respArr['error'] = $e->getMessage();
        }
        
        echo json_encode($respArr);die;
    }
}
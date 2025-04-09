<?php 
namespace Fermion\Pagelayout\Helper;


class ReportHelper extends \Magento\Framework\App\Helper\AbstractHelper {            
    protected $store_manager;    
    protected $conn;
   
    public function __construct(        
        \Magento\Store\Model\StoreManagerInterface $storeManager,        
        \Magento\Framework\App\ResourceConnection $resourceConn
        
    ) {        
        $this->store_manager = $storeManager;
        $this->conn = $resourceConn->getConnection();
    }

    /* return available result */
    
    public function getResultForSalesresult($filter,$request_param) {
        try{
            $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get("Magento\Framework\App\ResourceConnection");
            $connection = $resource->getConnection();
            $storeManager   = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $baseUrl = $storeManager->getStore()->getBaseUrl();
            $selectparam = '';
            $leftjoin = '';
            $whereClause = '';
            $cpev18 = 0;
            $cpet = 0;
            $cpet1 = 0;
            $cpei4 = 0;
            $cpev = 0;
            $cpei8 = 0;
            $cpev5 = 0;
            $cped2 = 0;
            $cpei7 = 0;
            $cpei = 0;
            $cpev40 = 0;
            $cpev9 = 0;
            $cped =0;
            $cpev10 =0;
            $cpev11 =0;
            $cped1 =0;
            $cpev19 =0;
            $ccev =0;
            $csi =0;
            $cpei5 =0;
            $eaov5 = 0;
            $eaov6 = 0;
            $eaov7 = 0;
            $eaov8 = 0;
            $eaov11 = 0;
            
            $result = array();
            $cacheurl="https://aashniandco.com/pub/media/catalog/product";
            $designerNames = isset($request_param['designer_name']) ? $request_param['designer_name'] : "";
            $elements = explode(",", $designerNames);
            $elementNew = array();
            foreach ($elements as $element) {
            
            $trimmedElement = trim($element);
            if ($trimmedElement !== "") {
                $elementNew[] = '"' . $trimmedElement . '"';
            }
            }
            
            $newString = implode(",", $elementNew);

            $selectparam = "cpe.sku,cpe.type_id AS product_type";
            $selectArr = array('sku','type_id');
            if($filter != ''){
                foreach($filter as $key => $value)
                {
                    $filtervalue = trim(strtolower($value));
                        
                    if($filtervalue == 'product_name')
                    {
                        $selectparam .= ",cpev18.value AS 'product_name'";
                         $attribute_id = $this->getAttributeIdByName('name');
                       if($cpev18 == 0)
                        {   

                            $leftjoin .= ' LEFT JOIN catalog_product_entity_varchar cpev18 ON cpe.entity_id = cpev18.entity_id AND cpev18.`attribute_id`= '.$attribute_id;
                            $cpev18 =1;
                        }
                         
                        $selectArr[] = 'product_name';
                    
                    } 

                    if($filtervalue == 'short_description')
                    {
                        $selectparam .= ",cpet.value AS 'short_description'";
                         $attribute_id = $this->getAttributeIdByName('short_description');
                        if($cpet == 0)
                        {
                            $leftjoin .= ' LEFT JOIN catalog_product_entity_text cpet ON cpe.entity_id = cpet.entity_id AND cpet.`attribute_id` = '.$attribute_id;
                            $cpet =1;
                        }
                         
                        $selectArr[] = 'short_description';
                        
                    } 
                     if($filtervalue == 'description')
                    {
                        $selectparam .= ",cpet1.value AS 'description'";
                        $attribute_id = $this->getAttributeIdByName('description');
                        if($cpet1 == 0)
                        {
                            $leftjoin .= ' LEFT JOIN catalog_product_entity_text cpet1 ON cpe.entity_id = cpet1.entity_id AND cpet1.`attribute_id` = '.$attribute_id;
                            $cpet1 =1;
                        }
                         
                        $selectArr[] = 'description';
                        
                    } 
                    if($filtervalue == 'men_women')
                    {
                        $selectparam .= ",eaov9.value AS 'men_women'";
                         $attribute_id = $this->getAttributeIdByName('men_women');
                        if($cpei4 == 0)
                        {
                            $leftjoin .= ' LEFT JOIN catalog_product_entity_int cpei4 ON cpe.entity_id = cpei4.entity_id AND cpei4.`attribute_id` = '.$attribute_id.' LEFT JOIN eav_attribute_option_value eaov9 ON cpei4.value = eaov9.option_id';
                            $cpei4 =1;
                        }
                         
                        $selectArr[] = 'men_women';
                        
                    } 
                    if($filtervalue == 'color')
                    {
                        $selectparam .= ",GROUP_CONCAT(DISTINCT eaov.value) AS color";
                         $attribute_id = $this->getAttributeIdByName('color_multiple');
                        if($cpev == 0)
                        {
                            $leftjoin .= ' LEFT JOIN catalog_product_entity_varchar cpev ON cpe.entity_id = cpev.entity_id AND cpev.`attribute_id` = '.$attribute_id.' LEFT JOIN eav_attribute_option_value eaov ON FIND_IN_SET(eaov.option_id,cpev.value)';
                            $cpev =1;
                        }
                         
                        $selectArr[] = 'color';
                        
                    } 
                     if($filtervalue == 'theme')
                    {
                        $selectparam .= ",eaov15.value AS 'theme'";
                         $attribute_id = $this->getAttributeIdByName('theme');
                        if($cpei8 == 0)
                        {
                            $leftjoin .= ' LEFT JOIN catalog_product_entity_int cpei8 ON cpe.entity_id = cpei8.entity_id AND cpei8.`attribute_id` = '.$attribute_id.' LEFT JOIN eav_attribute_option_value eaov15 ON cpei8.value = eaov15.option_id';
                            $cpei8 =1;
                        }
                         
                        $selectArr[] = 'theme';
                        
                    } 
                    if($filtervalue == 'patterns')
                    {
                        $selectparam .= ",GROUP_CONCAT(DISTINCT eaov10.value) AS patterns";
                         $attribute_id = $this->getAttributeIdByName('patterns');
                        if($cpev5 == 0)
                        {
                            $leftjoin .= ' LEFT JOIN catalog_product_entity_varchar cpev5 ON cpe.entity_id = cpev5.entity_id AND cpev5.`attribute_id` = '.$attribute_id.' LEFT JOIN eav_attribute_option_value eaov10 ON FIND_IN_SET(eaov10.option_id,cpev5.value)';
                            $cpev5 =1;
                        }
                         
                        $selectArr[] = 'patterns';
                        
                    }  
                    if($filtervalue == 'weight')
                    {
                        $selectparam .= ",cped2.value AS 'weight'";
                         $attribute_id = $this->getAttributeIdByName('weight');
                        if($cped2 == 0)
                        {
                            $leftjoin .= ' LEFT JOIN catalog_product_entity_decimal cped2 ON cpe.entity_id = cped2.entity_id AND cped2.`attribute_id` = '.$attribute_id;
                            $cped2 =1;
                        }
                         
                        $selectArr[] = 'weight';
                        
                    } 
                if($filtervalue == 'status')
                    {
                    $selectparam .= ",if(cpei7.value=1,'enable','disable') AS status";
                     $attribute_id = $this->getAttributeIdByName('status');
                        if($cpei7 == 0)
                        {
                            $leftjoin .= ' LEFT JOIN catalog_product_entity_int cpei7 ON cpe.entity_id = cpei7.entity_id AND cpei7.`attribute_id` =  '.$attribute_id;
                            $cpei7 =1;
                        }
                         
                    $selectArr[] = 'status';
                        
                    } 
                    
                    if($filtervalue == 'deliverytimes')
                    {
                        $selectparam .= ",eaov4.value AS 'deliverytimes'";
                         $attribute_id = $this->getAttributeIdByName('deliverytimes');
                        if($cpei == 0)
                        {
                            $leftjoin .= ' LEFT JOIN catalog_product_entity_int cpei ON cpe.entity_id = cpei.entity_id AND cpei.`attribute_id` = '.$attribute_id.' LEFT JOIN eav_attribute_option_value eaov4 ON cpei.value = eaov4.option_id';
                            $cpei =1;
                        }
                         
                        $selectArr[] = 'deliverytimes';
                        
                    } 
                    if($filtervalue == 'delivery')
                    {
                        $selectparam .= ",cpev40.value AS 'delivery'";
                        $attribute_id = $this->getAttributeIdByName('delivery');

                        if($cpev40 == 0)
                        {
                            $leftjoin .= ' LEFT JOIN catalog_product_entity_varchar cpev40 ON cpe.entity_id = cpev40.entity_id AND cpev40.`attribute_id`= '.$attribute_id;
                            $cpev40 =1;
                        }
                         
                        $selectArr[] = 'delivery';
                        
                    } 
                    if($filtervalue == 'composition')
                    {
                        $selectparam .= ",cpev9.value AS 'composition'";
                        $attribute_id = $this->getAttributeIdByName('composition');
                        if($cpev9 == 0)
                        {
                            $leftjoin .= ' LEFT JOIN catalog_product_entity_varchar cpev9 ON cpe.entity_id = cpev9.entity_id AND cpev9.`attribute_id` = '.$attribute_id;
                            $cpev9 =1;
                        }
                         
                        $selectArr[] = 'composition';
                        
                    } 
                    if($filtervalue == 'price')
                    {
                    $selectparam .= ",IF(cped3.value, cped3.value, cped.value) AS 'India_Price', IF(cped4.value, cped4.value, cped.value) AS 'United_Kingdom_Price', IF(cped5.value, cped5.value, cped.value) AS 'United_States_Price', IF(cped6.value, cped6.value, cped.value) AS 'International_Price'";
                        if($cped == 0)
                        {
                        $leftjoin .= ' LEFT JOIN catalog_product_entity_decimal cped ON (cped.entity_id = cpe.entity_id) AND (cped.attribute_id = 77) AND (cped.store_id = 0) LEFT JOIN catalog_product_entity_decimal cped3 ON cpe.entity_id = cped3.entity_id AND cped3.`attribute_id` = 77 AND (cped3.store_id = 1) LEFT JOIN catalog_product_entity_decimal cped4 ON (cped4.entity_id = cpe.entity_id) AND (cped4.attribute_id = 77) AND (cped4.store_id = 2) LEFT JOIN catalog_product_entity_decimal cped5 ON (cped5.entity_id = cpe.entity_id) AND (cped5.attribute_id = 77) AND (cped5.store_id = 3) LEFT JOIN catalog_product_entity_decimal cped6 ON (cped6.entity_id = cpe.entity_id) AND (cped6.attribute_id = 77) AND (cped6.store_id = 4)';
                            $cped =1;
                        }
                         
                    $selectArr[] = 'India_Price';
                    $selectArr[] = 'United_Kingdom_Price';
                    $selectArr[] = 'United_States_Price';
                    $selectArr[] = 'International_Price';
                        
                    } 
                    if($filtervalue == 'special_price')
                    {
                    $selectparam .= ",IF(cped11.value IS NOT NULL AND (cpedt1.value IS NULL OR UNIX_TIMESTAMP(NOW()) >= UNIX_TIMESTAMP(cpedt1.value) AND (UNIX_TIMESTAMP(NOW()) <= UNIX_TIMESTAMP(cpedt2.value) OR cpedt2.value IS NULL)), cped11.value, NULL) AS 'India_Special_Price', IF(cped21.value IS NOT NULL AND (cpedt1.value IS NULL OR UNIX_TIMESTAMP(NOW()) >= UNIX_TIMESTAMP(cpedt1.value) AND (UNIX_TIMESTAMP(NOW()) <= UNIX_TIMESTAMP(cpedt2.value) OR cpedt2.value IS NULL)), cped21.value, NULL) AS 'UK_Special_Price',IF(cped31.value IS NOT NULL AND (cpedt1.value IS NULL OR UNIX_TIMESTAMP(NOW()) >= UNIX_TIMESTAMP(cpedt1.value) AND (UNIX_TIMESTAMP(NOW()) <= UNIX_TIMESTAMP(cpedt2.value) OR cpedt2.value IS NULL)), cped31.value, NULL) AS 'US_Special_Price',IF(cped41.value IS NOT NULL AND (cpedt1.value IS NULL OR UNIX_TIMESTAMP(NOW()) >= UNIX_TIMESTAMP(cpedt1.value) AND (UNIX_TIMESTAMP(NOW()) <= UNIX_TIMESTAMP(cpedt2.value) OR cpedt2.value IS NULL)), cped41.value, NULL) AS 'International_Special_Price'";
                        if($cped1 == 0)
                        {
                        $leftjoin .= ' LEFT JOIN catalog_product_entity_decimal cped11 ON (cped11.entity_id = cpe.entity_id) AND (cped11.attribute_id = 78) AND (cped11.store_id = 1) LEFT JOIN catalog_product_entity_decimal cped21 ON (cped21.entity_id = cpe.entity_id) AND (cped21.attribute_id = 78) AND (cped21.store_id = 2) LEFT JOIN catalog_product_entity_decimal cped31 ON (cped31.entity_id = cpe.entity_id) AND (cped31.attribute_id = 78) AND (cped31.store_id = 3) LEFT JOIN catalog_product_entity_decimal cped41 ON (cped41.entity_id = cpe.entity_id) AND (cped41.attribute_id = 78) AND (cped41.store_id = 4) LEFT JOIN catalog_product_entity_datetime cpedt1 ON cpe.entity_id = cpedt1.entity_id AND cpedt1.attribute_id = 79 AND cpedt1.store_id = 1 LEFT JOIN catalog_product_entity_datetime cpedt2 ON cpe.entity_id = cpedt2.entity_id AND cpedt2.attribute_id = 80 AND cpedt2.store_id = 1';

                            $cped1 =1;
                        }
                         
                    $selectArr[] = 'India_Special_Price';
                    $selectArr[] = 'UK_Special_Price';
                    $selectArr[] = 'US_Special_Price';
                    $selectArr[] = 'International_Special_Price';
                        
                    } 
                     if($filtervalue == 'designer_code')
                    {
                        $selectparam .= ",cpev19.value AS 'designer_code'";
                        $attribute_id = $this->getAttributeIdByName('designer_code');
                        if($cpev19 == 0)
                        {
                            $leftjoin .= ' LEFT JOIN catalog_product_entity_varchar cpev19 ON cpe.entity_id = cpev19.entity_id AND cpev19.`attribute_id` = '.$attribute_id;
                            $cpev19 =1;
                        }
                         
                        $selectArr[] = 'designer_code';
                        
                    } 
                     if($filtervalue == 'categories')
                    {
                        $selectparam .= ",GROUP_CONCAT(DISTINCT ccev.value) AS categories";
                        if($ccev == 0)
                        {
                            $leftjoin .= ' LEFT JOIN catalog_category_product ccp ON ccp.product_id = cpe.entity_id LEFT JOIN catalog_category_entity_varchar ccev ON ccev.entity_id = ccp.category_id AND ccev.attribute_id = 45';
                            $ccev =1;
                        }
                         
                        $selectArr[] = 'categories';
                        
                    } 
                if($filtervalue == 'qty')
                    {
                        $selectparam .= ",csi.qty AS 'Quantity'";
                        if($csi == 0)
                        {
                            $leftjoin .= ' LEFT JOIN cataloginventory_stock_item csi ON cpe.entity_id = csi.product_id';
                            $csi =1;
                        }
                         
                        $selectArr[] = 'Quantity';
                        
                    }
                    if($filtervalue == 'stock_status')
                    {
                        $selectparam .= ",if(cpei5.stock_status = 1,'in stock','out of stock') AS stock_status";
                        $attribute_id = $this->getAttributeIdByName('quantity_and_stock_status');
                        if($cpei5 == 0)
                        {
                            $leftjoin .= ' LEFT JOIN cataloginventory_stock_status cpei5 ON cpe.entity_id = cpei5.product_id AND cpei5.`website_id` = 0';
                            $cpei5 =1;
                        }
                         
                        $selectArr[] = 'stock_status';
                        
                    }  

                    if($filtervalue == 'designer_name')
                    {
                        $selectparam .= ",eaov5.value as designer_name";
                        $attribute_id = $this->getAttributeIdByName('designer');
                        if($eaov5 == 0)
                        {
                            $leftjoin .= ' LEFT JOIN catalog_product_entity_int cpei1 ON cpe.entity_id = cpei1.entity_id AND cpei1.`attribute_id` = '.$attribute_id.' LEFT JOIN eav_attribute_option_value eaov5 ON cpei1.value = eaov5.option_id';
                            $eaov5 =1;
                        }
                         
                        $selectArr[] = 'designer_name';
                        
                    } 

                if($filtervalue == 'kids')
                {
                    $selectparam .= ",eaov6.value as 'kids'";
                    $attribute_id = $this->getAttributeIdByName('kids');
                    if($eaov6 == 0)
                    {
                        $leftjoin .= ' LEFT JOIN catalog_product_entity_int cpei3 ON cpe.entity_id = cpei3.entity_id AND cpei3.attribute_id = '.$attribute_id.' LEFT JOIN eav_attribute_option_value eaov6 ON cpei3.value = eaov6.option_id';
                        $eaov6 =1;
                    }
                     
                    $selectArr[] = 'kids';
                    
                } 

                if($filtervalue == 'occasions')
                {
                    $selectparam .= ",GROUP_CONCAT(DISTINCT eaov7.value) as 'occasions'";
                    $attribute_id = $this->getAttributeIdByName('occasions');
                    if($eaov7 == 0)
                    {
                        $leftjoin .= ' LEFT JOIN catalog_product_entity_varchar cpev20 ON cpev20.entity_id = cpe.entity_id AND cpev20.attribute_id = '.$attribute_id.' LEFT JOIN eav_attribute_option_value eaov7 ON FIND_IN_SET(eaov7.option_id, cpev20.value) > 0';
                        $eaov7 =1;
                    }
                     
                    $selectArr[] = 'occasions';
                    
                } 

                if($filtervalue == 'edits')
                {
                    $selectparam .= ",GROUP_CONCAT(DISTINCT eaov8.value) as 'A+CO Edits'";
                    $attribute_id = $this->getAttributeIdByName('edits');
                    if($eaov8 == 0)
                    {
                        $leftjoin .= ' LEFT JOIN catalog_product_entity_varchar cpev21 ON cpev21.entity_id = cpe.entity_id AND cpev21.attribute_id = '.$attribute_id.' LEFT JOIN eav_attribute_option_value eaov8 ON FIND_IN_SET(eaov8.option_id, cpev21.value) > 0';
                        $eaov8 =1;
                    }
                     
                    $selectArr[] = 'edits';
                    
                }

                if($filtervalue == 'bridal')
                {
                    $selectparam .= ",GROUP_CONCAT(DISTINCT eaov11.value) as 'bridal'";
                    $attribute_id = $this->getAttributeIdByName('bridal');
                    if($eaov11 == 0)
                    {
                        $leftjoin .= ' LEFT JOIN catalog_product_entity_varchar cpev22 ON cpev22.entity_id = cpe.entity_id AND cpev22.attribute_id = '.$attribute_id.' LEFT JOIN eav_attribute_option_value eaov11 ON FIND_IN_SET(eaov11.option_id, cpev22.value) > 0';
                        $eaov11 =1;
                    }
                     
                    $selectArr[] = 'bridal';
                    
                }

                    
                }


                if($newString != ''){
                    if($eaov5 == 0 ){
                        $attribute_id = $this->getAttributeIdByName('designer');
                        $leftjoin .= ' LEFT JOIN catalog_product_entity_int cpei1 ON cpe.entity_id = cpei1.entity_id AND cpei1.`attribute_id` = '.$attribute_id.' LEFT JOIN eav_attribute_option_value eaov5 ON cpei1.value = eaov5.option_id';
                    }

                    $whereClause = " WHERE eaov5.value in($newString)";
                }



              
                

                $query = 'SELECT '.$selectparam.' FROM catalog_product_entity cpe '. $leftjoin.$whereClause.' GROUP BY cpe.entity_id  ORDER BY cpe.entity_id DESC';
                $tableName = $this->conn->getTableName('sales_report_queries');
    
                $this->conn->insert($tableName, [
                    'query_string' => $query,
                    'status' => 'pending',
                    'select_array' => json_encode($selectArr),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
    
              
            
                
                $result = [
                    'success' => true,
                    'message' => 'Query inserted successfully',
                    'error' => 0
                ];
                

            }
        }catch(Exception $e){
            $result = [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 1
            ];
            
    
        }
        return $result ;
        
    }

    public function getAttributeIdByName($attribute_name)
    {
       return $this->conn->fetchOne("select attribute_id from eav_attribute where attribute_code='$attribute_name' and entity_type_id=4");
    }
}
        
 


?>
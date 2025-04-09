<?php 
namespace Fermion\Pagelayout\Model\Solr;

class SolrUtil extends \Magento\Framework\Model\AbstractModel {
	protected $_img_helper;
	protected $_prod_repo;
	protected $_stock_repo;
	protected $_conn;

	public function __construct(
		\Magento\Catalog\Helper\Image $image,
		\Magento\Catalog\Model\ProductRepository $productRepository,
		\Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
		\Magento\Framework\App\ResourceConnection $resourceConn
	) {
		$this->_img_helper = $image;		
		$this->_prod_repo = $productRepository;
		$this->_stock_repo = $stockItemRepository;
		$this->_conn = $resourceConn->getConnection();
	}

    /*generate xml based format data from product data array*/
    public function generateXMLFormatData($prodArr = array()) {  
    	
    	$objectManager   = \Magento\Framework\App\ObjectManager::getInstance(); 
    	$attribute =$objectManager->create('Magento\Eav\Api\AttributeRepositoryInterface')->get(\Magento\Catalog\Model\Product::ENTITY, 'size');

		$sort = 0;
		$sizeOptionArr = array();
	    foreach ($attribute->getOptions() as $option) {
	    	if(trim($option->getLabel())){
	    		$sort++; 
	    		$sizeOptionArr[$option->getLabel()] = $sort;
	    		
	    	}
	    }


	    $attributeShippedIn =$objectManager->create('Magento\Eav\Api\AttributeRepositoryInterface')->get(\Magento\Catalog\Model\Product::ENTITY, 'deliverytimes');

		$sort = 0;
		$shippedInOptionArr = array();
	    foreach ($attributeShippedIn->getOptions() as $option) {
	    	if(trim($option->getLabel())){
	    		$sort++; 
	    		$shippedInOptionId = $attributeShippedIn->getSource()->getOptionId($option->getLabel());
	    		$shippedInOptionArr[$shippedInOptionId] = $sort;

	    		
	    	}
	    }
	
	    



	    



    	$xml_data1 = "";
    	if (!empty($prodArr)) {
    		$xml_data1 .= "<add>\n";    		
    		foreach ($prodArr as $index => $prod) {
			$prodId = isset($prod['prod_en_id']) ? $prod['prod_en_id'] : '0';
    			$xml_data = "\t<doc>\n";
    			;
    			foreach ($prod as $key => $value) {
    				/*generate designer fields in xml*/
	    			if ($key == "designers" && !empty($value)) {
	    				foreach ($value as $des_id => $d_name) {
	    					$xml_data .= "\t<field name='designer_name'><![CDATA[".$this->repalceNonAscii($d_name)."]]></field>\n";
	    					$xml_data .= "\t<field name='designer_id'><![CDATA[".$this->repalceNonAscii($des_id)."]]></field>\n";
	    					$xml_data .= "\t<field name='designer_token'><![CDATA[".$this->repalceNonAscii($des_id).'|'.$this->repalceNonAscii($d_name)."]]></field>\n"; 	
	    				}
	    			} 

	    			/*generate occasions fields in xml*/
	    			if ($key == "occasions" && !empty($value)) {
	    				foreach ($value as $occ_id => $occ_name) {
	    					$xml_data .= "\t<field name='occasion_name'><![CDATA[".$this->repalceNonAscii($occ_name)."]]></field>\n";
	    					$xml_data .= "\t<field name='occasion_id'><![CDATA[".$this->repalceNonAscii($occ_id)."]]></field>\n";
	    					$xml_data .= "\t<field name='occasion_token'><![CDATA[".$this->repalceNonAscii($occ_id).'|'.$this->repalceNonAscii($occ_name)."]]></field>\n"; 
	    				}
	    			} 		

	    			/*generate genders fields in xml*/
	    			if ($key == "genders" && !empty($value)) {
	    				foreach ($value as $gen_id => $gen_name) {
	    					$xml_data .= "\t<field name='gender_name'><![CDATA[".$this->repalceNonAscii($gen_name)."]]></field>\n";
	    					$xml_data .= "\t<field name='gender_id'><![CDATA[".$this->repalceNonAscii($gen_id)."]]></field>\n";
	    					$xml_data .= "\t<field name='gender_token'><![CDATA[".$this->repalceNonAscii($gen_id).'|'.$this->repalceNonAscii($gen_name)."]]></field>\n"; 
	    				}
	    			} 



	    		   /*generate kids fields in xml*/
	    			if ($key == "kids" && !empty($value)) {
	    				foreach ($value as $kid_id => $kid_name) {
	    					$xml_data .= "\t<field name='kid_name'><![CDATA[".$this->repalceNonAscii($kid_name)."]]></field>\n";
	    					$xml_data .= "\t<field name='kid_id'><![CDATA[".$this->repalceNonAscii($kid_id)."]]></field>\n";
	    					$xml_data .= "\t<field name='kid_token'><![CDATA[".$this->repalceNonAscii($kid_id).'|'.$this->repalceNonAscii($kid_name)."]]></field>\n"; 
	    				}
	    			} 




	    			/*generate tags fields in xml*/
	    			if ($key == "tags" && !empty($value)) {
	    				foreach ($value as $tags_id => $tags_name) {
	    					$xml_data .= "\t<field name='tags_name'><![CDATA[".$this->repalceNonAscii($tags_name)."]]></field>\n";
	    					$xml_data .= "\t<field name='tags_id'><![CDATA[".$this->repalceNonAscii($tags_id)."]]></field>\n";
	    					$xml_data .= "\t<field name='tags_token'><![CDATA[".$this->repalceNonAscii($tags_id).'|'.$this->repalceNonAscii($tags_name)."]]></field>\n"; 
	    				}
	    			}

	    			/*generate theme fields in xml*/
	    			if ($key == "themes" && !empty($value)) {
	    				foreach ($value as $theme_id => $theme_name) {
	    					$xml_data .= "\t<field name='theme_name'><![CDATA[".$this->repalceNonAscii($theme_name)."]]></field>\n";
	    					$xml_data .= "\t<field name='theme_id'><![CDATA[".$this->repalceNonAscii($theme_id)."]]></field>\n";
	    					$xml_data .= "\t<field name='theme_token'><![CDATA[".$this->repalceNonAscii($theme_id).'|'.$this->repalceNonAscii($theme_name)."]]></field>\n"; 
	    				}
	    			}

	    			/*generate producttags fields in xml*/
	    			if ($key == "product_tags" && !empty($value)) {
	    				foreach ($value as $product_tags_identifier => $product_tags_name) {
	    					$xml_data .= "\t<field name='product_tags_name'><![CDATA[".$this->repalceNonAscii($product_tags_name)."]]></field>\n";
	    					$xml_data .= "\t<field name='product_tags_identifier'><![CDATA[".$this->repalceNonAscii($product_tags_identifier)."]]></field>\n";
	    					// $xml_data .= "\t<field name='product_tags_identifier'><![CDATA[".$this->repalceNonAscii($product_tags_identifier).'|'.$this->repalceNonAscii($product_tags_name)."]]></field>\n"; 
	    				}
	    			}
	    			


	    			/*generate season fields in xml*/
	    			if ($key == "seasons" && !empty($value)) {
	    				foreach ($value as $seas_id => $seas_name) {
	    					$xml_data .= "\t<field name='season_name'><![CDATA[".$this->repalceNonAscii($seas_name)."]]></field>\n";
	    					$xml_data .= "\t<field name='season_id'><![CDATA[".$this->repalceNonAscii($seas_id)."]]></field>\n";
	    					$xml_data .= "\t<field name='season_token'><![CDATA[".$this->repalceNonAscii($seas_id).'|'.$this->repalceNonAscii($seas_name)."]]></field>\n";
	    				} 
	    			} 

	    			/*generate fabric fields in xml*/
	    			if ($key == "edit" && !empty($value)) {
	    				foreach ($value as $edit_id => $edit_name) {
	    					$xml_data .= "\t<field name='a_co_edit_name'><![CDATA[".$this->repalceNonAscii($edit_name)."]]></field>\n";
	    					$xml_data .= "\t<field name='a_co_edit_id'><![CDATA[".$this->repalceNonAscii($edit_id)."]]></field>\n";
	    					$xml_data .= "\t<field name='a_co_edit_token'><![CDATA[".$this->repalceNonAscii($edit_id).'|'.$this->repalceNonAscii($edit_name)."]]></field>\n";
	    				} 
	    			} 


	    			/*generate fabric fields in xml*/
	    			if ($key == "calender" && !empty($value)) {
	    				foreach ($value as $calender_id => $calender_name) {
	    					$xml_data .= "\t<field name='calender_name'><![CDATA[".$this->repalceNonAscii($calender_name)."]]></field>\n";
	    					$xml_data .= "\t<field name='calender_id'><![CDATA[".$this->repalceNonAscii($calender_id)."]]></field>\n";
	    					$xml_data .= "\t<field name='calender_token'><![CDATA[".$this->repalceNonAscii($calender_id).'|'.$this->repalceNonAscii($calender_name)."]]></field>\n";
	    				} 
	    			} 

	    			/*generate fabric fields in xml*/
	    			if ($key == "patterns" && !empty($value)) {
	    				foreach ($value as $patterns_id => $patterns_name) {
	    					$xml_data .= "\t<field name='patterns_name'><![CDATA[".$this->repalceNonAscii($patterns_name)."]]></field>\n";
	    					$xml_data .= "\t<field name='patterns_id'><![CDATA[".$this->repalceNonAscii($patterns_id)."]]></field>\n";
	    					$xml_data .= "\t<field name='patterns_token'><![CDATA[".$this->repalceNonAscii($patterns_id).'|'.$this->repalceNonAscii($patterns_name)."]]></field>\n";
	    				} 
	    			} 


	    			/*generate fabric fields in xml*/
	    			if ($key == "bridal" && !empty($value)) {
	    				foreach ($value as $bridal_id => $bridal_name) {
	    					$xml_data .= "\t<field name='bridal_name'><![CDATA[".$this->repalceNonAscii($bridal_name)."]]></field>\n";
	    					$xml_data .= "\t<field name='bridal_id'><![CDATA[".$this->repalceNonAscii($bridal_id)."]]></field>\n";
	    					$xml_data .= "\t<field name='bridal_token'><![CDATA[".$this->repalceNonAscii($bridal_id).'|'.$this->repalceNonAscii($bridal_name)."]]></field>\n";
	    				} 
	    			} 

	    			/*generate size fields in xml*/
	    			if ($key == "sizes" && !empty($value)) {
	    				foreach ($value as $size_id => $size_name) {
	    					$position = isset($sizeOptionArr[$size_name]) ? $sizeOptionArr[$size_name] : 0;
	    					
	    					$xml_data .= "\t<field name='size_name'><![CDATA[".$this->repalceNonAscii($size_name)."]]></field>\n";
	    					$xml_data .= "\t<field name='size_id'><![CDATA[".$this->repalceNonAscii($size_id)."]]></field>\n";

	    					$xml_data .= "\t<field name='size_token'><![CDATA[".$this->repalceNonAscii($position).'|'.$this->repalceNonAscii($size_id).'|'.$this->repalceNonAscii($size_name)."]]></field>\n";


	    				}
	    			}


	    			/*generate rts size fields in xml*/
	    			if ($key == "rts_sizes" && !empty($value)) {
	    				foreach ($value as $size_id => $size_name) {
	    					$position = isset($sizeOptionArr[$size_name]) ? $sizeOptionArr[$size_name] : 0;
	    					$xml_data .= "\t<field name='rts_size_name'><![CDATA[".$this->repalceNonAscii($size_name)."]]></field>\n";
	    					$xml_data .= "\t<field name='rts_size_id'><![CDATA[".$this->repalceNonAscii($size_id)."]]></field>\n";

	    					$xml_data .= "\t<field name='rts_size_token'><![CDATA[".$this->repalceNonAscii($position).'|'.$this->repalceNonAscii($size_id).'|'.$this->repalceNonAscii($size_name)."]]></field>\n";


	    				}
	    			}  

              if ($key == "prod_availability" && !empty($value)) {
	    				// echo "========avail=====";
	    				// print_r($value);
	    				foreach ($value as $availability_id => $availability_label) {
	    					//echo "id::".$availability_id;
	    					$xml_data .= "\t<field name='prod_availability_label'><![CDATA[".$this->repalceNonAscii($prod_availability_label)."]]></field>\n";
	    					$xml_data .= "\t<field name='prod_availability_id'><![CDATA[".$this->repalceNonAscii($prod_availability_id)."]]></field>\n";

	    					// $xml_data .= "\t<field name='availability_token'><![CDATA[".$this->repalceNonAscii($availability_id).'|'.$this->repalceNonAscii($availability_name)."]]></field>\n";


	    				}
	    			} 


	    			// if ($key == "prod_availability" && !empty($value)) {
	    			// 	// echo "========avail=====";
	    			// 	// print_r($value);
	    			// 	foreach ($value as $availability_id => $availability_name) {
	    			// 		//echo "id::".$availability_id;
	    			// 		$xml_data .= "\t<field name='availability_name'><![CDATA[".$this->repalceNonAscii($availability_name)."]]></field>\n";
	    			// 		$xml_data .= "\t<field name='availability_id'><![CDATA[".$this->repalceNonAscii($availability_id)."]]></field>\n";

	    			// 		$xml_data .= "\t<field name='availability_token'><![CDATA[".$this->repalceNonAscii($availability_id).'|'.$this->repalceNonAscii($availability_name)."]]></field>\n";


	    			// 	}
	    			// } 

	    			/*generate color fields in xml*/
	    			if ($key == "colors" && !empty($value)) {
	    				foreach ($value as $col_id => $col_name) {
	    					$xml_data .= "\t<field name='color_name'><![CDATA[".$this->repalceNonAscii($col_name)."]]></field>\n";
	    					$xml_data .= "\t<field name='color_id'><![CDATA[".$this->repalceNonAscii($col_id)."]]></field>\n";
	    					$xml_data .= "\t<field name='color_token'><![CDATA[".$this->repalceNonAscii($col_id).'|'.$this->repalceNonAscii($col_name)."]]></field>\n";	
	    				}
	    			} 

	    			/*generate delivery time field in xml*/
	    			// if ($key == "delivery_time" && !empty($value)) {
	    			// 	foreach ($value as $del_time) {
	    					
	    			// 		$xml_data .= "\t<field name='delivery_time'><![CDATA[".$this->repalceNonAscii(trim(strtolower($del_time)))."]]></field>\n";
	    			// 		$xml_data .= "\t<field name='delivery_token'><![CDATA[".$this->repalceNonAscii($position).'|'.$this->repalceNonAscii(trim(strtolower($del_time)))."]]></field>\n";

	    			// 		$xml_data .= "\t<field name='color_id'><![CDATA[".$this->repalceNonAscii($col_id)."]]></field>\n";

	    			// 		$days = '';
				    //         if (strpos(strtolower($del_time), 'week') !== false) {
				    //             $days  = 7 * intval($del_time);
				    //         } elseif (strpos(strtolower($del_time), 'month') !== false) {
				    //             $days  = 30 * intval($del_time);
				    //         } elseif (strpos(strtolower($del_time), 'day') !== false) {
				    //             $days  = intval($del_time);
				    //         }elseif (strpos(strtolower($del_time), 'hour') !== false) {
				    //             $days  = intval($del_time) / 24;
				    //         }
	    			// 		$xml_data .= "\t<field name='delivery_days'><![CDATA[".$this->repalceNonAscii($days)."]]></field>\n";
	    			// 	}
	    			// } 


	    			if ($key == "delivery_time" && !empty($value)) {
	    				foreach ($value as $delivery_id => $delivery_name) {

	    					$position = isset($shippedInOptionArr[$delivery_id]) ? $shippedInOptionArr[$delivery_id] : 1000;


	    					$xml_data .= "\t<field name='delivery_time'><![CDATA[".$this->repalceNonAscii($delivery_name)."]]></field>\n";
	    					$xml_data .= "\t<field name='delivery_id'><![CDATA[".$this->repalceNonAscii($delivery_id)."]]></field>\n";
	    					$xml_data .= "\t<field name='delivery_token'><![CDATA[".$this->repalceNonAscii($position).'|'.$this->repalceNonAscii($delivery_id).'|'.$this->repalceNonAscii($delivery_name)."]]></field>\n"; 	
	    				}
	    			} 


	    			if ($key == "child_delivery_time" && !empty($value)) {
	    				
	    				foreach ($value as $child_delivery_id => $child_delivery_name) {
	    					
	    					$position = isset($shippedInOptionArr[$child_delivery_id]) ? $shippedInOptionArr[$child_delivery_id] : 1000;


	    					$xml_data .= "\t<field name='child_delivery_time'><![CDATA[".$this->repalceNonAscii($child_delivery_name)."]]></field>\n";
	    					

	    					$xml_data .= "\t<field name='child_delivery_time_token'><![CDATA[".$this->repalceNonAscii($position).'|'.$this->repalceNonAscii($child_delivery_id).'|'.$this->repalceNonAscii($child_delivery_name)."]]></field>\n";
	    				}
	    			} 


	    			


	    			if ($key == "child_delivery_time" && !empty($value)) {
	    				foreach ($value as $d_key => $del_time) {
	    			
	    					$days = '';
				            if (strpos(strtolower($del_time), 'week') !== false) {
				                $days  = 7 * intval($del_time);
				            } elseif (strpos(strtolower($del_time), 'month') !== false) {
				                $days  = 30 * intval($del_time);
				            } elseif (strpos(strtolower($del_time), 'day') !== false) {
				                $days  = intval($del_time);
				            }elseif (strpos(strtolower($del_time), 'hour') !== false) {
				                $days  = intval($del_time) / 24;
				            }
	    					$xml_data .= "\t<field name='child_shipped_in_".$d_key."'><![CDATA[".$this->repalceNonAscii($days)."]]></field>\n";

	    					
	    					

	    				}
	    			}
	    			
	    			/*generate price field in xml*/
	    			if ($key == "prices" && !empty($value)) {
	    				foreach ($value as $pr_k => $pr_v) {
	    					$xml_data .= "\t<field name='".$pr_k."'><![CDATA[".$this->repalceNonAscii($pr_v)."]]></field>\n";
	    				}	    				
	    			}

	    			

	    			/*generate category fields in xml*/
	    			if ($key == "categories" && !empty($value)) {
	    				foreach ($value as $store_key => $cat_arr) {
	    					if (!empty($cat_arr)) {
	    						foreach ($cat_arr as $cat_id => $cat_name) {
	    							$xml_data .= "\t<field name='".$store_key."_name'><![CDATA[".$this->repalceNonAscii(trim(strtolower($cat_name)))."]]></field>\n";
	    							$xml_data .= "\t<field name='".$store_key."_id'><![CDATA[".$this->repalceNonAscii(trim(strtolower($cat_id)))."]]></field>\n";
	    						}
	    					} 
	    				}
	    			}

	    			/* generate category mapping field in xml */
	    			if ($key == "categories_map" && !empty($value)) {
	    				foreach ($value as $store_key => $cat_arr) {
	    					if (!empty($cat_arr)) {
	    						foreach ($cat_arr as $cat_path => $cat_url) {
	    							$cataIdArr = explode("/",$cat_path);

	    							if(!empty($cataIdArr)){
	    								$catId = end($cataIdArr);
	    							}else{
	    								$catId = '';
	    							}
	    							$catname = isset($prod['categories'][$store_key][$catId]) ? $prod['categories'][$store_key][$catId] : '';
	    						
	    							
	    							$xml_data .= "\t<field name='".$store_key."_path'><![CDATA[".$this->repalceNonAscii(trim(strtolower($cat_path)))."]]></field>\n";
	    							$xml_data .= "\t<field name='".$store_key."_url_path'><![CDATA[".$this->repalceNonAscii(trim(strtolower($cat_url)))."]]></field>\n";
	    							$xml_data .= "\t<field name='".$store_key."_token'><![CDATA[".$this->repalceNonAscii(trim(strtolower($cat_path)))."|".$this->repalceNonAscii(trim(strtolower($cat_url)))."|".trim($catname)."]]></field>\n";
	    						}
	    					} 
	    				}
	    			}

	    			

	    			// generate product descount percentage in xml
	    			if ($key == "discount_percent" && !empty($value)) {
	    				foreach ($value as $disc_k => $disc_v) {
	    					$xml_data .= "\t<field name='".$disc_k."'><![CDATA[".$this->repalceNonAscii($disc_v)."]]></field>\n";
	    				}
	    			}





	    			/* generate catalog position field in xml */
	    			if ($key == "cat_position" && !empty($value)) {
	    				foreach ($value as $c_pos_k => $c_pos_v) {
	    					$xml_data .= "\t<field name='".$c_pos_k."'><![CDATA[".$this->repalceNonAscii($c_pos_v)."]]></field>\n";
	    				}
	    			}

	    			/* generate catalog position field in xml */
	    			if ($key == "new_sort_attr" && !empty($value)) {
	    				foreach ($value as $c_sort_k => $c_sort_v) {
	    					$xml_data .= "\t<field name='".$c_sort_k."'><![CDATA[".$this->repalceNonAscii($c_sort_v)."]]></field>\n";
	    				}
	    			}

	    			/* generate catalog position field in xml */
	    			if ($key == "new_sort_attr_int" && !empty($value)) {
	    				foreach ($value as $c_sort_k => $c_sort_v) {
	    					$xml_data .= "\t<field name='".$c_sort_k."'><![CDATA[".$this->repalceNonAscii($c_sort_v)."]]></field>\n";
	    				}
	    			}

	    			/* generate product urls field in xml*/
	    			if ($key == "prod_urls" && !empty($value)) {
	    				foreach ($value as $ur_k => $ur_v) {
	    					$xml_data .= "\t<field name='".$ur_k."'><![CDATA[".$this->repalceNonAscii($ur_v)."]]></field>\n";
	    				}
	    			}

	    			/* generate product urls field in xml*/
	    			if ($key == "enquire" && !empty($value)) {
	    				foreach ($value as $ur_k => $ur_v) {
	    					$xml_data .= "\t<field name='".$ur_k."'><![CDATA[".$this->repalceNonAscii($ur_v)."]]></field>\n";
	    				}
	    			}

	    			/*generate store id fields in xml*/
	    			if ($key == "prod_store_ids" && !empty($value)) {
	    				foreach ($value as $st_id) {
	    					$xml_data .= "\t<field name='prod_store_ids'><![CDATA[".$this->repalceNonAscii($st_id)."]]></field>\n";	
	    				}	    				
	    			} 

	    			/*generate extra product data field in xml*/
	    			if (($key != "designers" && $key != "enquire" && $key != "child_delivery_time" && $key != "occasions" && $key != "genders" && $key != "tags" && $key != "kids" && $key != "product_tags" && $key != "fabric" && $key != "seasons" && $key != "sizes" && $key != "rts_sizes" && $key != "colors" && $key != "delivery_time" && $key != "prices" && $key != "categories" && $key != "categories_map" && $key != "prod_store_ids" && $key != "cat_pop_attr" && $key != "cat_pop_coll" && $key != "cat_just_in" && $key != "cat_position" && $key != "prod_urls") && $value !== NULL && $key != "discount_percent" && $key != "prod_availability" && $key != "new_sort_attr" && $key != "new_sort_attr_int" && $key != "enquire" && $key != "child_delivery_time" && $key != "edit" && $key != "patterns" && $key != "bridal" && $key != "calender"  && $key != "themes" ) {
	    				$xml_data .= "\t<field name='".$key."'><![CDATA[".$this->repalceNonAscii($value)."]]></field>\n";
	    			} 
    			}
			$xml_data .= "\t</doc>\n";
			$xml_data1 .= $xml_data;
			//$this->writeXmlFile($prodId, $xml_data);
    		}
    		$xml_data1 .= "</add>";
    	} else {
    		$xml_data1 .= "<add>\n";
    		$xml_data1 .= "\t<doc></doc>\n";
    		$xml_data1 .= "</add>";
    	}   
      //	echo $xml_data1;die;
    	return $xml_data1;
    	
    	 
    }

     
	public function writeXmlFile($prodId, $xmlData) {
		$filePath = "/var/www/html/aashni/pub/media/solr_files/";
		try {
			file_put_contents($filePath.$prodId.'.xml', $xmlData);
		}catch(Exception $e) {
			echo $e->getTraceAsString();
		}
	}

	

    /* replace non ASCII characters from string */
    protected function repalceNonAscii($val) {  
    	//print_r($val);          
       	$val = iconv("UTF-8", "UTF-8//IGNORE", $val);
        return $val;
    }

    /* function  to get product data in array */  
	public function getProductDataInArray($prod_coll) {	
		// die('hi--------------------');
		$objectManager   = \Magento\Framework\App\ObjectManager::getInstance(); 
		$dataHelper = $objectManager->create('\Mec\PriceModifier\Helper\Data');
		try {
			
			$currentDate = strtotime(date("Y-m-d"));
		  	$productAttributes = array();
		  	$resp_arr = array();		  	
		  	foreach ($prod_coll as $prod_inx => $prod) {
		  		try{


		  		$product_tags_Array =array();
		    	$categoryArray = array();
			    $designersArray = array();
			    $occasionArray = array();
			    $genderArray = array();
			    $kidArray = array();
			    $tagsArray = array();
			    $sizeArray = array();
			    $themeArray = array();
			    $colorArray = array();
			    
			    $deliveryTimeArray = array();
			    
			    $price_arr = array();
			    $storeWiseMapArr = array();  
			    $storeWiseCatArr = array();  
			    $editArray = array();
			    $patternsArray = array();
			    $bridalArray = array();
			    $calenderArray = array();
			    $shortDescription = '';
			     $deliveryTimeNewArray = array();
			    
			    $cat_pos_arr = array();
			    $prod_url_arr = array();
			    
			    $prod_id = '';
			    $type = '';
			    $sku = '';
			    
			    $enquire = array();
			    $name = '';
			    $status = '';
			    $isSalable = '';
			    
			    $thumbnailImage = '';
			    $smallImage = '';
			    $visibility = '';    
			    $productDesigner = '';
			    
			    
			    /* load product object */
			    $product = $this->_prod_repo->get($prod['sku']);
			    $prod_id = $product->getId();
			  

			    /*avaialable stores*/
			   $storeIds = $product->getStoreIds();
			    
			    /*categories stuff*/
			    $cate_ids = $product->getCategoryIds();	

			    if(in_array(5593, $cate_ids)){
			    	$bestseller = 1;
			    }
			    else{
			    	$bestseller = 0;
			    }

			    foreach ($storeIds as $storeId) {
			    	
			      foreach ($cate_ids as $cat_id) {  
			                    
			        $query = "select cc1.value as name,cce.path,cc2.value as url_path from catalog_category_entity cce left join 
	catalog_category_entity_varchar cc1 ON cce.entity_id=cc1.entity_id and cc1.attribute_id = '45' left join 
	catalog_category_entity_varchar cc2 ON cce.entity_id=cc2.entity_id and cc2.attribute_id = '122' left join 
	catalog_category_entity_int cc3 ON cce.entity_id=cc3.entity_id and cc3.attribute_id = '46' where cce.entity_id = ".$cat_id." and cc3.value = 1";	
			       
			        $c_data = $this->_conn->fetchAll($query);  


			        if (count($c_data) > 0) {		        	
				  /* get catalog product position */
				  $catPosQuery = "SELECT `position` FROM catalog_category_product  WHERE `category_id`='".$cat_id."' AND `product_id`='".$prod_id."'"; 
				  

                    $catPos_data = $this->_conn->fetchAll($catPosQuery); 
                                
                   if (count($catPos_data) > 0) {
                      	//echo "outside\n";
                      	 $cat_pos_arr["cat_position_".$storeId."_".$cat_id] = $catPos_data[0]["position"];
				          /* get store wise category data */
				         $storeWiseMapArr["categories-store-".$storeId][$c_data[0]['path']] = $c_data[0]['url_path'];


				         $storeWiseCatArr["categories-store-".$storeId][$cat_id] = $c_data[0]['name'];
						}
			        }        
			      }  
			    } 
			      
			    
			    if (empty($storeWiseMapArr) && empty($storeWiseCatArr) && $product->getData('type_id') != 'mageworx_giftcards') {
			    	error_log("PROD CAT ERROR :: ".$prod_id);
			      	continue;
			    } 
			  
			     foreach ($storeIds as $st_id) {
			    	
			    	$st_product = $this->_prod_repo->getById($prod_id, false, $st_id);
			    	/* get store wise url */
			    	$prod_url_arr["prod_url_".$st_id] = $st_product->getProductUrl();

			    	$enquire['enquire_'.$st_id] = $st_product->getHideprice();
			    	
			    	
			    	
			    	if($product->getData('type_id') == 'mageworx_giftcards'){
			    		$pPrice = array('actual_price' => 5000,'special_price' => 5000);
                    }else{
				    	$pPrice = $this->getCatalogProductPrice($st_product,$st_id);

                    }
				    
				   
				    $actualPrice = isset($pPrice['actual_price']) ? $pPrice['actual_price'] : 0;
					$specialPrice = isset($pPrice['special_price']) ? $pPrice['special_price'] : '';
					$saleFromDate = isset($pPrice['special_from_date']) ? $pPrice['special_from_date'] : '';
                    $saleToDate = isset($pPrice['special_to_date']) ? $pPrice['special_to_date'] : '';

                    
                    // $usSpecialPrice = isset($pPrice['us_special_price']) ? $pPrice['us_special_price'] : '';
                    // $usActualPrice = isset($pPrice['us_actual_price']) ? $pPrice['us_actual_price'] : '';
                    // $worldSpecialPrice = isset($pPrice['world_special_price']) ? $pPrice['world_special_price'] : '';
                    // $worldActualPrice = isset($pPrice['world_actual_price']) ? $pPrice['world_actual_price'] : '';

					if($actualPrice == ''){
						$actualPrice = 0;
					}

					// if($usActualPrice == ''){
					// 	$usActualPrice = 0;
					// }
					// if($worldActualPrice == ''){
					// 	$worldActualPrice = 0;
					// }

					if($specialPrice < $actualPrice){
						$discount = round(($actualPrice - $specialPrice)*100/$actualPrice);
						$price_arr['actual_price_'.$st_id] = $actualPrice;
			        	$price_arr['special_price_'.$st_id] = $specialPrice;

			        	// $price_arr['us_special_price_'.$st_id] = $usSpecialPrice;
			        	// $price_arr['us_actual_price_'.$st_id] = $usActualPrice;
			        	// $price_arr['world_special_price_'.$st_id] = $worldSpecialPrice;
			        	// $price_arr['world_actual_price_'.$st_id] = $worldActualPrice;


						$discountArr['discount_percent_'.$st_id] = $discount;
						$price_arr['special_from_date_'.$st_id] = $saleFromDate;
                        $price_arr['special_to_date_'.$st_id] = $saleToDate;
					}else{
						$price_arr['actual_price_'.$st_id] = $actualPrice;
			        	$price_arr['special_price_'.$st_id] = $actualPrice;

			        	// $price_arr['us_special_price_'.$st_id] = $usActualPrice;
			        	// $price_arr['us_actual_price_'.$st_id] = $usActualPrice;
			        	// $price_arr['world_special_price_'.$st_id] = $worldActualPrice;
			        	// $price_arr['world_actual_price_'.$st_id] = $worldActualPrice;
			        	$discountArr['discount_percent_'.$st_id] = 0;
						$price_arr['special_from_date_'.$st_id] = '';
                        $price_arr['special_to_date_'.$st_id] = '';
					}
				    

				 
			    }
			    
			    $price_arr['us_special_price_1'] = isset($price_arr['special_price_2']) ? $price_arr['special_price_2'] : 0;
	        	$price_arr['us_actual_price_1'] =  isset($price_arr['actual_price_2']) ? $price_arr['actual_price_2'] : 0;
	        	$price_arr['world_special_price_1'] = isset($price_arr['special_price_3']) ? $price_arr['special_price_2'] : 0;;
	        	$price_arr['world_actual_price_1'] = isset($price_arr['actual_price_3']) ? $price_arr['actual_price_2'] : 0;


			  	/*get Designer*/
			  	$designer = $product->getResource()->getAttribute('designer');
			    if ($designer) {
			      $designerName = $designer->getFrontend()->getValue($product);        
			      $designerOptionId = $designer->getSource()->getOptionId($designerName);
			      if (!empty($designerName) && !empty($designerOptionId)) {
			        $designersArray[$designerOptionId] = $designerName;    
			      }
			    }
			   //code for product Tags start:
			    $product_tag_query= "SELECT lpp.product_id ,lpt.tag_title AS 'tags',lpt.identifier from lof_producttags_product lpp left join lof_producttags_tag lpt ON lpp.tag_id=lpt.tag_id where status=1 and lpp.product_id = $prod_id";
 //echo $product_tag_query;die;
			    $product_tag_data = $this->_conn->fetchAll($product_tag_query);
			    if(!empty($product_tag_data))
			    {
			    	foreach($product_tag_data as $prod)
			    	{
			    		$tag_title = $prod['tags'];
                        $tag_identifier = $prod['identifier'];
                        $product_id= $prod['product_id'];
                        $product_tags_Array[$tag_identifier] = $tag_title;

			    	}

			    }
			    /*get occasion*/
			    $occasion = $product->getResource()->getAttribute('occasions');

			    if ($occasion) {
			      $occasionNames = explode(',', $occasion->getFrontend()->getValue($product));        
			      foreach ($occasionNames as $occasionName) {
			      	
			      	$occasionName = trim($occasionName);
			        $occasionOptionId = $occasion->getSource()->getOptionId($occasionName);
			        
			        if (!empty($occasionName) && !empty($occasionOptionId)) {
			        	//echo "occasion--".$occasionName;
			          $occasionArray[$occasionOptionId] = $occasionName;  
			        }
			      }  
			    }
			    // gender code by mithilesh
			    $gender = $product->getResource()->getAttribute('men_women');

			    if ($gender) {
			      $genderNames = explode(',', $gender->getFrontend()->getValue($product));        
			      foreach ($genderNames as $genderName) {
			      	// print_r($genderNames);die;
			      	$genderName = trim($genderName);
			        $genderOptionId = $gender->getSource()->getOptionId($genderName);
			        if (!empty($genderName) && !empty($genderOptionId)) {
			        	//echo "occasion--".$occasionName;
			          $genderArray[$genderOptionId] = $genderName;  
			        }
			      }  
			    }


			    			    // kid code by mithilesh
			    $kid = $product->getResource()->getAttribute('kids');

			    if ($kid) {
			      $kidNames = explode(',', $kid->getFrontend()->getValue($product));        
			      foreach ($kidNames as $kidName) {
			      	//print_r($kidNames);die;
			      	$kidName = trim($kidName);
			        $kidOptionId = $kid->getSource()->getOptionId($kidName);
			        if (!empty($kidName) && !empty($kidOptionId)) {
			        	//echo "occasion--".$occasionName;
			          $kidArray[$kidOptionId] = $kidName;  
			        }
			      }  
			    }



			   // get stock status 
                 // $stock_statuss = array();
                 //  $stock_statuss = $product->getData('quantity_and_stock_status');
                  // aniket's code for getting child qty and setting it 
                  $allOutOfStock = 'out';
                  $config = $objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
                  $childrenIds = $config->getChildrenIds($prod_id); 
			foreach ($childrenIds as $childIds) {
    foreach ($childIds as $childId) {
    	$productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
        $childProduct = $productRepository->getById($childId, false, 1);
        if ($childProduct) {
            $quantityAndStockStatus = $childProduct->getData('quantity_and_stock_status');
            if ($quantityAndStockStatus['qty'] > 0 || $quantityAndStockStatus['is_in_stock'] == 1) {
                $allOutOfStock = 'in';
                break 2; // break both loops
            }
        }
    }
}

if($product->getData('type_id') == 'mageworx_giftcards'){
	$allOutOfStock = 'in';
}
// echo $allOutOfStock.' ::allOutOfStock';
// echo $prod_id.' ::prod_id';
			    if($allOutOfStock){
			    	if($allOutOfStock == 'out'){
			    		$availability_id = 1;
			        	$availability_label = 'Out of stock';
			        }else{
			        	$availability_id = 0;
			        	$availability_label = 'Is in stock';
			        }
			    }
			  //  echo $availability_label.' :: label';die;
                /*get tag*/
			    $tags = $product->getResource()->getAttribute('tags');

			    if ($tags) {
			      $tagsNames = explode(',', $tags->getFrontend()->getValue($product));        
			      foreach ($tagsNames as $tagsName) {
			      	
			      	$tagsName = trim($tagsName);
			        $tagsOptionId = $tags->getSource()->getOptionId($tagsName);
			        
			        if (!empty($tagsName) && !empty($tagsOptionId)) {
			        	//echo "occasion--".$occasionName;
			          $tagsArray[$tagsOptionId] = $tagsName;  
			        }
			      }  
			    }

			      /*get theme*/
			    $theme = $product->getResource()->getAttribute('theme');

			    if ($theme) {
			      $themeNames = explode(',', $theme->getFrontend()->getValue($product));        
			      foreach ($themeNames as $themeName) {
			      	
			      	$themeName = trim($themeName);
			        $themeOptionId = $theme->getSource()->getOptionId($themeName);
			        
			        if (!empty($themeName) && !empty($themeOptionId)) {
			        	//echo "occasion--".$occasionName;
			          $themeArray[$themeOptionId] = $themeName;  
			        }
			      }  
			    }
			   

			    /*get size*/
			    /*get size*/
			    if ($product->getData('type_id') == "configurable") {
			    	
			      	$childProductIds = $product->getTypeInstance()->getUsedProductIds($product);
			      	foreach ($childProductIds as $childProductId) {
			        	$childProduct = $this->_prod_repo->getById($childProductId);
			        	if ($this->checkIsInStock($childProduct) == 1 && $childProduct->getStatus() == 1) {
			          		$sizesource = $childProduct->getResource()->getAttribute('size');
					        $sizeVarient = $sizesource->getFrontend()->getValue($childProduct);
					        $sizeOptionId = $sizesource->getSource()->getOptionId($sizeVarient);
					        if (!empty($sizeVarient) && !empty($sizeOptionId)) {
					            $sizeArray[$sizeOptionId] = $sizeVarient;  
					        }



					        $shippedInNew = $childProduct->getResource()->getAttribute('deliverytimes');
						   	if ($shippedInNew) {
						      	$shippedInDuration = trim($shippedInNew->getFrontend()->getValue($childProduct));
						     	$shippedInOptionId = $shippedInNew->getSource()->getOptionId($shippedInDuration);
						      	if (!empty($shippedInDuration)) {
						      		$deliveryTimeNewArray[$shippedInOptionId] = $shippedInDuration;
						      	}         
						    }

						}



			      	}
			    } else if ($product->getData('type_id') == "simple") {
			      if ($this->checkIsInStock($product) == 1) {
			        $sizesource = $product->getResource()->getAttribute('size');
			        $sizeVarient = $sizesource->getFrontend()->getValue($product);
			        $sizeOptionId = $sizesource->getSource()->getOptionId($sizeVarient);
			        if (!empty($sizeVarient) && !empty($sizeOptionId)) {
			          $sizeArray[$sizeOptionId] = $sizeVarient;         
			        }

			        
				    $shippedInNew = $product->getResource()->getAttribute('deliverytimes');
				   	if ($shippedInNew) {
				      	$shippedInDuration = trim($shippedInNew->getFrontend()->getValue($product));
				      	$shippedInOptionId = $shippedInNew->getSource()->getOptionId($shippedInDuration);
				      	if (!empty($shippedInDuration)) {
				      		$deliveryTimeNewArray[$shippedInOptionId] = $shippedInDuration;
				      	}        
				    }

			      }


			    }

			    

			    $color = $product->getResource()->getAttribute('color_multiple');
			    if ($color) {
			      $colorNames = explode(',', $color->getFrontend()->getValue($product));        
			      foreach ($colorNames as $colorName) {
			      	
			      	$colorName = trim($colorName);
			        $colorOptionId = $color->getSource()->getOptionId($colorName);
			        
			        if (!empty($colorName) && !empty($colorOptionId)) {
			        	//echo "occasion--".$occasionName;
			          $colorArray[$colorOptionId] = $colorName;  
			        }
			      }  
			    }

			   


			    $edits = $product->getResource()->getAttribute('edits');
			    if ($edits) {
			      $editsNames = explode(',', $edits->getFrontend()->getValue($product));        
			      foreach ($editsNames as $editsName) {
			      	
			      	$editsName = trim($editsName);
			        $editsOptionId = $edits->getSource()->getOptionId($editsName);
			        
			        if (!empty($editsName) && !empty($editsOptionId)) {
			        	//echo "occasion--".$occasionName;
			          $editArray[$editsOptionId] = $editsName;  
			        }
			      }  
			    }


			    $bridal = $product->getResource()->getAttribute('bridal');
			    if ($bridal) {
			      $bridalNames = explode(',', $bridal->getFrontend()->getValue($product));        
			      foreach ($bridalNames as $bridalName) {
			      	
			      	$bridalName = trim($bridalName);
			        $bridalOptionId = $bridal->getSource()->getOptionId($bridalName);
			        
			        if (!empty($bridalName) && !empty($bridalOptionId)) {
			        	//echo "occasion--".$occasionName;
			          $bridalArray[$bridalOptionId] = $bridalName;  
			        }
			      }  
			    }
			   
			   	$calender = $product->getResource()->getAttribute('calender');
			    if ($calender) {
			      $calenderNames = explode(',', $calender->getFrontend()->getValue($product));        
			      foreach ($calenderNames as $calenderName) {
			      	
			      	$calenderName = trim($calenderName);
			        $calenderOptionId = $calender->getSource()->getOptionId($calenderName);
			        
			        if (!empty($calenderName) && !empty($calenderOptionId)) {
			        	//echo "occasion--".$occasionName;
			          $calenderArray[$calenderOptionId] = $calenderName;  
			        }
			      }  
			    }


			    $patterns = $product->getResource()->getAttribute('patterns');
			    if ($patterns) {
			      $patternsNames = explode(',', $patterns->getFrontend()->getValue($product));        
			      foreach ($patternsNames as $patternsName) {
			      	
			      	$patternsName = trim($patternsName);
			        $patternsOptionId = $patterns->getSource()->getOptionId($patternsName);
			        
			        if (!empty($patternsName) && !empty($patternsOptionId)) {
			        	//echo "occasion--".$occasionName;
			          $patternsArray[$patternsOptionId] = $patternsName;  
			        }
			      }  
			    }

			   
			     
			    $shippedIn = $product->getResource()->getAttribute('deliverytimes');
			    if ($shippedIn) {
			      $shippedInName = $shippedIn->getFrontend()->getValue($product);        
			      $shippedInOptionId = $shippedIn->getSource()->getOptionId($shippedInName);
			      if (!empty($shippedInName) && !empty($shippedInOptionId)) {
			        $deliveryTimeArray[$shippedInOptionId] = $shippedInName;    
			      }
			    }

			    //$theme = $product->getTheme();
 
			    

			    /*get product other data*/		    
			    $type = $product->getData('type_id');
			    $sku = $product->getData('sku');
			   
			    
			   
			   
			    $name = $product->getData('name');
			    $shortDescription = $product->getData('short_description');
			    if($product->getData('type_id') == 'mageworx_giftcards'){
					$shortDescription = 'Gift Card';
				}
			    $status = $product->getData('status');  
			    $isSalable = $product->getIsSalable();  
			    if($isSalable != 1){
			    	$isSalable = 0;
			    }   
			        
			    //$isSalable = $this->checkIsInStock($product);
			    //echo $product->getId()."isSalable::". $isSalable;

			    if(in_array(1383,$cate_ids)){
			    	$smallImage = $this->_img_helper->init($product, 'product_celebrity_image')->setImageFile($product->getCelebrityImage())->resize(540,810)->getUrl();
			    	
			    }else{
			    	$smallImage = $this->_img_helper->init($product, 'product_small_image')
			    	->setImageFile($product->getSmallImage())
			    	->resize(540,810)
			    	->getUrl();
			    }
			    

			    $thumbnailImage = $this->_img_helper->init($product, 'product_thumbnail_image')
			    ->setImageFile($product->getThumbnail())
			    ->resize(540,810)
			    ->getUrl(); 

			   // error_log("smallimage::".$smallImage." thumbnail::".$thumbnailImage);  
			    $visibility = $product->getData('visibility');         
			    $productDesigner = $product->getResource()->getAttribute('designer')->getFrontend()->getValue($product);
			   
			    $productAttributes[$prod_inx]["designers"] = $designersArray;
                $productAttributes[$prod_inx]["product_tags"] = $product_tags_Array;
			    $productAttributes[$prod_inx]["occasions"] = $occasionArray;
			    $productAttributes[$prod_inx]["genders"] = $genderArray;
			    $productAttributes[$prod_inx]["kids"] = $kidArray;
			    $productAttributes[$prod_inx]["tags"] = $tagsArray;
			    $productAttributes[$prod_inx]["themes"] = $themeArray;
			    $productAttributes[$prod_inx]["sizes"] = $sizeArray;
			    //$productAttributes[$prod_inx]["theme_id"] = $theme;
			    $productAttributes[$prod_inx]["colors"] = $colorArray;
			    $productAttributes[$prod_inx]["edit"] = $editArray;
			    $productAttributes[$prod_inx]["calender"] = $calenderArray;
			    $productAttributes[$prod_inx]["patterns"] = $patternsArray;
			    $productAttributes[$prod_inx]["bridal"] = $bridalArray;
			    $productAttributes[$prod_inx]["delivery_time"] = $deliveryTimeArray;

			    //print_r($deliveryTimeNewArray);die;  
			    $productAttributes[$prod_inx]["child_delivery_time"] = $deliveryTimeNewArray;
			 
			   //print_r($cate_ids);die;
			    $productAttributes[$prod_inx]["prices"] = $price_arr;    
			    //print_r($storeWiseCatArr);
			    $productAttributes[$prod_inx]["enquire"] = $enquire;

			    
			     $productAttributes[$prod_inx]["categories"] = $storeWiseCatArr;
			    $productAttributes[$prod_inx]["cat_position"] = $cat_pos_arr;
			    $productAttributes[$prod_inx]["categories_map"] = $storeWiseMapArr;
			    $productAttributes[$prod_inx]["prod_urls"] = $prod_url_arr;
			    /* other data array*/
			    $productAttributes[$prod_inx]["prod_en_id"] = $prod_id;  
			    $productAttributes[$prod_inx]["prod_en_id_int"] = $prod_id;   
			    $productAttributes[$prod_inx]["prod_type"] = $type;
			    $productAttributes[$prod_inx]["prod_sku"] = $sku;
			    $productAttributes[$prod_inx]["prod_name"] = $name;
			    $productAttributes[$prod_inx]["short_desc"] = $shortDescription;
			    $productAttributes[$prod_inx]["prod_status"] = $status;
			    $productAttributes[$prod_inx]["prod_is_salable"] = $isSalable;
			    $productAttributes[$prod_inx]["prod_small_img"] = $smallImage;
			    $productAttributes[$prod_inx]["prod_thumb_img"] = $thumbnailImage;
			    $productAttributes[$prod_inx]["prod_visibility"] = $visibility;
                 $productAttributes[$prod_inx]["prod_availability_label"] = $availability_label;
			    $productAttributes[$prod_inx]["prod_availability_id"] = $availability_id;     
			    $productAttributes[$prod_inx]["prod_design"] = $productDesigner;
			    $productAttributes[$prod_inx]["prod_desc"] = $product->getData('description');
			    $productAttributes[$prod_inx]["bestseller_cat_position"] = $bestseller;
				$product_position_query = "SELECT position FROM products_custom_position WHERE product_id = ? ";
    			$product_position = $this->_conn->fetchOne($product_position_query,[$prod_id]);

			    $productAttributes[$prod_inx]["prod_designer_position"] = isset($product_position) ? $product_position:0; 

			    
			    }catch(\Exception $e){
			    	echo "Error1::".$e->getMessage();continue;
		  		}     
		  	}		  				  	 
		  	$resp_arr["error"] = 0;
		  	$resp_arr["prod_data"] = $productAttributes;  
		  	  		  			  	
		} catch (\Exception $e) {
			$resp_arr["error"] = 1;
			$resp_arr["prod_data"] = $e->getMessage();
		}
		return $resp_arr;	 				  	
	}

	


	/* check product is in stock or not */
	protected function checkIsInStock($prod) {	  
		$query = "select is_in_stock from cataloginventory_stock_item where product_id = ".$prod->getId()." and website_id = 0";
		
	  	$isInStock = $this->_conn->fetchOne($query);
	  	return $isInStock;
	}

	// public function getCatalogProductPrice($product,$storeId,$dataHelper) {
		
 //        $finalPriceArray = array();
 //        if ($product->getTypeId() == "simple") {
 //        	$product_id = $product->getId();
 //            $currentDate = strtotime(date("Y-m-d"));
 //            if($product->getSpecialPrice() != NULL && ($currentDate >= strtotime($product->getSpecialFromDate()) && ($currentDate <= strtotime($product->getSpecialToDate()) || strtotime($product->getSpecialToDate()) == '' ))) {
 //                    $specialPrice = $product->getSpecialPrice();
 //                    $actualPrice = $product->getPrice();
 //                    $finalPriceArray['special_price'] = $specialPrice;
 //                    $finalPriceArray['actual_price'] = $actualPrice;
 //                    $finalPriceArray['special_from_date'] = $product->getSpecialFromDate();
 //                	$finalPriceArray['special_to_date'] = $product->getSpecialToDate();

 //                	$us_price_rate = $dataHelper->getUsPriceRate($product_id);
 //                	if(!empty($us_price_rate)){
	// 		            $finalPriceArray['us_special_price'] = $specialPrice + ($us_price_rate*$specialPrice);
	// 		            $finalPriceArray['us_actual_price'] = $actualPrice + ($us_price_rate*$actualPrice);
	// 		        }else{
	// 		        	$finalPriceArray['us_special_price'] = $specialPrice;
	// 		            $finalPriceArray['us_actual_price'] = $actualPrice;
	// 		        } 



	// 		        $world_price_rate = $dataHelper->getWorldPriceRate($product_id);
 //                	if(!empty($us_price_rate)){
	// 		            $finalPriceArray['world_special_price'] = $specialPrice + ($world_price_rate*$specialPrice);
	// 		            $finalPriceArray['world_actual_price'] = $actualPrice + ($world_price_rate*$actualPrice);
	// 		        }else{
	// 		        	$finalPriceArray['world_special_price'] = $specialPrice;
	// 		            $finalPriceArray['world_actual_price'] = $actualPrice;
	// 		        }

 //            } else if($product->getPrice() != NULL) {
 //                $specialPrice = $product->getPrice();
 //                $actualPrice = $product->getPrice();
 //                $finalPriceArray['special_price'] = $specialPrice; 
 //                $finalPriceArray['actual_price'] = $actualPrice;
 //                $finalPriceArray['special_from_date'] = '';
 //                $finalPriceArray['special_to_date'] = '';


 //                $us_price_rate = $dataHelper->getUsPriceRate($product_id);
 //            	if(!empty($us_price_rate)){
	// 	            $finalPriceArray['us_special_price'] = $specialPrice + ($us_price_rate*$specialPrice);
	// 	            $finalPriceArray['us_actual_price'] = $actualPrice + ($us_price_rate*$actualPrice);
	// 	        }else{
	// 	        	$finalPriceArray['us_special_price'] = $specialPrice;
	// 	            $finalPriceArray['us_actual_price'] = $actualPrice;
	// 	        } 



	// 	        $world_price_rate = $dataHelper->getWorldPriceRate($product_id);
 //            	if(!empty($us_price_rate)){
	// 	            $finalPriceArray['world_special_price'] = $specialPrice + ($world_price_rate*$specialPrice);
	// 	            $finalPriceArray['world_actual_price'] = $actualPrice + ($world_price_rate*$actualPrice);
	// 	        }else{
	// 	        	$finalPriceArray['world_special_price'] = $specialPrice;
	// 	            $finalPriceArray['world_actual_price'] = $actualPrice;
	// 	        }


 //            } else {
 //                $finalPriceArray['special_price'] = 0; 
 //                $finalPriceArray['actual_price'] = 0;
 //                $finalPriceArray['special_from_date'] = '';
 //                $finalPriceArray['special_to_date'] = '';
 //                $finalPriceArray['world_special_price'] = 0;
	// 	        $finalPriceArray['world_actual_price'] = 0;
	// 	        $finalPriceArray['us_special_price'] = 0;
	// 	        $finalPriceArray['us_actual_price'] = 0;


 //            }
 //        } else if ($product->getTypeId() == "configurable") {

 //            $finalPriceArray = $this->getConfigurableProductPrice($product,$storeId,$dataHelper);
 //        }
       

 //        return $finalPriceArray;
 //    }
		
    public function getCatalogProductPrice($product,$storeId) {
        $finalPriceArray = array();
        //echo "\nGCatPP";
        if ($product->getTypeId() == "simple") {
        	//echo "\nGCatPP simple";
            $currentDate = strtotime(date("Y-m-d"));
            if($product->getSpecialPrice() != NULL && ($currentDate >= strtotime($product->getSpecialFromDate()) && ($currentDate <= strtotime($product->getSpecialToDate()) || strtotime($product->getSpecialToDate()) == '' ))) {
            	//echo "\nGCatPP simple special price";
                    $specialPrice = $product->getSpecialPrice();
                    $actualPrice = $product->getPrice();
                    $finalPriceArray['special_price'] = $specialPrice;
                    $finalPriceArray['actual_price'] = $actualPrice;
            } else if($product->getPrice() != NULL) {
            	//echo "\nGCatPP simple no special price";
                $specialPrice = $product->getPrice();
                $actualPrice = $product->getPrice();
                $finalPriceArray['special_price'] = $specialPrice; 
                $finalPriceArray['actual_price'] = $actualPrice;
            } else {
            	//echo "\nGCatPP simple no price";
                $finalPriceArray['special_price'] = 0; 
                $finalPriceArray['actual_price'] = 0;
            }
        } else if ($product->getTypeId() == "configurable") {
        	//echo "\nGCatPP configurable";
            $finalPriceArray = $this->getConfigurableProductPrice($product,$storeId);
        }
       

        return $finalPriceArray;
    }

    public function getConfigurableProductPrice($product,$storeId) {
    	//echo "\nGConfPP";
        $lowestPrice = array();
        $priceArray = array();
        $productRepository = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Catalog\Model\ProductRepository');
        $childrens = $product->getTypeInstance()->getUsedProductIds($product);
        $currentDate = strtotime(date("Y-m-d"));
        if(count($childrens)){
            foreach ($childrens as $index => $child) {
                $childProduct = $productRepository->getById($child,false,$storeId);
                if($childProduct->getSpecialPrice() != NULL && ($currentDate >= strtotime($childProduct->getSpecialFromDate()) &&   ($currentDate <= strtotime($childProduct->getSpecialToDate()) || strtotime($childProduct->getSpecialToDate()) == '' ) )){
                	//echo "\nGConfPP configurable special price";
                    $specialPrice = $childProduct->getSpecialPrice();
                    $actualPrice = $childProduct->getPrice();
                    $priceArray[$index]['special_price'] = $specialPrice;
                    $priceArray[$index]['actual_price'] = $actualPrice;
                    array_push($lowestPrice, $specialPrice);
                }else if($childProduct->getPrice() != NULL) {
                	//echo "\nGConfPP configurable no special price :: ".$childProduct->getPrice();
                    $price = $childProduct->getPrice();
                    $priceArray[$index]['special_price'] = $price;
                    $priceArray[$index]['actual_price'] = $price;
                    array_push($lowestPrice, $price);
                } else {
                	//echo "\nGConfPP configurable no price";
                    $priceArray[$index]['special_price'] = 0;
                    $priceArray[$index]['actual_price'] = 0;
                    array_push($lowestPrice, 0);
                } 
            }
        }else{
        	//echo "\nGConfPP configurable no children";
            if($product->getSpecialPrice() != NULL && ($currentDate >= strtotime($product->getSpecialFromDate()) && $currentDate <= strtotime($product->getSpecialToDate()))){
            	//echo "\nGConfPP configurable no children specialPrice";
                $specialPrice = $product->getSpecialPrice();
                $actualPrice = $product->getPrice();
                $priceArray[0]['special_price'] = $specialPrice;
                $priceArray[0]['actual_price'] = $actualPrice;
                array_push($lowestPrice, $specialPrice);
            }else if($product->getPrice() != NULL) {
            	//echo "\nGConfPP configurable no children actualPrice";
                $price = $product->getPrice();
                $priceArray[0]['special_price'] = $price;
                $priceArray[0]['actual_price'] = $price;
                array_push($lowestPrice, $price);
            }
        }

        $newPriceArray = array();
        if(empty($lowestPrice)){
            $minimumPrice = 0;
        }else{
            $minimumPrice = min($lowestPrice);
        }

        foreach ($priceArray as $index => $price) {
            if ($price['special_price'] == $minimumPrice) {
                $specialPrice = $price['special_price'];
                $actualPrice = $price['actual_price'];
                $newPriceArray['special_price'] = $specialPrice;
                $newPriceArray['actual_price'] = $actualPrice;
                break;
            }
                }
                
        return $newPriceArray;
                }

                
    // public function getConfigurableProductPrice($product,$storeId,$dataHelper) {
    //     $lowestPrice = array();
    //     $priceArray = array();
    //     // $productRepository = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Catalog\Model\ProductRepository');
    //     // echo "product id ::".$product->getId()."\n";
    //     //$childrens = $product->getTypeInstance()->getUsedProductIds($product);
    //     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    //     $config = $objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
    //     $childre = $config->getChildrenIds($product->getId());
    //     foreach($childre as $children){
    //     	//print_r($children);
    //     	$childrens = $children;
    //     }
    //     $currentDate = strtotime(date("Y-m-d"));
    //     $world_price_rate = 0;
    //     $us_price_rate = 0;
    //     if(count($childrens)){

    //         foreach ($childrens as $index => $child) {
    //         	$childProduct = array();
    //         	if(empty($world_price_rate)){
    //         		$world_price_rate = $dataHelper->getWorldPriceRate($child);
    //         	}
    //         	if(empty($us_price_rate)){
    //         		$us_price_rate = $dataHelper->getUsPriceRate($child);
    //         	}
            	
		
    //             $childProduct =$this->_prod_repo->getById($child);
    //             if($childProduct->getSpecialPrice() != NULL && ($currentDate >= strtotime($childProduct->getSpecialFromDate()) &&   ($currentDate <= strtotime($childProduct->getSpecialToDate()) || strtotime($childProduct->getSpecialToDate()) == '' ) )){
    //             	//echo '--------if-------';
    //                 $specialPrice = $childProduct->getSpecialPrice();
    //                 $actualPrice = $childProduct->getPrice();
    //                 $priceArray[$index]['special_from_date'] = $childProduct->getSpecialFromDate();
    //                 $priceArray[$index]['special_to_date'] = $childProduct->getSpecialToDate();
    //                 $priceArray[$index]['special_price'] = $specialPrice;
    //                 $priceArray[$index]['actual_price'] = $actualPrice;
    //                 array_push($lowestPrice, $specialPrice);
                    
    //             }else if($childProduct->getPrice() != NULL) {
    //                 $price = $childProduct->getPrice();
    //             	//echo '--------else if-------'.$childProduct->getId()."====".$childProduct->getPrice()."\n";
    //                 $priceArray[$index]['special_price'] = $price;
    //                 $priceArray[$index]['actual_price'] = $price;
    //                 $priceArray[$index]['special_from_date'] = '';
    //                 $priceArray[$index]['special_to_date'] = '';
    //                 array_push($lowestPrice, $price);
    //             } else {
    //             	//echo '--------else-------';
    //                 $priceArray[$index]['special_price'] = 0;
    //                 $priceArray[$index]['actual_price'] = 0;
    //                 $priceArray[$index]['special_from_date'] = '';
    //                 $priceArray[$index]['special_to_date'] = '';
    //                 array_push($lowestPrice, 0);
    //             } 
    //         }
    //     }else{
    //     	$parentId = $product->getId();

    //     	if(empty($world_price_rate)){
    //     		$world_price_rate = $dataHelper->getWorldPriceRate($parentId);
    //     	}
    //     	if(empty($us_price_rate)){
    //     		$us_price_rate = $dataHelper->getUsPriceRate($parentId);
    //     	}
    //         if($product->getSpecialPrice() != NULL && ($currentDate >= strtotime($product->getSpecialFromDate()) && $currentDate <= strtotime($product->getSpecialToDate()))){
    //             $specialPrice = $product->getSpecialPrice();
    //             $actualPrice = $product->getPrice();
    //             $priceArray[0]['special_price'] = $specialPrice;
    //             $priceArray[0]['actual_price'] = $actualPrice;
    //             $priceArray[0]['special_from_date'] = $product->getSpecialFromDate();
    //             $priceArray[0]['special_to_date'] = $product->getSpecialToDate();
    //             array_push($lowestPrice, $specialPrice);
    //         }else if($product->getPrice() != NULL) {
    //             $price = $product->getPrice();
    //             $priceArray[0]['special_price'] = $price;
    //             $priceArray[0]['actual_price'] = $price;
    //             $priceArray[0]['special_from_date'] = '';
    //             $priceArray[0]['special_to_date'] = '';
    //             array_push($lowestPrice, $price);
    //         }
    //     }

    //     $newPriceArray = array();
    //     if(empty($lowestPrice)){
    //         $minimumPrice = 0;
    //     }else{
    //         $minimumPrice = min($lowestPrice);
    //     }

    //     foreach ($priceArray as $index => $price) {
    //         if ($price['special_price'] == $minimumPrice) {
    //             $specialPrice = $price['special_price'];
    //             $actualPrice = $price['actual_price'];
    //             $newPriceArray['special_price'] = $specialPrice;
    //             $newPriceArray['actual_price'] = $actualPrice;
    //             $newPriceArray['special_from_date'] = isset($price['special_from_date']) ? $price['special_from_date'] : '';
    //             $newPriceArray['special_to_date'] = isset($price['special_to_date']) ? $price['special_to_date'] : '';
    //             if(!empty($us_price_rate)){
    //             	$newPriceArray['us_special_price'] = $specialPrice + ($us_price_rate*$specialPrice);
    //             	$newPriceArray['us_actual_price'] = $actualPrice + ($us_price_rate*$actualPrice);
    //             }else{
    //             	$newPriceArray['us_special_price'] = $specialPrice;
    //             	$newPriceArray['us_actual_price'] = $actualPrice;
    //             }
                
    //             if(!empty($world_price_rate)){
    //             	$newPriceArray['world_special_price'] = $specialPrice + ($world_price_rate*$specialPrice);
    //             	$newPriceArray['world_actual_price'] = $actualPrice + ($world_price_rate*$actualPrice);
    //             }else{
    //             	$newPriceArray['world_special_price'] = $specialPrice;
    //             	$newPriceArray['world_actual_price'] = $actualPrice;
    //             }

                
    //             break;
    //         }
    //     }

        //return $newPriceArray;
    //}

    public function getCatDataInArray($catArr){

		$catAttributes = array();
		$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
		$catObj = $objectManager->create('Magento\Catalog\Model\CategoryRepository');
		try{
			foreach ($catArr as $catKey => $catVal) {
				$catId = $catVal['entity_id'];
				$category = $catObj->get($catId);
				echo "status::".$category->getIsActive()."\n";
				if($catId == 1 || $catId == 2 || $catId == 1371 || !$category->getIsActive()){
					continue;
				}
				
			    $parentIds = $category->getParentIds();
			    $pageType = '';
			    $catUrl =  $category->getUrl();
			    $catAttributes[$catId]['cat_name'] = $category->getName();
				$catAttributes[$catId]['cat_url'] = $catUrl;
				$catAttributes[$catId]['url_path'] = $category->getUrlPath();
				$catAttributes[$catId]["cat_en_id"] = $catId;
			    $catAttributes[$catId]["cat_en_id_int"] = $catId;
			    $catAttributes[$catId]["cat_parent_id"] = $category->getParentId();
			    $catAttributes[$catId]["cat_level"] = $category->getLevel();
			    $catAttributes[$catId]["all_parents_id"] = $parentIds;
			    $catAttributes[$catId]["page_type"] = $pageType;
			    $catAttributes[$catId]["cat_img"] = $category->getImageUrl();
			    $catAttributes[$catId]["cat_desc"] = $category->getDescription();
			    $catAttributes[$catId]["cat_url_key"] = $category->getUrlKey();
			    $catAttributes[$catId]["cat_has_child"] = $category->hasChildren();
			    $catAttributes[$catId]["cat_path"] = $category->getPath();
			    $catAttributes[$catId]["meta_title"] = $category->getMetaTitle();
			    $catAttributes[$catId]["meta_description"] = $category->getMetaDescription();
			    $catAttributes[$catId]["meta_keywords"] = $category->getMetaKeywords();
			    if(!empty($category->getParentId()) && $category->getParentId() != 1371){
			    	$parentCategory = $catObj->get($category->getParentId());
			    	$catAttributes[$catId]["parent_cat_name"] = $parentCategory->getName();
			    	$catAttributes[$catId]["parent_cat_url"] = $parentCategory->getUrl();
			    }
			   
			}
		  	$resp_arr["error"] = 0;
		  	$resp_arr["cat_data"] = $catAttributes;    		  			  	
		} catch (\Exception $e) {
			$resp_arr["error"] = 1;
			$resp_arr["cat_data"] = $e->getMessage();
		}
		// echo "arry::";
		// print_r($resp_arr);die;
		return $resp_arr;
	}


	public function generateCatXMLFormatData($catArr = array()) {  
// print_r($catArr);die;
    	$xml_data = "";
    	if (!empty($catArr)) {
    		$xml_data .= "<add>\n";    		
    		foreach ($catArr as $index => $cat) {
    			$xml_data .= "\t<doc>\n";

    			foreach ($cat as $key => $value) {
    				 // print_r($key);
    				/*generate designer fields in xml*/
    				// echo "key::".$key;
    				if($key == "all_parents_id" && !empty($value)){
    					foreach ($value as $all_parents_id => $parent_categories) {
	    					var_dump($parent_categories);
	    					$xml_data .= "\t<field name='all_parents_id'><![CDATA[".$this->repalceNonAscii($parent_categories)."]]></field>\n";

	    				}

    				}
    				if (!empty($value)) {
    					// echo "insetted foor cat";
    					if($key == "all_parents_id"){
    						continue;
    					}
    					$xml_data .= "\t<field name='".$key."'><![CDATA[".$this->repalceNonAscii($value)."]]></field>\n";
    				}
    				
    				
    			}
    			// die;
    			$xml_data .= "\t</doc>\n";
    		}
    		$xml_data .= "</add>";
    	} else {
    		$xml_data .= "<add>\n";
    		$xml_data .= "\t<doc></doc>\n";
    		$xml_data .= "</add>";
    	}   




      	// echo "data------------\n".$xml_data;die;
    	return $xml_data;

    	 
    }
     
}

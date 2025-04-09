<?php 
namespace Fermion\Pagelayout\Model\Listing;
use Magento\Framework\App\ObjectManager;

class ListInfo extends \Magento\Framework\Model\AbstractModel {
	protected $_storeManager;	
	protected $_solrHelper;
	protected $_prod_help;
	protected $_catRepo;	
	protected $_curr_inter;

	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,		
		\Magento\Catalog\Model\CategoryRepository $categoryRepository,
		\Fermion\Pagelayout\Helper\SolrHelper $solrHelper,
		//\Fermion\Pagelayout\Helper\ProductData $pData,
		\Magento\Framework\Pricing\PriceCurrencyInterface $currInterface
	) {
		$this->_storeManager = $storeManager;				
		$this->_solrHelper = $solrHelper;
		//$this->_prod_help = $pData;		
		$this->_catRepo = $categoryRepository;	
		$this->_curr_inter = $currInterface;
	}

	public function getSolrProductPrice($id){
		$st_id = $this->getCurrentStoreId();
		$query = 'q=prod_en_id:'.rawurlencode($id)."&fl=actual_price:actual_price_".$st_id.",special_price:special_price_".$st_id;
		$price = $this->_solrHelper->getFilterCollection($query);
		return json_decode($price, 1);
	}
	
	/* get category data */
	public function getCategoryData($catId) {
		$cat_data_arr = [];
		
        $storeId = $this->getCurrentStoreId();
        $cat = $this->_catRepo->get($catId, $storeId);
        $cat_data_arr["cat_name"] = $cat->getName();
        $cat_data_arr["cat_img"] = $cat->getImageUrl();
        $cat_data_arr["cat_desc"] = $cat->getDescription();
        $cat_data_arr["cat_url"] = $cat->getUrl();
        $cat_data_arr["cat_url_key"] = $cat->getUrlKey();
        $cat_data_arr["pare_cat_id"] = $cat->getParentId();
        //$cat_data_arr["cat_bread_crumb"] = $this->getBreadCrumbs($cat);         
        $cat_data_arr["cat_level"] = $cat->getLevel();
        $cat_data_arr["cat_id"] = $catId;  
        $cat_data_arr["cat_has_child"] = $cat->hasChildren();  
        $cat_data_arr["cat_path"] = $cat->getPath();   
        $cat_data_arr["cat_url_path"] = $cat->getUrlPath(); 
        // error_log("********************category meta data****".$cat->getMetaTitle());
        $cat_data_arr["meta_title"] = $cat->getMetaTitle();   
        $cat_data_arr["meta_description"] = $cat->getMetaDescription(); 
        $cat_data_arr["meta_keywords"] = $cat->getMetaKeywords();        
        if(!empty($cat->getParentId())){
	    	$parentCategory = $this->_catRepo->get($cat->getParentId());
	    	$catAttributes[$catId]["parent_cat_name"] = $parentCategory->getName();
	    	$catAttributes[$catId]["parent_cat_url"] = $parentCategory->getUrl();
	    }   
	    // print_r($cat_data_arr);die;     
        return $cat_data_arr; 
	}

	public function getCategoryFilteredDataSolr($catId = null){
		$catData = array();
		if($catId == null){
			return null;
		}
		$query = "q=cat_en_id:".$catId."&fl=cat_name,cat_url,cat_url_path:url_path,cat_id:cat_en_id,pare_cat_id:cat_parent_id,cat_level,all_parents_id,page_type,cat_img,cat_desc,cat_url_key,cat_has_child,cat_path,meta_title,meta_description,meta_keywords,parent_cat_name,parent_cat_url";
		// echo $query;die;
		//error_log("----------Getting Cat Data from SOLR----------");
		$cat_filt_coll = $this->_solrHelper->getCatCollection($query);
		// print_r($cat_filt_coll);die;
		$cat_filt_coll = json_decode($cat_filt_coll, 1);
		if($cat_filt_coll['response']['numFound'] > 0){
			foreach ($cat_filt_coll['response']['docs'] as $catArr => $catArrData) {
				foreach ($catArrData as $key => $value) {
					if (is_array($value)) {
				        $catData[$key] = $value[0];
				    } else {
				        $catData[$key] = $value;
				    }
				}
			}
		}
		return $catData;
	}

	/* get category bread crumbs */
	public function getBreadCrumbs($cat) {
		$cat_bread = [];
		$pare_cats = $cat->getParentCategories();
        foreach ($pare_cats as $p_cat) {
            if ($p_cat->getLevel() != 1) {
                if (trim(strtolower($p_cat->getName())) == "designers") {
                    $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
                    $cat_bread[$baseUrl."all-ogaan-designers"] = $p_cat->getName(); 
                } else {
                    $cat_bread[$p_cat->getUrl()] = $p_cat->getName();
                }
            }
        }
        $cat_bread[$cat->getUrl()] = $cat->getName();
        return $cat_bread;
	}
	// price filter 23-08-2023
	public function convertCurrency($amount) {

        $amount = floatval($amount);

        $storeManager = ObjectManager::getInstance()->get('\Magento\Store\Model\StoreManagerInterface');
        $currencyFactory = ObjectManager::getInstance()->get('\Magento\Directory\Model\CurrencyFactory');

        $currencyCodeFrom = $storeManager->getStore()->getCurrentCurrency()->getCode();
        $currencyCodeTo = $storeManager->getStore()->getBaseCurrency()->getCode();
        if ($currencyCodeFrom == $currencyCodeTo) {
            return $amount;

        }

        $currencyRate = $currencyFactory->create()->load($currencyCodeFrom)->getAnyRate($currencyCodeTo);
        $amount = $amount * $currencyRate;

        return $amount;
    }


	public function getBreadCrumbsListingSchema($catId,$breacrumbData = array()) {
		$storeId = $this->getCurrentStoreId();
		$baseUrl = $this->_storeManager->getStore()->getBaseUrl();
		$cat = $this->_catRepo->get($catId, $storeId);
		// $cat_schema_arr = $this->getBreadCrumbs($cat);
		$cat_schema_arr = $breacrumbData;
        $bread_crumb_arr = array();
        $bread_crumb = array();
        $bread_crumbs =array();
        $cat_schema = array();
        $i = 2;

        	$bread_crumb["@type"] = "ListItem";
            $bread_crumb["position"] = 1;
            $bread_crumb["name"] = "home";
            $bread_crumb["item"] = $baseUrl;
            $bread_crumb_arr[] = $bread_crumb;

		foreach ($cat_schema_arr as $cat_schema_url => $cat_schema_name) {
			$bread_crumb["@type"] = "ListItem";
            $bread_crumb["position"] = $i;
            $bread_crumb["name"] = $cat_schema_name;
            $bread_crumb["item"] = $cat_schema_url;
            $bread_crumb_arr[] = $bread_crumb;
            $i++;
		}
		$bread_crumbs["@context"] = "https://schema.org";
        $bread_crumbs["@type"] = "BreadcrumbList";
        $bread_crumbs["itemListElement"] = $bread_crumb_arr;
        $cat_schema = isset($bread_crumbs) ? $bread_crumbs : '';
		//echo '<pre>';
		// print_r(json_encode($cat_schema));die('die in listing control');
		return $cat_schema;

	}


	

	/* get filtered product collection */
	public function getFilteredData($start, $rows, $cat_data = array(), $app_filter = array(), $coun_code = NULL) {		

		$st_id = $this->getCurrentStoreId();
		//$st_id = 1;
		$cur_cat = isset($cat_data["cat_id"]) ? $cat_data["cat_id"] : '';
		$par_cat = isset($cat_data["pare_cat_id"]) ? $cat_data["pare_cat_id"] : '';
		$cat_level = isset($cat_data["cat_level"]) ? $cat_data["cat_level"] : '';
		$c_has_child = isset($cat_data["cat_has_child"]) ? $cat_data["cat_has_child"] : 0;
		$cat_name = isset($cat_data["cat_name"]) ? $cat_data["cat_name"] : '';
		$cat_url_key = isset($cat_data['cat_url_key']) ? $cat_data['cat_url_key'] : '';
		$cat_path = isset($cat_data['cat_path']) ? $cat_data['cat_path'] : '';
		$cat_url_path = isset($cat_data['cat_url_path']) ? $cat_data['cat_url_path'] : '';			
		
			
		/* query params */
		$query = "q=categories-store-" . $st_id . "_id:" . $cur_cat;
		/* facet params */
		if (isset($app_filter["is_scroll_req"]) && $app_filter["is_scroll_req"] != 1) {
			$query .= "&facet=true&facet.mincount=1&facet.field=designer_token&facet.field=availability_token&facet.field=availability_id&facet.field=availability_name&facet.field=occasion_token&facet.field=patterns_token&facet.field=size_name&facet.field=tags_name&facet.field=bridal_name&facet.field=gender_token&facet.field=kid_token&facet.field=kid_id&facet.field=size_id&facet.field=tags_id&facet.field=bridal_id&facet.field=child_delivery_time&facet.field=color_token&facet.field=a_co_edit_token&facet.field=special_price_".$st_id."&facet.field=size_token&facet.field=tags_token&facet.field=bridal_token&facet.field=child_delivery_time_token&facet.field=theme_token&facet.field=navratri_color_token&facet.limit=500000";

			
			/*get category filter section for 2nd level category*/

			$parentCatId = isset($cat_data['pare_cat_id']) ? $cat_data['pare_cat_id'] : '';
			$query .= "&facet.field=categories-store-" . $st_id . "_token";
				/*filter on facets*/
			if($parentCatId != 1375){
				$query .= "&f.categories-store-".$st_id."_token.facet.contains=".$cat_path;
			}
		}
		
		$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
	//	$country = $objectManager->create('Mec\PriceModifier\Plugin\Magento\Catalog\Model\Product')->getCountryCode();
		// $country = isset($_GET['ci']) ? $_GET['ci'] : 'IN';
		// if(empty($country) || $country == 'IN') {
		//     $specialPriceField = "special_price_".$st_id;
		//     $actualPriceField = "actual_price_".$st_id;
		// }elseif($country == 'US'){
		// 	$specialPriceField = "us_special_price_".$st_id;
		// 	$actualPriceField = "us_actual_price_".$st_id;
		// }else{
		// 	$specialPriceField = "world_special_price_".$st_id;
		// 	$actualPriceField = "world_actual_price_".$st_id;
		// }
		$specialPriceField = "special_price_".$st_id;
		$actualPriceField = "actual_price_".$st_id;
		// if($cur_cat != 2876){
		// 	$specialPriceField = "special_price_".$st_id;
		//     $actualPriceField = "actual_price_".$st_id;
		// }

		/* field list params */
		$query .= "&fl=actual_price:".$actualPriceField.",special_price:".$specialPriceField.",categories-store-".$st_id."_id,prod_en_id,prod_type,prod_sku,prod_plp_flag,prod_sd_flag,prod_sdm_flag,prod_rts_flag,enquire_".$st_id.",prod_exp_ship_flag,same_day_shipping_market,rts_flag_market,express_shipping_market,prod_name,short_desc,prod_url:prod_url_".$st_id.",prod_small_img,prod_thumb_img,prod_design,prod_is_salable,product_tags_name,product_tags_identifier,prod_availability_label";

		if($cur_cat != 1475){
			$query .= "&fq=actual_price_".$st_id.":[".rawurlencode("1 TO *")."]";
		}

		// $query .= "&fq=prod_is_salable:1";
		//$query .= "&fq=-enquire_".$st_id.":1";
		


		/* apply filters on collection */
		if (isset($app_filter["categoryFilter"]) && !empty($app_filter["categoryFilter"])) {
			$cat_ids = implode(' OR ', $app_filter["categoryFilter"]);
			$query .= "&fq=categories-store-".$st_id."_id:(".rawurlencode($cat_ids).")";				
		}
		// echo "filters::";
		// print_r($app_filter);die;
		if (isset($app_filter["designerFilter"]) && !empty($app_filter["designerFilter"])) {
			$des_ids = implode(' OR ', $app_filter["designerFilter"]);			
			$query .= "&fq=designer_id:(".rawurlencode($des_ids).")";				
		}

		if (isset($app_filter["occasionFilter"]) && !empty($app_filter["occasionFilter"])) {
			$occ_ids = implode(' OR ', $app_filter["occasionFilter"]);
			$query .= "&fq=occasion_id:(".rawurlencode($occ_ids).")";					
		}
		if (isset($app_filter["navratriColorFilter"]) && !empty($app_filter["navratriColorFilter"])) {
			$navr_color_ids = implode(' OR ', $app_filter["navratriColorFilter"]);
			$query .= "&fq=navratri_color_id:(".rawurlencode($navr_color_ids).")";	
		}
		if (isset($app_filter["genderFilter"]) && !empty($app_filter["genderFilter"])) {
            $gen_ids = implode(' OR ', $app_filter["genderFilter"]);
            $query .= "&fq=gender_id:(".rawurlencode($gen_ids).")";                  
        }
        if (isset($app_filter["kidFilter"]) && !empty($app_filter["kidFilter"])) {
            $kid_ids = implode(' OR ', $app_filter["kidFilter"]);
            $query .= "&fq=kid_id:(".rawurlencode($kid_ids).")";                  
        }
		if (isset($app_filter["tagsFilter"]) && !empty($app_filter["tagsFilter"])) {
			$tag_ids = implode(' OR ', $app_filter["tagsFilter"]);
			$query .= "&fq=tags_id:(".rawurlencode($tag_ids).")";					
		}




		// if (isset($app_filter["seasonFilter"]) && !empty($app_filter["seasonFilter"])) {
		// 	$sea_ids = implode(' OR ', $app_filter["seasonFilter"]);
		// 	$query .= "&fq=season_id:(".rawurlencode($sea_ids).")";						
		// }
		
		if (isset($app_filter["acoeditFilter"]) && !empty($app_filter["acoeditFilter"])) {
			$aco_ids = implode(' OR ', $app_filter["acoeditFilter"]);
			$query .= "&fq=a_co_edit_id:(".rawurlencode($aco_ids).")";					
		}
		

		if (isset($app_filter["bridalFilter"]) && !empty($app_filter["bridalFilter"])) {
			$bridal_ids = implode(' OR ', $app_filter["bridalFilter"]);
			$query .= "&fq=bridal_id:(".rawurlencode($bridal_ids).")";					
		}

		if (isset($app_filter["calenderFilter"]) && !empty($app_filter["calenderFilter"])) {
			$calender_ids = implode(' OR ', $app_filter["calenderFilter"]);
			$query .= "&fq=calender_id:(".rawurlencode($calender_ids).")";					
		}

		if (isset($app_filter["patternsFilter"]) && !empty($app_filter["patternsFilter"])) {
			$patterns_ids = implode(' OR ', $app_filter["patternsFilter"]);
			$query .= "&fq=patterns_id:(".rawurlencode($patterns_ids).")";					
		}


		if (isset($app_filter["deliveryFilter"]) && !empty($app_filter["deliveryFilter"])) {
			$encod_del_arr = [];
			foreach ($app_filter["deliveryFilter"] as $del_name) {
				array_push($encod_del_arr, rawurlencode("\"".trim($del_name)."\""));
			}
			$del_names = implode('OR', $encod_del_arr);
			$query .= "&fq=child_delivery_time:(".$del_names.")";						
		}
		
		// price filter 23-08-2023
		if (isset($app_filter["priceFilter"]) && !empty($app_filter["priceFilter"])) {
			if((int) $app_filter["priceFilter"][0] < (int) $app_filter["priceFilter"][1]){
				$minPrice = $this->convertCurrency($app_filter["priceFilter"][0]);
            	$maxPrice = $this->convertCurrency($app_filter["priceFilter"][1]);
			}else{
				$minPrice = $this->convertCurrency($app_filter["priceFilter"][1]);
            	$maxPrice = $this->convertCurrency($app_filter["priceFilter"][0]);
			}
			
			// if($cur_cat == '845') {
                        //error_log('MUSTAFA1 SOLR :::'.$query);
                        // error_log('MUSTAFA1 SOLR :::'. ($minPrice . "::::" . $maxPrice));
	    // }
            $query .= "&fq=special_price_".$st_id.":[".rawurlencode($minPrice." TO ".$maxPrice)."]";
		}

		if (isset($app_filter["sizeFilter"]) && !empty($app_filter["sizeFilter"])) {
			$encod_siz_arr = [];
			foreach ($app_filter["sizeFilter"] as $siz_name) {
				array_push($encod_siz_arr, rawurlencode("\"".trim($siz_name)."\""));
			}
			$siz_names = implode('OR', $encod_siz_arr);
			$query .= "&fq=size_name:(".$siz_names.")";						
		}

		

		// if (isset($app_filter["shipsInFilter"]) && !empty($app_filter["shipsInFilter"])) {
		// 	$shipInRange = $app_filter["shipsInFilter"];
		// 	sort($shipInRange);
		// 	$minDay = $shipInRange[0];
		// 	$encod_childshipped_arr = [];
		// 	$maxPrice = $shipInRange[1];
		// 	if (isset($app_filter["sizeFilter"]) && !empty($app_filter["sizeFilter"])) {
		// 		foreach ($app_filter["sizeFilter"] as $siz_name) {
		// 			array_push($encod_childshipped_arr,"child_shipped_in_".trim($siz_name).":[".rawurlencode($minDay." TO ".$maxPrice)."]");
					

		// 		}
		// 		$child_shipped_names = implode(rawurlencode(' OR '), $encod_childshipped_arr);

		// 		$query .= "&fq=(".$child_shipped_names.")";

		// 	}
		// }

		if (isset($app_filter["colorFilter"]) && !empty($app_filter["colorFilter"])) {
			$col_ids = implode(' OR ', $app_filter["colorFilter"]);
			$query .= "&fq=color_id:(".rawurlencode($col_ids).")";							
		}

		if (isset($app_filter["themeFilter"]) && !empty($app_filter["themeFilter"])) {
			$theme_ids = implode(' OR ', $app_filter["themeFilter"]);
			$query .= "&fq=theme_id:(".rawurlencode($theme_ids).")";							
		}

		if (isset($app_filter["availabilityFilter"]) && !empty($app_filter["availabilityFilter"])) {
			$encod_availability_arr = [];
			foreach ($app_filter["availabilityFilter"] as $availability_id) {
				array_push($encod_availability_arr, rawurlencode("\"".trim($availability_id)."\""));
			}
			$availability_id = implode('OR', $encod_availability_arr);
			$query .= "&fq=availability_id:(".$availability_id.")";						
		}

		
		
		/* sorting*/		
		if (isset($app_filter["sorting"]) && !empty($app_filter["sorting"])) {
			if($app_filter["sorting"] == "new-arrival") {
				$query .= "&sort=".rawurlencode("prod_is_salable desc,prod_en_id_int desc");
			}elseif($app_filter["sorting"] == "high-to-low"){
				$query .= "&sort=".rawurlencode("prod_is_salable desc,special_price_".$st_id." desc");
			}elseif($app_filter["sorting"] == "low-to-high"){
				$query .= "&sort=".rawurlencode("prod_is_salable desc,special_price_".$st_id." asc");
			}
			elseif($app_filter["sorting"] == "bestseller"){
				$query .= "&sort=".rawurlencode("prod_is_salable desc,bestseller_cat_position desc,prod_en_id_int desc");
			}else{
				$query .= "&sort=".rawurlencode("prod_is_salable desc,prod_en_id_int desc");
			}
		} else {

			if($cur_cat == 1372){
				$query .= "&sort=".rawurlencode("prod_is_salable desc,prod_designer_position desc,prod_en_id_int desc");
			}
			else{
				$query .= "&sort=".rawurlencode("prod_is_salable desc,cat_position_".$st_id."_".$cur_cat." desc,prod_en_id_int desc");
			}
			
			
		}

		/* pagination */	
		$query .= "&start=" . $start . "&rows=" . $rows;		
		// error_log("----------------------------------listing page query::".$query);
		if(isset($_GET['otest']) && $_GET['otest'] == 'ntest') {
			echo $query;die;
		}
		// if(isset($_GET['req_data']['filt_to_apply']['mukesh'])) {
		// 	echo $query;
		// 	print_r($app_filter);die;
			
		// }
		
		$filt_coll = $this->_solrHelper->getFilterCollection($query);
		return json_decode($filt_coll, 1);
	}


	/* get filtered product collection */
	public function getSearchFilteredData($start, $rows, $searchKeyword, $app_filter = array(), $coun_code = NULL) {

		//echo $searchKeyword;die;
		$st_id = $this->getCurrentStoreId();
		//$st_id = 1;
		$searchKeyword = rawurlencode($searchKeyword);
		// $query = "q=prod_sku_search:(".$searchKeyword.")".rawurlencode("^16 categories-store-".$st_id."_name:(").$searchKeyword.")".
		// rawurlencode("^8 designer_name_search:(").$searchKeyword.")".
		// rawurlencode("^2 short_desc_search:(").$searchKeyword.")*";
		
		$query = "q=color_name_search:(".$searchKeyword.")".rawurlencode("prod_sku_search:(").$searchKeyword.")".rawurlencode("^8 categories-store-".$st_id."_name:(").$searchKeyword.")".
		rawurlencode("^4 designer_name_search:(").$searchKeyword.")".
		rawurlencode("^2 short_desc_search:(").$searchKeyword.")";
		//$query = "q=short_desc_search:(".$searchKeyword.")*";
		
			
		/* query params */
		
		/* facet params */
		if (isset($app_filter["is_scroll_req"]) && $app_filter["is_scroll_req"] != 1) {
			$query .= "&facet=true&facet.mincount=1&facet.field=designer_token&facet.field=availability_token&facet.field=availability_id&facet.field=availability_name&facet.field=occasion_token&facet.field=patterns_token&facet.field=size_name&facet.field=tags_name&facet.field=bridal_name&facet.field=gender_token&facet.field=kid_token&facet.field=size_id&facet.field=tags_id&facet.field=bridal_id&facet.field=child_delivery_time&facet.field=color_token&facet.field=a_co_edit_token&facet.field=special_price_".$st_id."&facet.field=size_token&facet.field=tags_token&facet.field=bridal_token&facet.field=child_delivery_time_token&facet.field=theme_token&facet.limit=500000";

			
			/*get category filter section for 2nd level category*/

			
			$query .= "&facet.field=categories-store-" . $st_id . "_token";
				/*filter on facets*/
			
		}
		
		$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
		//$country = $objectManager->create('Mec\PriceModifier\Plugin\Magento\Catalog\Model\Product')->getCountryCode();
		// $country = isset($_GET['ci']) ? $_GET['ci'] : 'IN';
		// if(empty($country) || $country == 'IN') {
		//     $specialPriceField = "special_price_".$st_id;
		//     $actualPriceField = "actual_price_".$st_id;
		// }elseif($country == 'US'){
		// 	$specialPriceField = "us_special_price_".$st_id;
		// 	$actualPriceField = "us_actual_price_".$st_id;
		// }else{
		// 	$specialPriceField = "world_special_price_".$st_id;
		// 	$actualPriceField = "world_actual_price_".$st_id;
		// }

		$specialPriceField = "special_price_".$st_id;
		$actualPriceField = "actual_price_".$st_id;
		

		/* field list params */
		$query .= "&fl=actual_price:".$actualPriceField.",special_price:".$specialPriceField.",categories-store-".$st_id."_id,prod_en_id,prod_type,prod_sku,prod_plp_flag,prod_sd_flag,prod_sdm_flag,prod_rts_flag,enquire_".$st_id.",prod_exp_ship_flag,same_day_shipping_market,rts_flag_market,express_shipping_market,prod_name,short_desc,prod_url:prod_url_".$st_id.",prod_small_img,prod_thumb_img,prod_design,prod_is_salable,product_tags_name,prod_availability_label";

		//$query .= "&fq=actual_price_".$st_id.":[".rawurlencode("1 TO *")."]";

		//$query .= "&fq=prod_is_salable:1";
		//$query .= "&fq=-enquire_".$st_id.":1";
		


		/* apply filters on collection */
		if (isset($app_filter["categoryFilter"]) && !empty($app_filter["categoryFilter"])) {
			$cat_ids = implode(' OR ', $app_filter["categoryFilter"]);
			$query .= "&fq=categories-store-".$st_id."_id:(".rawurlencode($cat_ids).")";				
		}
		
		if (isset($app_filter["designerFilter"]) && !empty($app_filter["designerFilter"])) {
			$des_ids = implode(' OR ', $app_filter["designerFilter"]);			
			$query .= "&fq=designer_id:(".rawurlencode($des_ids).")";				
		}
		if (isset($app_filter["tagsFilter"]) && !empty($app_filter["tagsFilter"])) {
			$tag_ids = implode(' OR ', $app_filter["tagsFilter"]);			
			$query .= "&fq=tags_id:(".rawurlencode($tag_ids).")";				
		}

		if (isset($app_filter["occasionFilter"]) && !empty($app_filter["occasionFilter"])) {
			$occ_ids = implode(' OR ', $app_filter["occasionFilter"]);
			$query .= "&fq=occasion_id:(".rawurlencode($occ_ids).")";					
		}

		if (isset($app_filter["genderFilter"]) && !empty($app_filter["genderFilter"])) {
            $gen_ids = implode(' OR ', $app_filter["genderFilter"]);
            $query .= "&fq=gender_id:(".rawurlencode($gen_ids).")";                  
        }
        if (isset($app_filter["kidFilter"]) && !empty($app_filter["kidFilter"])) {
            $kid_ids = implode(' OR ', $app_filter["kidFilter"]);
            $query .= "&fq=kid_id:(".rawurlencode($kid_ids).")";                  
        }

		
		if (isset($app_filter["acoeditFilter"]) && !empty($app_filter["acoeditFilter"])) {
			$aco_ids = implode(' OR ', $app_filter["acoeditFilter"]);
			$query .= "&fq=a_co_edit_id:(".rawurlencode($aco_ids).")";	
						
		}
		

		if (isset($app_filter["bridalFilter"]) && !empty($app_filter["bridalFilter"])) {
			$bridal_ids = implode(' OR ', $app_filter["bridalFilter"]);
			$query .= "&fq=bridal_id:(".rawurlencode($bridal_ids).")";					
		}

		if (isset($app_filter["calenderFilter"]) && !empty($app_filter["calenderFilter"])) {
			$calender_ids = implode(' OR ', $app_filter["calenderFilter"]);
			$query .= "&fq=calender_id:(".rawurlencode($calender_ids).")";					
		}

        if (isset($app_filter["themeFilter"]) && !empty($app_filter["themeFilter"])) {
			$theme_ids = implode(' OR ', $app_filter["themeFilter"]);
			$query .= "&fq=theme_id:(".rawurlencode($theme_ids).")";					
		}

		if (isset($app_filter["patternsFilter"]) && !empty($app_filter["patternsFilter"])) {
			$patterns_ids = implode(' OR ', $app_filter["patternsFilter"]);
			$query .= "&fq=patterns_id:(".rawurlencode($patterns_ids).")";					
		}


		if (isset($app_filter["deliveryFilter"]) && !empty($app_filter["deliveryFilter"])) {
			$encod_del_arr = [];
			foreach ($app_filter["deliveryFilter"] as $del_name) {
				array_push($encod_del_arr, rawurlencode("\"".trim($del_name)."\""));
			}
			$del_names = implode('OR', $encod_del_arr);
			$query .= "&fq=child_delivery_time:(".$del_names.")";						
		}
		
		// price filter
		if (isset($app_filter["priceFilter"]) && !empty($app_filter["priceFilter"])) {
			if((int) $app_filter["priceFilter"][0] < (int) $app_filter["priceFilter"][1]){
				$minPrice = $this->convertCurrency($app_filter["priceFilter"][0]);
            	$maxPrice = $this->convertCurrency($app_filter["priceFilter"][1]);
			}else{
				$minPrice = $this->convertCurrency($app_filter["priceFilter"][1]);
            	$maxPrice = $this->convertCurrency($app_filter["priceFilter"][0]);
			}

			
/*			if($cur_cat == '845') {
                        //error_log('MUSTAFA1 SOLR :::'.$query);
                        error_log('MUSTAFA1 SOLR :::'. ($minPrice . "::::" . $maxPrice));
	    }*/
            $query .= "&fq=special_price_".$st_id.":[".rawurlencode($minPrice." TO ".$maxPrice)."]";
		}

		

		if (isset($app_filter["sizeFilter"]) && !empty($app_filter["sizeFilter"])) {
			$encod_siz_arr = [];
			foreach ($app_filter["sizeFilter"] as $siz_name) {
				array_push($encod_siz_arr, rawurlencode("\"".trim($siz_name)."\""));
			}
			$siz_names = implode('OR', $encod_siz_arr);
			$query .= "&fq=size_name:(".$siz_names.")";						
		}

		

		

		if (isset($app_filter["colorFilter"]) && !empty($app_filter["colorFilter"])) {
			$col_ids = implode(' OR ', $app_filter["colorFilter"]);
			$query .= "&fq=color_id:(".rawurlencode($col_ids).")";							
		}

		// if (isset($app_filter["availabilityFilter"]) && !empty($app_filter["availabilityFilter"])) {
		// 	$encod_availability_arr = [];
		// 	foreach ($app_filter["availabilityFilter"] as $availability_id) {
		// 		array_push($encod_availability_arr, rawurlencode("\"".trim($availability_id)."\""));
		// 	}
		// 	$availability_id = implode('OR', $encod_availability_arr);
		// 	$query .= "&fq=availability_id:(".$availability_id.")";						
		// }

		
		
		/* sorting*/		
		if (isset($app_filter["sorting"]) && !empty($app_filter["sorting"])) {
			if($app_filter["sorting"] == "new-arrival") {
				$query .= "&sort=".rawurlencode("prod_is_salable desc,prod_en_id_int desc");
			}elseif($app_filter["sorting"] == "high-to-low"){
				$query .= "&sort=".rawurlencode("prod_is_salable desc,special_price_".$st_id." desc");
			}elseif($app_filter["sorting"] == "low-to-high"){
				$query .= "&sort=".rawurlencode("prod_is_salable desc,special_price_".$st_id." asc");
			}else{
				$query .= "&sort=".rawurlencode("prod_is_salable desc,prod_en_id_int desc");
			}
		} else {
			$query .= "&sort=".rawurlencode("score desc, prod_en_id_int desc");
			
		}

		/* pagination */	
		$query .= "&start=" . $start . "&rows=" . $rows;		
		// error_log("----------------------------------listing page query::".$query);
		if(isset($_GET['otest']) && $_GET['otest'] == 'ntest') {
			echo $query;die;
		}
		if(isset($_GET['req_data']['filt_to_apply']['mukesh'])) {
			//echo $query;
		}
		//echo $query;die;
		$filt_coll = $this->_solrHelper->getFilterCollection($query);
		 //echo "===========";print_r($filt_coll);die;
		return json_decode($filt_coll, 1);
	}



	/* get filtered product collection */
	public function getSearchSuggestionData($searchKeyword) {

		//echo $searchKeyword;die;
		$st_id = $this->getCurrentStoreId();
		//$st_id = 1;
		$searchKeyword = rawurlencode($searchKeyword);
		$query = "q=color_name_search:(".$searchKeyword.")".rawurlencode("prod_sku_search:(").$searchKeyword.")".rawurlencode("^8 categories-store-".$st_id."_name:(").$searchKeyword.")".
		rawurlencode("^4 designer_name_search:(").$searchKeyword.")".
		rawurlencode("^2 short_desc_search:(").$searchKeyword.")";
		
		//$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
		//$country = $objectManager->create('Mec\PriceModifier\Plugin\Magento\Catalog\Model\Product')->getCountryCode();
		// $country = isset($_GET['ci']) ? $_GET['ci'] : 'IN';
		// if(empty($country) || $country == 'IN') {
		//     $specialPriceField = "special_price_".$st_id;
		//     $actualPriceField = "actual_price_".$st_id;
		// }elseif($country == 'US'){
		// 	$specialPriceField = "us_special_price_".$st_id;
		// 	$actualPriceField = "us_actual_price_".$st_id;
		// }else{
		// 	$specialPriceField = "world_special_price_".$st_id;
		// 	$actualPriceField = "world_actual_price_".$st_id;
		// }
		
		$specialPriceField = "special_price_".$st_id;
		$actualPriceField = "actual_price_".$st_id;
		
				
		/* field list params */
		$query .= "&facet=true&facet.mincount=1&facet.field=occasion_token&fl=actual_price:".$actualPriceField.",special_price:".$specialPriceField.",categories-store-".$st_id."_id,categories-store-".$st_id."_token,categories-store-".$st_id."_url_path,prod_en_id,prod_type,prod_sku,prod_plp_flag,prod_sd_flag,prod_sdm_flag,prod_rts_flag,enquire_".$st_id.",prod_exp_ship_flag,same_day_shipping_market,rts_flag_market,express_shipping_market,prod_name,short_desc,prod_url:prod_url_".$st_id.",prod_small_img,prod_thumb_img,prod_design,prod_is_salable";

		$query .= "&fq=actual_price_".$st_id.":[".rawurlencode("1 TO *")."]";

		//$query .= "&fq=prod_is_salable:1";
		//$query .= "&fq=-enquire_".$st_id.":1";
		



		// echo $query;
		$query .= "&sort=".rawurlencode("score desc, prod_en_id_int desc");

		/* pagination */	
		$query .= "&start=0&rows=4";		
		
		if(isset($_GET['ootest']) && $_GET['ootest'] == 'nntest') {
			echo $query;die;
		}
		
		$filt_coll = $this->_solrHelper->getFilterCollection($query);
		
		return json_decode($filt_coll, 1);
	}


	/* get filtered product collection */
	public function getSearchSuggestionDataForCat($searchKeyword) {

		//echo $searchKeyword;die;
		$st_id = $this->getCurrentStoreId();
		//$st_id = 1;
		$searchKeyword = rawurlencode($searchKeyword);
		$query = "q=categories-store-".$st_id."_name:(".$searchKeyword.")*";
		
		//$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
		//$country = $objectManager->create('Mec\PriceModifier\Plugin\Magento\Catalog\Model\Product')->getCountryCode();
		// $country = isset($_GET['ci']) ? $_GET['ci'] : 'IN';
		// if(empty($country) || $country == 'IN') {
		//     $specialPriceField = "special_price_".$st_id;
		//     $actualPriceField = "actual_price_".$st_id;
		// }elseif($country == 'US'){
		// 	$specialPriceField = "us_special_price_".$st_id;
		// 	$actualPriceField = "us_actual_price_".$st_id;
		// }else{
		// 	$specialPriceField = "world_special_price_".$st_id;
		// 	$actualPriceField = "world_actual_price_".$st_id;
		// }
		
		$specialPriceField = "special_price_".$st_id;
		$actualPriceField = "actual_price_".$st_id;
		
				
		/* field list params */
		$query .= "&facet=true&facet.mincount=1&facet.field=categories-store-".$st_id."_token&fl=categories-store-".$st_id."_name";

		$query .= "&fq=actual_price_".$st_id.":[".rawurlencode("1 TO *")."]";

		//$query .= "&fq=prod_is_salable:1";
		//$query .= "&fq=-enquire_".$st_id.":1";
		



		
		$query .= "&sort=".rawurlencode("score desc, prod_en_id_int desc");

		/* pagination */	
		$query .= "&start=0&rows=5";		
		
		if(isset($_GET['otest']) && $_GET['otest'] == 'ntest') {
			echo $query;die;
		}
		
		$filt_coll = $this->_solrHelper->getFilterCollection($query);
		
		return json_decode($filt_coll, 1);
	}

	/* get facets in array format*/
	public function searchfilterResponseFacets($facet_arr, $on_load = NULL, $priceRange = NULL) {
		$st_id = $this->getCurrentStoreId();
		$all_facets_arr = [];
		$cat_path_arr = [];
		$cat_path_url_arr = [];
		$des_id_arr = [];
		$des_name_arr = [];
		$occ_id_arr = [];
		$gen_id_arr = [];
		$kid_id_arr = [];
		$occ_name_arr = [];
		$gen_name_arr = [];
		$kid_name_arr = [];
		$sea_id_arr = [];
		$sea_name_arr = [];		
		$fab_id_arr = [];
		$fab_name_arr = [];		
		$size_name_arr = [];
		$del_time_arr = [];
		$col_id_arr = [];
		$a_co_edit_id_arr = [];
		$col_name_arr = [];
		$availability_id_arr = [];
		$a_co_edit_name_arr = [];
		$availability_name_arr = [];
		$spe_price_arr = [];
		$clothingArr = array();
		$clothingUrlArr = array();
		$jewelleryArr = array();
		$jewelleryUrlArr = array();
		$accArr = array();
		$accUrlArr = array();
		$seperateCattokenArr = array();
		$newCatArr = array();
		$newChildArr = array();
		$del_day_arr = array();
        $theme_id_arr = [];
		$theme_name_arr = [];
		$attQuery = "select group_concat(entity_id) as category_id from catalog_category_entity_int where attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'show_in_filters') and value = 0";
		$resourceConnection = ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        $connection = $resourceConnection->getConnection();
		$excludeCatArr = $connection->fetchAll($attQuery);
		$excludeCatIds = array();
		if(isset($excludeCatArr[0]['category_id']) && !empty($excludeCatArr[0]['category_id'])){
			$excludeCatIds = explode(",",$excludeCatArr[0]['category_id']);
		}

		$urlIndex = 0;
		$idIndex = 1;
		$mainCatArr = array(3374,1374,1381,1380,6023);
		if (!empty($facet_arr)) {

			foreach ($facet_arr as $f_k => $f_arr) {
				if (!empty($f_arr)) {
					foreach ($f_arr as $sf_k => $fac_v) {
						if ($sf_k % 2 == 0 && $fac_v != NULL) {
							if ($f_k == "categories-store-".$st_id."_token") {
								$catTokenArr = explode('|', $fac_v);
								$catPath = isset($catTokenArr[0]) ? $catTokenArr[0] : '';
								$catUrlPath = isset($catTokenArr[1]) ? $catTokenArr[1] : '';
								$catName = isset($catTokenArr[2]) ? $catTokenArr[2] : '';
								$catKeyArr = explode('/', $catPath);
								$pCatId = end($catKeyArr);
								if(!empty($catKeyArr) && in_array($pCatId,$mainCatArr)){

									if(in_array($pCatId,$excludeCatIds)){
										continue;
									}

									if($catName != ''){
										$newCatArr[$pCatId] = ucwords(strtolower($catName));
									}else{
										continue;
									}

									$pcatkey = array_search ($pCatId, $catKeyArr);
									
									$matches = array();
									$searchword = str_replace('/', '\/', $catPath.'/');
									
									$matches = array_filter($facet_arr['categories-store-'.$st_id.'_token'], function($var) use ($searchword) { return preg_match("/\b$searchword\b/i", $var); });

									
									if(!empty($matches)){
										
										foreach ($matches as $ckey => $childToken) {

											$childcatTokenArr = explode('|', $childToken);
											$childcatPath = isset($childcatTokenArr[0]) ? $childcatTokenArr[0] : '';
											// $childcatUrlPath = isset($childcatTokenArr[1]) ? $childcatTokenArr[1] : '';

											$childCatName = isset($childcatTokenArr[2]) ? $childcatTokenArr[2] : '';

											if($childCatName == ''){
												continue;
											}
											
											$childcatKeyArr = explode('/', $childcatPath);
											// $childcatPathArr = explode('/', $childcatUrlPath);
											
											if(isset($childcatKeyArr[$pcatkey+1])  && (!isset($childcatKeyArr[$pcatkey+2])) ){
													if(in_array($childcatKeyArr[$idIndex+2],$excludeCatIds)){
														continue;
													}
													$newChildArr[$catKeyArr[$idIndex+1]][$childcatKeyArr[$idIndex+2]] = $childCatName;
											}
										}	
										
									}
									

								}
							}  else if ($f_k == "designer_token") {
								$des_da = explode('|', $fac_v);
								array_push($des_id_arr, $des_da[0]);
								//array_push($des_name_arr, strtoupper($des_da[1]));
								// array_push($des_name_arr, ucwords(strtolower($des_da[1])));
								array_push($des_name_arr, $des_da[1]);

							} else if ($f_k == "occasion_token") {
								$occ_da = explode('|', $fac_v);
								array_push($occ_id_arr, $occ_da[0]);
								// array_push($occ_name_arr, strtoupper($occ_da[1]));
								array_push($occ_name_arr, ucwords(strtolower($occ_da[1])));
								}else if ($f_k == "gender_token") {
								$gen_da = explode('|', $fac_v);
								array_push($gen_id_arr, $gen_da[0]);
								// array_push($gen_name_arr, strtoupper($gen_da[1]));
								array_push($gen_name_arr, ucwords(strtolower($gen_da[1])));
							}else if ($f_k == "kid_token") {
								$kid_da = explode('|', $fac_v);
								array_push($kid_id_arr, $kid_da[0]);
								// array_push($kid_name_arr, strtoupper($kid_da[1]));
								array_push($kid_name_arr, ucwords(strtolower($kid_da[1])));
								}
								else if($f_k == "rts_size_token" && $cur_cat == 475){	
								$sizeTokenArr = explode("|", $fac_v);
								//array_push($size_name_arr, $sizeTokenArr[2]);
								$size_name_arr[$sizeTokenArr[0]] = $sizeTokenArr[2];
							}else if($f_k == "size_token"){	
								$sizeTokenArr = explode("|", $fac_v);
								//array_push($size_name_arr, $sizeTokenArr[2]);
								$size_name_arr[$sizeTokenArr[0]] = $sizeTokenArr[2];
							}
							
							else if($f_k == "child_delivery_time_token"){	
								$deliveryTokenArr = explode("|", $fac_v);
								$del_time_arr[$deliveryTokenArr[0]] = $deliveryTokenArr[2];


							}

							else if ($f_k == "color_token") {
								$col_da = explode('|', $fac_v);								
								array_push($col_id_arr, $col_da[0]);
								//array_push($col_name_arr, strtoupper($col_da[1]));
								array_push($col_name_arr, ucwords(strtolower($col_da[1])));
							}else if ($f_k == "a_co_edit_token") {
								$a_co_edit_da = explode('|', $fac_v);								
								array_push($a_co_edit_id_arr, $a_co_edit_da[0]);
								// array_push($a_co_edit_name_arr, strtoupper($a_co_edit_da[1]));
								array_push($a_co_edit_name_arr, ucwords(strtolower($a_co_edit_da[1])));
							}else if ($f_k == "availability_token") {
								$availability_da = explode('|', $fac_v);						
								array_push($availability_id_arr, $availability_da[0]);
								// array_push($availability_name_arr, strtoupper($availability_da[1]));
								array_push($availability_name_arr, ucwords(strtolower($availability_da[1])));
							} else if ($f_k == "special_price_".$st_id) {
								array_push($spe_price_arr, floatval($fac_v));
							} else if ($f_k == "theme_token") {
								$theme_da = explode('|', $fac_v);
								array_push($theme_id_arr, $theme_da[0]);
								// echo 'yes here <pre>';print_r($theme_da[0]);
								// echo 'now here <pre>';print_r($theme_da[1]);
								
								array_push($theme_name_arr, ucwords(strtolower($theme_da[1])));
							}  						 
						}
					}
				}
			}
		}
		

		//print_r($newChildArr);die;
		$cat_arr = $newCatArr;
			
			
		
		
		if (!empty($theme_id_arr) && !empty($theme_name_arr)) {
			$theme_arr = array_combine($theme_id_arr, $theme_name_arr);	
			asort($theme_arr);
		}
		if (!empty($des_id_arr) && !empty($des_name_arr)) {
			$des_arr = array_combine($des_id_arr, $des_name_arr);	
			asort($des_arr);
		} 
		
		if (!empty($occ_id_arr) && !empty($occ_name_arr)) {
			$occ_arr = array_combine($occ_id_arr, $occ_name_arr);	
			asort($occ_arr);
		} 
		if (!empty($gen_id_arr) && !empty($gen_name_arr)) {
			$gen_arr = array_combine($gen_id_arr, $gen_name_arr);	
			asort($gen_arr);
		}
		if (!empty($kid_id_arr) && !empty($kid_name_arr)) {
			$kid_arr = array_combine($kid_id_arr, $kid_name_arr);	
			asort($kid_arr);
		}  

		if (!empty($sea_id_arr) && !empty($sea_name_arr)) {
			$sea_arr = array_combine($sea_id_arr, $sea_name_arr);	
			asort($sea_arr);
		} 

		if (!empty($fab_id_arr) && !empty($fab_name_arr)) {
			$fab_arr = array_combine($fab_id_arr, $fab_name_arr);	
			asort($fab_arr);
		} 

		if (!empty($size_name_arr)) {
			ksort($size_name_arr);
		} 

		$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
			
		
		
		if (!empty($col_id_arr) && !empty($col_name_arr)) {
			$col_arr = array_combine($col_id_arr, $col_name_arr);	
			asort($col_arr);
		} 


		if (!empty($a_co_edit_id_arr) && !empty($a_co_edit_name_arr)) {
			$a_co_arr = array_combine($a_co_edit_id_arr, $a_co_edit_name_arr);	
			asort($a_co_arr);
		}

		if (!empty($availability_id_arr) && !empty($availability_name_arr)) {
			if($cur_cat == 1611 || $cur_cat == 1613 || $cur_cat == 297){
				$availability_arr = array_combine($availability_id_arr, $availability_name_arr);		
				asort($availability_arr);
			}else{
				$availability_arr = array();
			}
			
		} 

		if (!empty($del_time_arr)) {
			ksort($del_time_arr);
		} 
		
		if (isset($cat_arr)) {
			
			$all_facets_arr["categories"] = $cat_arr;
			asort($all_facets_arr["categories"]);	
		}

		//print_r($newChildArr);die;
		if(isset($newChildArr) && !empty($newChildArr)){
			$all_facets_arr["child_categories"] = $newChildArr;
			asort($all_facets_arr["child_categories"]);	
		}
		
		if (isset($des_arr)) {
			$all_facets_arr["designers"] = $des_arr;
		}	

		if(isset($occ_arr[6260])){
			unset($occ_arr[6260]);
		}
		if(isset($occ_arr[6261])){
			unset($occ_arr[6261]);
		}
		if (isset($occ_arr)) {
			$all_facets_arr["occasions"] = $occ_arr;	
		}

		if (isset($gen_arr)) {

			$all_facets_arr["genders"] = $gen_arr;	
		}
		if (isset($kid_arr)) {
			
			$all_facets_arr["kids"] = $kid_arr;	
		}

		$sea_arr = array();
		if (isset($sea_arr)) {
			$all_facets_arr["seasons"] = $sea_arr;		
		}	

		if (isset($fab_arr)) {
			$all_facets_arr["fabric"] = $fab_arr;		
		}	
		
		
		if (isset($size_name_arr)) {
			$all_facets_arr["sizes"] = $size_name_arr;	
		}
		
		// if(isset($_GET['anitestt']) && $_GET['anitestt'] == 'anitestt') {
		// 	print_r($del_time_arr);
		// 	echo "aashni====";
		// }

		if (isset($del_time_arr)) {
			$all_facets_arr["delivery_times"] = $del_time_arr;	
		}
		
		if (isset($col_arr)) {
			$all_facets_arr["colors"] = $col_arr;	
		}
		// if(isset($a_co_arr[6230])){
		// 	unset($a_co_arr[6230]);
		// }if(isset($a_co_arr[6212])){
		// 	unset($a_co_arr[6212]);
		// }
		if (isset($a_co_arr)) {
			$a_co_arr = array();
			$all_facets_arr["a_co_edit"] = $a_co_arr;	
		}
		// if(isset($_GET['gtest']) && $_GET['gtest'] == 'gtest') {
		// 	//print_r($a_co_arr);
		// 	//echo "aashni====";
		// }
		if (isset($availability_arr)) {
			$all_facets_arr["availability"] = $availability_arr;	
		}
		if (isset($theme_arr)) {
			$all_facets_arr["themes"] = $theme_arr;	
		}
		// price filter 23-08-2023
		if (!empty($spe_price_arr) && empty($priceRange)) {
            $all_facets_arr["min_price"] = $this->getCurrencyHelper(min($spe_price_arr));
            $all_facets_arr["max_price"] = $this->getCurrencyHelper(max($spe_price_arr));    
        } else if (!empty($priceRange)) {
        	if((int) $priceRange[0] < (int) $priceRange[1]){
	            $all_facets_arr["min_price"] = $priceRange[0];    
	            $all_facets_arr["max_price"] = $priceRange[1];    
			}else{
	            $all_facets_arr["min_price"] = $priceRange[1];    
	            $all_facets_arr["max_price"] = $priceRange[0];    
			}   
        } else {
            $all_facets_arr["min_price"] = 0;    
            $all_facets_arr["max_price"] = 1;    
        }		
		
		if ($on_load == 1) {
			$all_facets_arr["curr_symb"] = $this->getCurrentCurrencySymbol();	
		}			
		// if(isset($_GET['anitestt']) && $_GET['anitestt'] == 'anitestt') {
		// 	print_r($all_facets_arr);
		// 	echo "aashni====";
		// }					
		return $all_facets_arr;
	}

	/* get facets in array format*/
	public function filterResponseFacets($facet_arr, $on_load = NULL, $priceRange = NULL,$parentCatId = '',$cur_cat = '',$catLevel,$cat_path) {
		$st_id = $this->getCurrentStoreId();
		$all_facets_arr = [];
		$cat_path_arr = [];
		$cat_path_url_arr = [];
		$des_id_arr = [];
		$des_name_arr = [];
		$patterns_name_arr = [];
		$patterns_id_arr = [];
		$occ_id_arr = [];
		$occ_name_arr = [];
		$gen_id_arr = [];
		$kid_id_arr = [];
		$gen_name_arr = [];
		$kid_name_arr = [];
		$sea_id_arr = [];
		$sea_name_arr = [];		
		$fab_id_arr = [];
		$fab_name_arr = [];		
		$size_name_arr = [];
		$del_time_arr = [];
		$col_id_arr = [];
		$a_co_edit_id_arr = [];
		$col_name_arr = [];
		$availability_id_arr = [];
		$a_co_edit_name_arr = [];
		$availability_name_arr = [];
		$spe_price_arr = [];
		$clothingArr = array();
		$clothingUrlArr = array();
		$jewelleryArr = array();
		$jewelleryUrlArr = array();
		$accArr = array();
		$accUrlArr = array();
		$seperateCattokenArr = array();
		$newCatArr = array();
		$newChildArr = array();
		$del_day_arr = array();
        $theme_id_arr =[];
		$theme_name_arr =[];
		$tag_id_arr =array();
		$tag_name_arr =array();
		$bridal_id_arr =array();
		$bridal_name_arr =array();
		$nav_color_id_arr = array();
		$nav_color_name_arr = array();
		
		$attQuery = "select group_concat(entity_id) as category_id from catalog_category_entity_int where attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'show_in_filters') and value = 0";
		$resourceConnection = ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        $connection = $resourceConnection->getConnection();
		$excludeCatArr = $connection->fetchAll($attQuery);
		$excludeCatIds = array();
		if(isset($excludeCatArr[0]['category_id']) && !empty($excludeCatArr[0]['category_id'])){
			$excludeCatIds = explode(",",$excludeCatArr[0]['category_id']);
		}

		$currCatPathArr = explode('/', $cat_path);
		$currMainCat = $currCatPathArr[2];

		$urlIndex = 1;
		$idIndex = 3;
		$urlIndex = $currMainCat == 1375 ? $catLevel - 3 : $catLevel - 2;
		$idIndex = $currMainCat == 1375 ? $catLevel - 1 : $catLevel;
		$mainCatArr = array(3374,1374,1381,1380,6023);
		if (!empty($facet_arr)) {

			foreach ($facet_arr as $f_k => $f_arr) {

				if (!empty($f_arr)) {
					foreach ($f_arr as $sf_k => $fac_v) {

						if ($sf_k % 2 == 0 && $fac_v != NULL) {
							if ($f_k == "categories-store-".$st_id."_token") {
								$catTokenArr = explode('|', $fac_v);
								$catPath = isset($catTokenArr[0]) ? $catTokenArr[0] : '';
								$catUrlPath = isset($catTokenArr[1]) ? $catTokenArr[1] : '';
								$catName = isset($catTokenArr[2]) ? $catTokenArr[2] : '';
								$catKeyArr = explode('/', $catPath);
								$catPathArr = explode('/', $catUrlPath);
								
								$proceed = false;
								if($currMainCat == 1375){
									foreach ($mainCatArr as $key => $value) {
										if(in_array($value, $catKeyArr)){
											$proceed = true;
											break;
										}
									}
									if(!$proceed){
										continue;
									}
								}
								
								if(isset($catKeyArr[$idIndex+1]) && isset($catPathArr[$urlIndex+1]) && (!isset($catKeyArr[$idIndex+2]))){

									// if($catKeyArr[$idIndex+1] == 1500){
									// 	print_r($catKeyArr);
									// }
									if(in_array($catKeyArr[$idIndex+1],$excludeCatIds)){
										continue;
									}
									if($catName != ''){
										$newCatArr[$catKeyArr[$idIndex+1]] = ucwords(strtolower($catName));
									}else{
										continue;
									}
									

									$matches = array();
									$searchword = str_replace('/', '\/', $catPath.'/');
									
									$matches = array_filter($facet_arr['categories-store-'.$st_id.'_token'], function($var) use ($searchword) { return preg_match("/\b$searchword\b/i", $var); });

									
									if(!empty($matches)){
										
										foreach ($matches as $ckey => $childToken) {

											$childcatTokenArr = explode('|', $childToken);
											$childcatPath = isset($childcatTokenArr[0]) ? $childcatTokenArr[0] : '';
											$childcatUrlPath = isset($childcatTokenArr[1]) ? $childcatTokenArr[1] : '';

											$childCatName = isset($childcatTokenArr[2]) ? $childcatTokenArr[2] : '';

											if($childCatName == ''){
												continue;
											}
											
											$childcatKeyArr = explode('/', $childcatPath);
											$childcatPathArr = explode('/', $childcatUrlPath);
											
											if(isset($childcatKeyArr[$idIndex+2]) && isset($childcatPathArr[$urlIndex+2]) && (!isset($childcatKeyArr[$idIndex+3])) ){

													if(in_array($childcatKeyArr[$idIndex+2],$excludeCatIds)){
														continue;
													}

													$newChildArr[$catKeyArr[$idIndex+1]][$childcatKeyArr[$idIndex+2]] = $childCatName;
											}
										}
										
									}
									

								}
							}  else if ($f_k == "designer_token") {
								$des_da = explode('|', $fac_v);
								array_push($des_id_arr, $des_da[0]);
								//array_push($des_name_arr, strtoupper($des_da[1]));
								//array_push($des_name_arr, ucwords(strtolower($des_da[1])));
								array_push($des_name_arr, $des_da[1]);
							}else if ($f_k == "patterns_token") {
								$patterns_da = explode('|', $fac_v);
								array_push($patterns_id_arr, $patterns_da[0]);
								// array_push($patterns_name_arr, strtoupper($patterns_da[1]));
								array_push($patterns_name_arr, ucwords(strtolower($patterns_da[1])));
							} else if ($f_k == "occasion_token") {
								$occ_da = explode('|', $fac_v);
								array_push($occ_id_arr, $occ_da[0]);
								//array_push($occ_name_arr, strtoupper($occ_da[1]));
								array_push($occ_name_arr, ucwords(strtolower($occ_da[1])));
								}else if ($f_k == "gender_token") {
								$gen_da = explode('|', $fac_v);
								array_push($gen_id_arr, $gen_da[0]);
								// array_push($gen_name_arr, strtoupper($gen_da[1]));
								array_push($gen_name_arr, ucwords(strtolower($gen_da[1])));
							}else if ($f_k == "kid_token") {
								$kid_da = explode('|', $fac_v);
								array_push($kid_id_arr, $kid_da[0]);
								// array_push($kid_name_arr, strtoupper($kid_da[1]));
								array_push($kid_name_arr, ucwords(strtolower($kid_da[1])));
								}
								else if($f_k == "tags_token") {
								$tag_da = explode('|', $fac_v);
								array_push($tag_id_arr, $tag_da[0]);
								// array_push($tag_name_arr, strtoupper($tag_da[1]));
								array_push($tag_name_arr, ucwords(strtolower($tag_da[1])));

								}else if($f_k == "bridal_token") {
								$bridal_da = explode('|', $fac_v);
								array_push($bridal_id_arr, $bridal_da[0]);
								// array_push($bridal_name_arr, strtoupper($bridal_da[1]));
								array_push($bridal_name_arr, ucwords(strtolower($bridal_da[1])));
								} 
							else if($f_k == "rts_size_token" && $cur_cat == 475){	
								$sizeTokenArr = explode("|", $fac_v);
								//array_push($size_name_arr, $sizeTokenArr[2]);
								$size_name_arr[$sizeTokenArr[0]] = $sizeTokenArr[2];
							}else if($f_k == "size_token"){	
								$sizeTokenArr = explode("|", $fac_v);
								//array_push($size_name_arr, $sizeTokenArr[2]);
								$size_name_arr[$sizeTokenArr[0]] = $sizeTokenArr[2];
							}
							
							else if($f_k == "child_delivery_time_token"){	
								$deliveryTokenArr = explode("|", $fac_v);
								$del_time_arr[$deliveryTokenArr[0]] = $deliveryTokenArr[2];
								// if(isset($_GET['anitestt']) && $_GET['anitestt'] == 'anitestt') {
									
								// 	echo $deliveryTokenArr[2].' ::array=>';

								// 	print_r($del_time_arr);
								// }	

							}

							else if ($f_k == "color_token") {
								$col_da = explode('|', $fac_v);								
								array_push($col_id_arr, $col_da[0]);
								//array_push($col_name_arr, strtoupper($col_da[1]));
								array_push($col_name_arr, ucwords(strtolower($col_da[1])));

							}else if ($f_k == "a_co_edit_token") {
								$a_co_edit_da = explode('|', $fac_v);								
								array_push($a_co_edit_id_arr, $a_co_edit_da[0]);
								// array_push($a_co_edit_name_arr, strtoupper($a_co_edit_da[1]));
								array_push($a_co_edit_name_arr, ucwords(strtolower($a_co_edit_da[1])));
							}else if ($f_k == "availability_token") {
								$availability_da = explode('|', $fac_v);						
								array_push($availability_id_arr, $availability_da[0]);
								// array_push($availability_name_arr, strtoupper($availability_da[1]));
								array_push($availability_name_arr, ucwords(strtolower($availability_da[1])));
							} else if ($f_k == "special_price_".$st_id) {
								array_push($spe_price_arr, floatval($fac_v));
							} else if ($f_k == "theme_token") {
								$theme_da = explode('|', $fac_v);
								array_push($theme_id_arr, $theme_da[0]);
								
								array_push($theme_name_arr, ucwords(strtolower($theme_da[1])));
							}
							else if ($f_k == "navratri_color_token") {
								$nav_color_da = explode('|', $fac_v);
								array_push($nav_color_id_arr, $nav_color_da[0]);
								array_push($nav_color_name_arr, ucwords(strtolower($nav_color_da[1])));
							}					 
						}
					}
				}
			}
		}
		
		$cat_arr = $newCatArr;
			
			
		

		if (!empty($theme_id_arr) && !empty($theme_name_arr)) {
			$theme_arr = array_combine($theme_id_arr, $theme_name_arr);	
			asort($theme_arr);
		} 
		if (!empty($des_id_arr) && !empty($des_name_arr)) {
			$des_arr = array_combine($des_id_arr, $des_name_arr);	
			asort($des_arr);
		} 

		if (!empty($patterns_id_arr) && !empty($patterns_name_arr)) {
			$patterns_arr = array_combine($patterns_id_arr, $patterns_name_arr);	
			asort($patterns_arr);
		} 
		
		if (!empty($occ_id_arr) && !empty($occ_name_arr)) {
			$occ_arr = array_combine($occ_id_arr, $occ_name_arr);	
			asort($occ_arr);
		} 

		if (!empty($nav_color_id_arr) && !empty($nav_color_name_arr)) {
			$nav_color_arr = array_combine($nav_color_id_arr, $nav_color_name_arr);	
			asort($nav_color_arr);
		} 
			if (!empty($gen_id_arr) && !empty($gen_name_arr)) {
            $gen_arr = array_combine($gen_id_arr, $gen_name_arr);
             //echo "<pre>";print_r($gen_arr);die("aaaa");

            asort($gen_arr);
        } 
        if (!empty($kid_id_arr) && !empty($kid_name_arr)) {
            $kid_arr = array_combine($kid_id_arr, $kid_name_arr);
            asort($kid_arr);
        } 

		if (!empty($tag_id_arr) && !empty($tag_name_arr)) {
			$tag_arr = array_combine($tag_id_arr, $tag_name_arr);	
			asort($tag_arr);
		}
		if (!empty($bridal_id_arr) && !empty($bridal_name_arr)) {
			$bridal_arr = array_combine($bridal_id_arr, $bridal_name_arr);	
			asort($bridal_arr);
		}

		if (!empty($sea_id_arr) && !empty($sea_name_arr)) {
			$sea_arr = array_combine($sea_id_arr, $sea_name_arr);	
			asort($sea_arr);
		} 

		if (!empty($fab_id_arr) && !empty($fab_name_arr)) {
			$fab_arr = array_combine($fab_id_arr, $fab_name_arr);	
			asort($fab_arr);
		} 

		if (!empty($size_name_arr)) {
			ksort($size_name_arr);
		} 

		$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
		$catAllParentIds = $objectManager->create('Magento\Catalog\Model\Category')->load($cur_cat)->getParentIds();	
		if(in_array(1359, $catAllParentIds)){
			$occ_arr = array();
			$sea_arr = array();
			$size_name_arr = array();
		}
		
		if (!empty($col_id_arr) && !empty($col_name_arr)) {
			$col_arr = array_combine($col_id_arr, $col_name_arr);	
			asort($col_arr);
		} 


		if (!empty($a_co_edit_id_arr) && !empty($a_co_edit_name_arr)) {
			$a_co_arr = array_combine($a_co_edit_id_arr, $a_co_edit_name_arr);	
			asort($a_co_arr);
		}

		if (!empty($availability_id_arr) && !empty($availability_name_arr)) {
			if($cur_cat == 1611 || $cur_cat == 1613 || $cur_cat == 297){
				$availability_arr = array_combine($availability_id_arr, $availability_name_arr);		
				asort($availability_arr);
			}else{
				$availability_arr = array();
			}
			
		} 

		if (!empty($del_time_arr)) {
			ksort($del_time_arr);
		} 
		
		if (isset($cat_arr)) {
			
    		if(isset($cat_arr[3375])){
				unset($cat_arr[3375]);
			}if(isset($cat_arr[3434])){
				unset($cat_arr[3434]);
			}if(isset($cat_arr[3287])){
				unset($cat_arr[3287]);
			}
			$all_facets_arr["categories"] = $cat_arr;
			asort($all_facets_arr["categories"]);	
		}

		//print_r($newChildArr);die;

			
		if(isset($newChildArr) && !empty($newChildArr)){
			if(isset($newChildArr[1500][2296])){
				unset($newChildArr[1500][2296]);
			}if(isset($newChildArr[1500][2307])){
				unset($newChildArr[1500][2307]);
			}if(isset($newChildArr[1977][3285])){
				unset($newChildArr[1977][3285]);
			}
			// if(isset($newChildArr[1500][4460])){
			// 	unset($newChildArr[1500][4460]);
			// }if(isset($newChildArr[1500][4450])){
			// 	unset($newChildArr[1500][4450]);
			// }if(isset($newChildArr[1500][4466])){
			// 	unset($newChildArr[1500][4466]);
			// }if(isset($newChildArr[1977][2587])){
			// 	unset($newChildArr[1977][2587]);
			// }if(isset($newChildArr[1500][4454])){
			// 	unset($newChildArr[1500][4454]);
			// }if(isset($newChildArr[1977][2588])){
			// 	unset($newChildArr[1977][2588]);
			// }if(isset($newChildArr[1500][4489])){
			// 	unset($newChildArr[1500][4489]);
			// }if(isset($newChildArr[1978][3335])){
			// 	unset($newChildArr[1978][3335]);
			// }if(isset($newChildArr[1500][4491])){
			// 	unset($newChildArr[1500][4491]);
			// }if(isset($newChildArr[1978][3335])){
			// 	unset($newChildArr[1978][3335]);
			// }
			$all_facets_arr["child_categories"] = $newChildArr;
			asort($all_facets_arr["child_categories"]);	
		}
		
		if (isset($des_arr)) {
			$all_facets_arr["designers"] = $des_arr;
		}
		if (isset($patterns_arr)) {
			// if(isset($_GET['aniitest']) && $_GET['aniitest']=='stest') {
		    //     //var_dump($parent_catId);
		    //     print_r($patterns_arr);
    		// }
    		if(isset($patterns_arr[6091])){
			unset($patterns_arr[6091]);
		}if(isset($patterns_arr[6258])){
			unset($patterns_arr[6258]);
		}
		// if(isset($patterns_arr[6523])){
		// 	unset($patterns_arr[6523]);
		// }
		if(isset($patterns_arr[6264])){
			unset($patterns_arr[6264]);
		}
			$all_facets_arr["patterns"] = $patterns_arr;
		}	
		if(isset($occ_arr[6260])){
			unset($occ_arr[6260]);
		}
		if(isset($occ_arr[6261])){
			unset($occ_arr[6261]);
		}
		if (isset($occ_arr)) {
			if(isset($occ_arr[6134])){
					unset($occ_arr[6134]);
			}
			if(isset($occ_arr[6054])){
					unset($occ_arr[6054]);
			}
			if(isset($occ_arr[6055])){
					unset($occ_arr[6055]);
			}
			if(isset($occ_arr[6056])){
					unset($occ_arr[6056]);
			}
			if($cur_cat == 1374){
				$occ_arr = [];
			}
			$all_facets_arr["occasions"] = $occ_arr;	
		}
		if (isset($nav_color_arr)) {
			$all_facets_arr["navratri_colors"] = $nav_color_arr;	
		}
		$category_plp = $objectManager->create('Magento\Catalog\Model\Category')->load($cur_cat);
		$parent_catId = $category_plp->getParentIds();
        if (isset($gen_arr)) {
        	if($cur_cat == 3374 || $cur_cat == 1374 || $cur_cat == 1381 || $cur_cat == 1380){
        		$gen_arr = [];
        	}
            $all_facets_arr["genders"] = $gen_arr;
        }
        if (isset($kid_arr)) {
        	if($cur_cat == 1372 || $cur_cat == 3374  ){
        		$kid_arr = [];
        	}
            $all_facets_arr["kids"] = $kid_arr;
        }
		if (isset($tag_arr)) {
			$tag_arr = [];
			$all_facets_arr["tags"] = $tag_arr;
		}
		if(isset($bridal_arr[6253])){
					unset($bridal_arr[6253]);
			}
			if(isset($bridal_arr[6255])){
					unset($bridal_arr[6255]);
			}
			if(isset($bridal_arr[6395])){
					unset($bridal_arr[6395]);
			}

        if (isset($bridal_arr)) {
        	if($cur_cat != 3126){
        		$bridal_arr = [];
        	}
			$all_facets_arr["bridal"] = $bridal_arr;
		}
		$sea_arr = array();
		if (isset($sea_arr)) {
			$all_facets_arr["seasons"] = $sea_arr;		
		}	

		if (isset($fab_arr)) {
			$all_facets_arr["fabric"] = $fab_arr;		
		}	
		
		
		if (isset($size_name_arr)) {
			if($cur_cat == 1372){
				if(isset($size_name_arr[82])){
					unset($size_name_arr[82]);
				}
				if(isset($size_name_arr[83])){
					unset($size_name_arr[83]);
				}
			}
			
			$all_facets_arr["sizes"] = $size_name_arr;	
		}
		// if(isset($_GET['anitestt']) && $_GET['anitestt'] == 'anitestt') {
		// 	print_r($del_time_arr);
		// 	echo "aashni====";
		// }	
		if (isset($del_time_arr)) {
			$all_facets_arr["delivery_times"] = $del_time_arr;	
		}
		
		if (isset($col_arr)) {
			$all_facets_arr["colors"] = $col_arr;	
		}

		if (isset($a_co_arr)) {
			//$a_co_arr = array();
			if(isset($a_co_arr[6230])){
					unset($a_co_arr[6230]);
				}
				if(isset($a_co_arr[6212])){
					unset($a_co_arr[6212]);
				}
				if(isset($a_co_arr[6259])){
					unset($a_co_arr[6259]);
				}
				if(isset($a_co_arr[6222])){
					unset($a_co_arr[6222]);
				}
				if(isset($a_co_arr[6367])){
					unset($a_co_arr[6367]);
				}
				if(isset($a_co_arr[6221])){
					unset($a_co_arr[6221]);
				}
				if(isset($a_co_arr[6226]) && $cur_cat != 1380){
					unset($a_co_arr[6226]);
				}
				if(isset($a_co_arr[6229]) && $cur_cat != 1380){
					unset($a_co_arr[6229]);
				}
				if(isset($a_co_arr[6224])){
					unset($a_co_arr[6224]);
				}
				if(isset($a_co_arr[6274])){
					unset($a_co_arr[6274]);
				}
				if(isset($a_co_arr[6214]) && $cur_cat != 1380){
					unset($a_co_arr[6214]);
				}
			if($cur_cat == 1372 ){
				if(isset($a_co_arr[6227])){
					unset($a_co_arr[6227]);
				}
				if(isset($a_co_arr[6236])){
					unset($a_co_arr[6236]);
				}
				if(isset($a_co_arr[6267])){
					unset($a_co_arr[6267]);
				}
				if(isset($a_co_arr[6230])){
					unset($a_co_arr[6230]);
				}
				if(isset($a_co_arr[6212])){
					unset($a_co_arr[6212]);
				}
				if(isset($a_co_arr[6228])){
					unset($a_co_arr[6228]);
				}
				if(isset($a_co_arr[6273])){
					unset($a_co_arr[6273]);
				}
				if(isset($a_co_arr[6213])){
					unset($a_co_arr[6213]);
				}
				if(isset($a_co_arr[6387])){
					unset($a_co_arr[6387]);
				}
				if(isset($a_co_arr[6276])){
					unset($a_co_arr[6276]);
				}
				if(isset($a_co_arr[6230])){
					unset($a_co_arr[6230]);
				}
				if(isset($a_co_arr[6212])){
					unset($a_co_arr[6212]);
				}
			}
			
			$all_facets_arr["a_co_edit"] = $a_co_arr;	
		}

		if (isset($availability_arr)) {
			$all_facets_arr["availability"] = $availability_arr;	
		}
		if (isset($theme_arr)) {
			$all_facets_arr["themes"] = $theme_arr;		
		}

		// price filter 23-08-2023
		if (!empty($spe_price_arr) && empty($priceRange)) {
            $all_facets_arr["min_price"] = $this->getCurrencyHelper(min($spe_price_arr));
            $all_facets_arr["max_price"] = $this->getCurrencyHelper(max($spe_price_arr));    
        } else if (!empty($priceRange)) {
        	if((int) $priceRange[0] < (int) $priceRange[1]){
	            $all_facets_arr["min_price"] = $priceRange[0];    
	            $all_facets_arr["max_price"] = $priceRange[1];    
			}else{
	            $all_facets_arr["min_price"] = $priceRange[1];    
	            $all_facets_arr["max_price"] = $priceRange[0];    
			}   
        } else {
            $all_facets_arr["min_price"] = 0;    
            $all_facets_arr["max_price"] = 1;    
        }

		
		
		if ($on_load == 1) {
			$all_facets_arr["curr_symb"] = $this->getCurrentCurrencySymbol();	
		}							
		return $all_facets_arr;
	}

	/* get product listing grid html */
	public function getProductGrid($prod_arr, $cat_data = array()) {
		$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
		$cartHelper = $objectManager->create('Magento\Checkout\Helper\Cart');
		$formKey = $objectManager->create('\Magento\Framework\Data\Form\FormKey')->getFormKey();

		$urlParamName = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;
		$uenc = $cartHelper->getProdUenc();
		
		
		$storeId = $this->getCurrentStoreId();
		$prod_html = "";
		$script_html  = "";
		$i = 1;
		$prodSchemaData = array();
		$prodScriptArr = array();
		$prod_Script = array();
		$prod_Schema = array();
		$ogImgArr = array();

		$utm_url = "?";
		$utm_url_set = false;
    	if (isset($_GET['utm_source']) || isset($_GET['utm_medium']) || isset($_GET['utm_campaign']) || isset($_GET['utm_content'])) {

        	$params = array();
    		
    		if (isset($_GET['utm_source']) && !empty($_GET['utm_source'])) {
        		$utm_source = rawurlencode($_GET['utm_source']);
        		$params[] = "utm_source=" . $utm_source;
    		}

    		if (isset($_GET['utm_medium']) && !empty($_GET['utm_medium'])) {
        		$utm_medium = rawurlencode($_GET['utm_medium']);
        		$params[] = "utm_medium=" . $utm_medium;
    		}

    		if (isset($_GET['utm_campaign']) && !empty($_GET['utm_campaign'])) {
        		$utm_campaign = rawurlencode($_GET['utm_campaign']);
        		$params[] = "utm_campaign=" . $utm_campaign;
    		}

    		if (isset($_GET['utm_content']) && !empty($_GET['utm_content'])) {
        		$utm_content = rawurlencode($_GET['utm_content']);
        		$params[] = "utm_content=" . $utm_content;
    		}

    		if (!empty($params)) {
        		$utm_url .= implode("&", $params);
        		$utm_url_set = true;
    		}
    	}

		if (!empty($prod_arr)) {
			$med_url = $this->getMediaUrl();
			$cat_id = isset($cat_data["cat_id"]) ? $cat_data["cat_id"] : '';
			$i = 1;
			foreach ($prod_arr as $prod) {

				//Aniket's code for hidding price
				// print_r($prod);die;
				// if(isset($_GET['otest']) && $_GET['otest'] == 'ntest') {
				// 		print_r($prod);
				// }
				$enquirenow = isset($prod['enquire_'.$storeId][0]) ? $prod['enquire_'.$storeId][0] : '';
				

				$prodUrl = isset($prod["prod_url"][0]) ? $prod["prod_url"][0] : '';
			    $prodUrl = preg_replace('/&?___store=[^&]*/', '', $prodUrl);
			    $prodUrl = str_replace('?','', $prodUrl);
			    
				$smallImage = isset($prod["prod_small_img"]) ? $prod["prod_small_img"]."?w=400" : '';
				$thumbnailImage = isset($prod["prod_thumb_img"]) ? $prod["prod_thumb_img"]."?w=400" : '';
				$name = isset($prod['prod_name'][0]) ? $prod['prod_name'][0] : '';
				$prodId =  isset($prod['prod_en_id']) ? $prod['prod_en_id'] : '';
				$routeParams = [
		            $urlParamName => $uenc,
		            'product' => $prodId,
		            '_secure' => 1
		        ];
		        $addToCartUrl = $cartHelper->getProdCartUrl($routeParams);
				$designerName = isset($prod['prod_design']) ? $prod['prod_design'] : '';
				$shortDesc = isset($prod['short_desc']) ? $prod['short_desc'] : '';
			   $product_tags = isset($prod['product_tags_name'][0]) ? $prod['product_tags_name'][0] : '';
               $availability_label = isset($prod['prod_availability_label'][0]) ? $prod['prod_availability_label'][0] : '';
                $pricehtml = '';
                $actualPrice = 0;
            	// anikets code
				if($enquirenow == 1){
					// echo $enquirenow. ' :Inif';
					if ((isset($prod['special_price']) && isset($prod['actual_price'])) && ($prod['special_price'] < $prod['actual_price'])) {
		                $actualPrice = $prod['actual_price'];
		                $specialPrice = $prod['special_price'];
		                $specialPrice = $this->getPrice($specialPrice);
		                $actualPrice = $this->getPrice($actualPrice);
	                	$pricehtml = '<button type="button"
                                                                          style="background: #fff; z-index:28; color: #000; padding: 5px 25px;
                                                                          position: relative; top: 8px; border:1px #000 solid; margin-bottom: 5px;"
                                                                          class="btn btn-primary" data-toggle="modal"
                                                                          data-target="#listEnquiryModal"
                                                                          data-sku= "'.$prod['prod_sku'].'"
                                                                          data-description="'.$shortDesc.'"
                                                                          data-name="'.$name.'">Enquire Now </button>';
	            
	            	} else {
	                	$actualPrice = $this->getPrice(isset($prod["actual_price"]) ? $prod["actual_price"] : 0);
	                	$pricehtml = '<button type="button"
                                                                          style="background: #fff; z-index:28; color: #000; padding: 5px 25px;
                                                                          position: relative; top: 8px; border:1px #000 solid; margin-bottom: 5px;"
                                                                          class="btn btn-primary" data-toggle="modal"
                                                                          data-target="#listEnquiryModal"
                                                                          data-sku="'.$prod['prod_sku'].'"
                                                                          data-description="'.$shortDesc.'"
                                                                          data-name="'.$name.'">Enquire Now </button>';
	            	}   
				}else{
					// echo $enquirenow. ' :Inelse';
					if ((isset($prod['special_price']) && isset($prod['actual_price'])) && ($prod['special_price'] < $prod['actual_price'])) {
		                $actualPrice = $prod['actual_price'];
		                $specialPrice = $prod['special_price'];
		                $specialPrice = $this->getPrice($specialPrice);
		                $actualPrice = $this->getPrice($actualPrice);
		                $pricehtml = '<div class="price-box price-final_price" data-role="priceBox" data-product-id="227330" data-price-box="product-id-227330"><span class="normal-price">
	    					<span class="price-container price-final_price tax weee">
							        <span id="product-price-227330" data-price-amount="35200" data-price-type="finalPrice" class="price-wrapper "><span class="price">'.$specialPrice.'</span></span>
							        </span>
							</span>

							    <span class="old-price sly-old-price">
							        

							<span class="price-container price-final_price tax weee">
							            <span class="price-label">Regular Price</span>
							        <span id="old-price-227330" data-price-amount="44000" data-price-type="oldPrice" class="price-wrapper "><span class="price">'.$actualPrice.'</span></span>
							        </span>
							    </span>


							</div>';
		            
		            } elseif(isset($prod['actual_price']) && $prod['actual_price'] > 0) {
		                $actualPrice = $this->getPrice(isset($prod["actual_price"]) ? $prod["actual_price"] : 0);
		                $pricehtml = '<div class="price-box price-final_price" data-role="priceBox" data-product-id="232055" data-price-box="product-id-232055"><span class="normal-price"><span class="price-container price-final_price tax weee">
						        <span id="product-price-232055" data-price-amount="42000" data-price-type="finalPrice" class="price-wrapper "><span class="price">'.$actualPrice.'</span></span>
						        </span>
						</span></div>';
		            }
				}
				

	            $class = '';
	            
	            if(($i % 3) == 1){
	            	$class .= ' first-row-item first-sm-item';
	            }
	            if(($i % 2) == 1){
	            	$class .= " first-xs-item";
	            }
	            if($i == 1){
	            	$class .= " first-element";
	            }

	            $i++;

	            $categoryRepository = $objectManager->get('\Magento\Catalog\Api\CategoryRepositoryInterface');
	            $parent = $objectManager->create('Magento\Catalog\Model\Product')->load($prodId);
                $cats = $parent->getCategoryIds();
                $category_name = '';
                $category_id = isset($cats[0]) ? $cats[0] : 0; 
                if($category_id){
                    try {
                        $category = $categoryRepository->get($category_id);
                        $category_name = $category->getName(); 
                    }catch(Exception $e) {
                        $category_name = 'NA';
                    }
                }
	            //echo $wishlistLi;die;
	           
                $prodUrl = $utm_url_set ? $prodUrl.$utm_url : $prodUrl;
                $smallImage = str_replace("static.aashniandco.com","imgs-aashniandco.gumlet.io",$smallImage);
                $thumbnailImage = str_replace("static.aashniandco.com","imgs-aashniandco.gumlet.io",$thumbnailImage);
			     $prod_html .= '<li class="item product product-item-info product-item col-lg-4 col-md-4 col-sm-4 col-xs-6 '.$class.'" data-shortDesc="'.$shortDesc.'" data-value="'.$actualPrice.'" data-sku="'.$prod['prod_sku'].'" data-brand="'.$designerName.'" data-category="'.$category_name.'"><div class="product-top">
							<a href="'.$prodUrl.'" class="product photo product-item-photo" tabindex="-1" target="_blank">
								<img src="'.$smallImage.'" alt="'.$shortDesc.'" class="img-responsive product-image-photo img-thumbnail" data-original="'.$smallImage.'">
								<img src="'.$thumbnailImage.'" alt="hover-img" class="hover-img" />
							</a>
							<ul class="actions-link" data-role="add-to-links">
							
								<li class="hidden-sm hidden-xs"><button data-title="Quick View" class="action mgs-quickview" data-quickview-url="/mgs_quickview/catalog_product/view/id/'.$prodId.'/" title="Quick View">
								<span class="fa fa-eye"></span></button></li>
								<li><button data-title="Add to Wish List" class="action towishlist" title="Add to Wish List" aria-label="Add to Wish List" data-post="{&quot;action&quot;:&quot;https:\/\/orders.aashniandco.com\/wishlist\/index\/add\/&quot;,&quot;data&quot;:{&quot;product&quot;:'.$prodId.',&quot;uenc&quot;:&quot;'.$uenc.'&quot;}}" data-action="add-to-wishlist" role="button" data-shortDesc="'.$shortDesc.'" data-value="'.$actualPrice.'" data-sku="'.$prod['prod_sku'].'" data-brand="'.$designerName.'" data-category="'.$category_name.'">
										<i class="fa fa-heart"></i>
									</button></li><li>																		<form data-role="tocart-form" action="'.$addToCartUrl.'" method="post" novalidate="novalidate">
										<input type="hidden" name="product" value="'.$prodId.'">
										<input type="hidden" name="uenc" value="'.$uenc.'">
										<input name="form_key" type="hidden" value="'.$formKey.'">										<button type="submit" title="Add to Cart" class="action tocart">
											<span class="fa fa-shopping-cart"></span>
										</button>
									</form></li>
															</ul>
						</div> 

						<div class="product details product-item-details">
														<div class="products product name product-item-name">
								<a class="product-item-link" href="'.$prodUrl.'">
									'.$designerName.'								</a>
							</div>
							<div class="product description product-item-description">
								'.$shortDesc.'								<a href="'.$prodUrl.'" title="'.$designerName.'" class="action more">
								</a>
							</div>

							'.$pricehtml.'</div>';
							if(isset($_GET['otest']) && $_GET['otest'] == 'ntest') {
									//echo $availability_label.' ::tags';
							}
							if($availability_label != 'Out of stock'){
								if($product_tags != ''){
									$prod_html .='<div class="readyto-process"><div class="readyto-btn">'.$product_tags.'</div></div></li>';
								}
							}else{
								$prod_html .='<div class="readyto-process"><div class="readyto-btn">'.$availability_label.'</div></div></li>';
							}

			}
		}
		
	
		$prod_html_arr['prod_html'] = isset($prod_html) ? $prod_html : '';
		

        return $prod_html_arr;
	} 

	/* get media url */
    public function getMediaUrl() {
    	return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . "ogaanImages/";

    }

    /* get formatted price */
    public function getPrice($price) {
    	return $this->getCurrentCurrencySymbol() . $this->formatPrice($this->getCurrencyHelper($price));
    }

    /* get page limit for pagination */
    public function getPageLimit() {
    	return 12;
    }

    /* remove unwanted keys from facets*/
    public function sanitizeFacets($appl_facets) {
    	 
        $avai_facet_arr = ['priceFilter', 'shipsInFilter', 'designerFilter', 'occasionFilter','genderFilter','seasonFilter','fabricFilter', 'sizeFilter', 'colorFilter','availabilityFilter', 'categoryFilter','acoeditFilter','bridalFilter','calenderFilter','kidFilter','patternsFilter','deliveryFilter','sorting','tagsFilter','themeFilter', 'navratriColorFilter'];  
        if (!empty($appl_facets)) {
            foreach ($appl_facets as $type => $value) {
                if (!in_array($type, $avai_facet_arr)) {
                    unset($appl_facets[$type]);
                }
            } 
                               
            ksort($appl_facets);
            return $appl_facets;
        } else {
            return [];
        }
    }

    /* get applied filters form url */
    public function getFiltersFromQuery($que_params = array()) {
        $appl_filt = [];
       	
        if (!empty($que_params)) {
            if (isset($que_params["firstAttemptedFilter"])) {
                unset($que_params["firstAttemptedFilter"]);
            }

            foreach ($que_params as $pa_k => $pa_val) {
                if (strpos($pa_k, "Filter") !== FALSE) {
                    $appl_filt[$pa_k] =  $this->splitValues($pa_val);    
                } else if ($pa_k == "sorting") {
                    $appl_filt[$pa_k] = $pa_val;
                }
            }
        }
        $appl_filt["is_scroll_req"] = 0;
        return $appl_filt;
    }

    /* split filter values by "+" sign */
    public function splitValues($str) {
    	
        return explode('+', $str);
    }

    /* get current currency code */
    public function getCurrentCurrencyCode() {
    	return $this->_curr_inter->getCurrency()->getCode();
    }

    /* get current store id */
	protected function getCurrentStoreId() {
		return $this->_storeManager->getStore()->getId();
	}

	public function getCurrentCurrencySymbol() {
		$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
        $priceCurrency = $objectManager->get('\Magento\Framework\Pricing\PriceCurrencyInterface');
        return $priceCurrency->getCurrency()->getCurrencySymbol();
    }

    public function checkIsInWishlist($productId) {
        $isInWishlist = 0;
        if ($this->isCustomerLoggedIn() == 1) {

            $wishlistObj = ObjectManager::getInstance()->get('\Magento\Wishlist\Model\Wishlist');
            $customerId = $this->getCustomerId();
            $productId = (int)$productId;
            $wishlistCollection = $wishlistObj->loadByCustomerId($customerId, true)->getItemCollection();    
            foreach ($wishlistCollection as $wishlistItem) {
                if($productId == $wishlistItem->getProductId()){
                    $isInWishlist = 1;
                    break;
                } else {
                    $isInWishlist = 0;
                }
            }
        } else {
            $isInWishlist = 0;
        }
        return $isInWishlist;
    }


    public function isCustomerLoggedIn() {
        $loggedIn = '';
        error_log('--------is custoemr log in-------');
        $customerSession = ObjectManager::getInstance()->get('Magento\Customer\Model\Session');
        error_log('--------is custoemr log in-2------');
        if ($customerSession->isLoggedIn()) {
            $loggedIn = 1;
        } else {
            $loggedIn = 0;
        }
        return $loggedIn;
    }

    public function formatPrice($price) {
        $newPrice = floatval((float)$price);
        if ( strpos($newPrice, ".") !== false ) {
            $price = floatval(number_format($newPrice,4,'.',''));
            if(strpos($price, ".") !== false) {
                return number_format($price,2);  
            } else {
              return number_format($price);
            }
        } else {
            return number_format($newPrice);
        }
    }

    public function getCurrencyHelper($price) {
        $priceHelper = ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data');
        return $priceHelper->currency($price,false,false);
    }

    public function getSearchSuggestItemHtml($productsData){
    	$cartHelper = ObjectManager::getInstance()->create('Magento\Checkout\Helper\Cart');
		$uenc = $cartHelper->getProdUenc();
		$itemsArr = array();
		$urlParamName = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;

    	foreach($productsData as $pkey => $pvalue){
            $prodId = isset($pvalue['prod_en_id']) ? $pvalue['prod_en_id'] : '';
            $item["name"] = isset($pvalue['prod_name'] [0]) ? $pvalue['prod_name'][0] : '';
            $item["url"] = isset($pvalue['prod_url'] [0]) ? $pvalue['prod_url'][0] : '';
            $item["sku"] = '';
            $item["description"] = isset($pvalue['short_desc']) ? $pvalue['short_desc'] : '';
            $item["image"] = isset($pvalue['prod_small_img']) ? $pvalue['prod_small_img'] : '';
            if(isset($pvalue['enquire_1'][0]) && $pvalue['enquire_1'][0] == 1){

            	 $item["price"] = '<div class="price-box price-final_price" data-role="priceBox" data-product-id="'.$prodId.'" data-price-box="product-id-'.$prodId.'"></div>';


            	
            }else{
               if ((isset($pvalue['special_price']) && isset($pvalue['actual_price'])) && ($pvalue['special_price'] < $pvalue['actual_price'])) {
	                $actualPrice = $pvalue['actual_price'];
	                $specialPrice = $pvalue['special_price'];
	                $price = $this->getPrice($specialPrice);
	                $actualPrice = $this->getPrice($actualPrice);


	                $item["price"] = '<div class="price-box price-final_price" data-role="priceBox" data-product-id="'.$prodId.'" data-price-box="product-id-'.$prodId.'"><span class="normal-price"><span class="price-container price-final_price"        >        <span  id="product-price-'.$prodId.'"                data-price-amount="'.$price.'"        data-price-type="finalPrice"        class="price-wrapper "    ><span class="price">'.$price.'</span></span>        </span></span>    <span class="old-price sly-old-price">        <span class="price-container price-final_price"        >            <span class="price-label">Regular Price</span>        <span  id="old-price-'.$prodId.'"                data-price-amount="'.$actualPrice.'"        data-price-type="oldPrice"        class="price-wrapper "    ><span class="price">'.$actualPrice.'</span></span>        </span>    </span></div>';
	            } else {
	                $price = $this->getPrice(isset($pvalue["actual_price"]) ? $pvalue["actual_price"] : 0);
	                $item["price"] = '<div class="price-box price-final_price" data-role="priceBox" data-product-id="'.$prodId.'" data-price-box="product-id-'.$prodId.'"><span class="normal-price">    <span class="price-container price-final_price"        >        <span  id="product-price-'.$prodId.'"                data-price-amount="'.$price.'"        data-price-type="finalPrice"        class="price-wrapper "    ><span class="price">'.$price.'</span></span>        </span></span></div>';
	                
	            }
               
            }
            

            $routeParams = [
		            $urlParamName => $uenc,
		            'product' => $prodId,
		            '_secure' => 1
		        ];
		
		        
		    $addToCartUrl = $cartHelper->getProdCartUrl($routeParams);

            $item["rating"] = "";
            $item["cart"] = array("visible"=>false,"label"=>"Add to Cart","params"=>array("action"=>$addToCartUrl,"data"=>array("product"=>$prodId,
						"uenc"=> $uenc)));
            $item["stock_status"] = null;
            $item["optimize"] = false;

            $itemsArr[] = $item;

        }
        return $itemsArr;
    }

}

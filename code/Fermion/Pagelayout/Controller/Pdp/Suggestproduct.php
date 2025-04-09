<?php
namespace Fermion\Pagelayout\Controller\Pdp;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;


class Suggestproduct extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $_cacheInterface;
	protected $_storeManager;
	protected $_request;
	protected $_cartHelper;
	protected $_formKey;
	protected $_solrHelper;
	protected $_priceCurrencyInterface;
	protected $_priceHelper;
	protected $_connection;
    protected $renderedProductIds = [];

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Magento\Framework\App\CacheInterface $cacheInterface,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Checkout\Helper\Cart $cartHelper,
		\Magento\Framework\Data\Form\FormKey $formKey,
		\Mec\PriceModifier\Plugin\Magento\Catalog\Model\Product $productModel,
		\Fermion\Pagelayout\Helper\SolrHelper $solrHelper, 
		\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrencyInterface,
		\Magento\Framework\Pricing\Helper\Data $priceHelper,
		\Magento\Framework\App\ResourceConnection $resource
	)
	{
		$this->_pageFactory = $pageFactory;
		$this->_cacheInterface = $cacheInterface;
		$this->_storeManager = $storeManager;
		$this->_request = $request;
		$this->_cartHelper = $cartHelper;
		$this->_formKey = $formKey;
		$this->_productModel = $productModel;
		$this->_solrHelper = $solrHelper;
		$this->_priceCurrencyInterface = $priceCurrencyInterface;
		$this->_priceHelper = $priceHelper;
		$this->_connection = $resource->getConnection();
		return parent::__construct($context);
	}

	public function execute()
	{
		
		$resultPage = $this->_pageFactory->create();
		$widgetCacheData = array();
		
		$pId = $this->_request->getParam('pid');
		
		$currency = $this->_storeManager->getStore()->getCurrentCurrencyCode();
		$cacheKey = "pdp_widget_".$pId."_".$currency;
		$widgetCacheData = $this->_cacheInterface->load($cacheKey);
		$widgetCacheData = array();
		if ((!$widgetCacheData)) {
			
			$suggestedTab = $resultPage->getLayout()
                        ->createBlock('Mec\SuggestedProducts\Block\SuggestedProduct')
                        ->setTemplate('Mec_SuggestedProducts::suggestedproducts_tab.phtml')
                        ->toHtml();

			
            $designer = $this->_request->getParam('designer');
            $pattern = $this->_request->getParam('patterns');
            $gender = $this->_request->getParam('gender');
            $colors = $this->_request->getParam('colors');
			$categoryIds = $this->_request->getParam('categoryIds');
			$theme = $this->_request->getParam('theme');
			$kidsId = $this->_request->getParam('kidsId');
			$finalprice = $this->_request->getParam('finalprice');
			$lastChildId = $this->_request->getParam('lastChildId');
			$recentlyViewedSkus = $this->_request->getParam('recentlyViewedSkus');


			$pairItWithHtml = $this->getpairItWithHtml($pId,$theme,$categoryIds,$colors,$gender,$finalprice);
			$readyToShipHtml = $this->getReadyToShipHtml($pId,$pattern,$kidsId,$gender);
			$similarColorHtml = $this->getSimilarColorHtml($pId,$gender,$colors,$kidsId, $categoryIds);
            $youmaylikehtml = $this->getYouMayAlsoLikeHtml($pattern, $lastChildId, $gender, $finalprice);
            $moreFromDesigner = $this->getRelatedProductHtml($designer);   
            $newArrivalsHtml = $this->getNewArrivalsHtml($categoryIds, $gender);   
            $recentViewed = $this->getRecentViewedHtml($recentlyViewedSkus);   


            $widgetCacheData['more_from_designer'] = $moreFromDesigner;
			$widgetCacheData['you_may_also_like'] = $youmaylikehtml;
			$widgetCacheData['suggested_tab'] = $suggestedTab;
			$widgetCacheData['ready_to_ship'] = $readyToShipHtml;
			$widgetCacheData['similar_color'] = $similarColorHtml;
			$widgetCacheData['pair_it_with'] = $pairItWithHtml;
			$widgetCacheData['new_arrivals'] = $newArrivalsHtml;
			$widgetCacheData['recent_viewed'] = $recentViewed;
			
			$widgetCacheData = json_encode($widgetCacheData);

			$this->_cacheInterface->save($widgetCacheData, $cacheKey, array(),86400);
		}

		
		
		echo $widgetCacheData;die;
	}

public function getpairItWithMen($pId,$theme,$categoryIds,$colors,$gender,$finalprice)
{
	$men_final_array = array();
	$query = "SELECT * FROM pdp_widgets_product_pairings a  WHERE a.clothing_category_id IN ($categoryIds)  AND a.clothing_color_id IN ($colors)  AND a.theme_id IN ($theme) AND a.gender_id IN ($gender)";

    $resultArr = $this->_connection->fetchAll($query);
    if($resultArr){
	    $resultArr = $resultArr[0];

	    $m_shoes = $resultArr['m_shoes'];
	    $m_shoes_color = $resultArr['m_shoes_color'];
	    $m_accessories = $resultArr['m_accessories'];
	    $m_accessories_color = $resultArr['m_accessories_color'];

	    
		$m_shoesArr = explode(',', $m_shoes);
	    if (is_array($m_shoesArr)) {
	        $m_shoesQuery = implode(' OR ', $m_shoesArr);
	    } else {
	        $m_shoesQuery = $m_shoes;
	    }

		$m_shoes_colorArr = explode(',', $m_shoes_color);
	    if (is_array($m_shoes_colorArr)) {
	        $m_shoes_colorQuery = implode(' OR ', $m_shoes_colorArr);
	    } else {
	        $m_shoes_colorQuery = $m_shoes_color;
	    }

	    	$storeId = $this->_storeManager->getStore()->getId();
			$specialPriceField = "special_price_".$storeId;
			$actualPriceField = "actual_price_".$storeId;

			$shoesQuery = "q=*:*";
			$shoesQuery .= "&fl=actual_price:".$actualPriceField.",special_price:".$specialPriceField.",categories-store-".$storeId."_id,prod_en_id,prod_type,prod_sku,prod_plp_flag,prod_sd_flag,prod_sdm_flag,prod_rts_flag,enquire_".$storeId.",prod_exp_ship_flag,same_day_shipping_market,rts_flag_market,express_shipping_market,prod_name,short_desc,prod_url:prod_url_".$storeId.",prod_small_img,prod_thumb_img,prod_design,prod_is_salable";

			$shoesQuery .= "&fq=actual_price_".$storeId.":[".rawurlencode("1 TO *")."]";
			$shoesQuery .= "&fq=!prod_en_id:".$this->_request->getParam('pid');

			$shoesQuery .= "&fq=category_name_search:(".rawurlencode($m_shoesQuery).")";
			$shoesQuery .= "&fq=color_name:(".rawurlencode($m_shoes_colorQuery).")";
			$shoesQuery .= "&fq=gender_id:".$gender;
			$shoesQuery .= "&fq=".$actualPriceField.":{*%20TO%20".$finalprice."}" ;


	    $randomNumber = rand(100, 999);
	    $shoesQuery .= '&sort=' . rawurlencode('random_' . $randomNumber . ' asc');
	    $shoesQuery .= "&start=0&rows=10";
	    // echo $shoesQuery;
	    // die;
	    $menShoes = json_decode($this->_solrHelper->getFilterCollection($shoesQuery), true);
	    $menShoes = isset($menShoes["response"]["docs"]) ? $menShoes["response"]["docs"]: [];
	    // echo '<pre>';
	    // print_r($menShoes);
	    // die;
	    // ----------------------------------------------------------------------
	    $m_accessoriesArr = explode(',', $m_accessories);
	    if (is_array($m_accessoriesArr)) {
	        $m_accessoriesQuery = implode(' OR ', $m_accessoriesArr);
	    } else {
	        $m_accessoriesQuery = $m_accessories;
	    }

	    $m_accessories_colorArr = explode(',', $m_accessories_color);
	    if (is_array($m_accessories_colorArr)) {
	        $m_accessories_colorQuery = implode(' OR ', $m_accessories_colorArr);
	    } else {
	        $m_accessories_colorQuery = $m_accessories_color;
	    }
			$storeId = $this->_storeManager->getStore()->getId();
			$specialPriceField = "special_price_".$storeId;
			$actualPriceField = "actual_price_".$storeId;
			$accessQuery = "q=*:*";
			
			
			$accessQuery .= "&fl=actual_price:".$actualPriceField.",special_price:".$specialPriceField.",categories-store-".$storeId."_id,prod_en_id,prod_type,prod_sku,prod_plp_flag,prod_sd_flag,prod_sdm_flag,prod_rts_flag,enquire_".$storeId.",prod_exp_ship_flag,same_day_shipping_market,rts_flag_market,express_shipping_market,prod_name,short_desc,prod_url:prod_url_".$storeId.",prod_small_img,prod_thumb_img,prod_design,prod_is_salable";

			$accessQuery .= "&fq=actual_price_".$storeId.":[".rawurlencode("1 TO *")."]";
			$accessQuery .= "&fq=!prod_en_id:".$this->_request->getParam('pid');

			$accessQuery .= "&fq=category_name_search:(".rawurlencode($m_accessoriesQuery).")";
			$accessQuery .= "&fq=color_name:(".rawurlencode($m_accessories_colorQuery).")";
			$accessQuery .= "&fq=gender_id:".$gender;
			$accessQuery .= "&fq=".$actualPriceField.":{*%20TO%20".$finalprice."}" ;


	    $randomNumber = rand(100, 999);
	    $accessQuery .= '&sort=' . rawurlencode('random_' . $randomNumber . ' asc');
	    $accessQuery .= "&start=0&rows=10";

	    $menAccessories = json_decode($this->_solrHelper->getFilterCollection($accessQuery), true);
	    $menAccessories = isset($menAccessories["response"]["docs"]) ? $menAccessories["response"]["docs"]: [];
	    // echo '<pre>';
	    // print_r($menAccessories);
	    // die;
	    $men_final_array = array_merge($menShoes,$menAccessories);
	    // echo '<pre>';
	    // print_r($men_final_array);
	    // die;
  //       $products = json_decode($filt_coll, true);
		// $products = isset($products["response"]["docs"]) ? $products["response"]["docs"] : array();
	    $filteredProductIds = array_column($men_final_array, 'prod_en_id');
	    // print_r($filteredProductIds);die;
	    $this->renderedProductIds = array_merge($this->renderedProductIds, $filteredProductIds);
	}
    return $men_final_array;
}
public function getpairItWithWomen($pId,$theme,$categoryIds,$colors,$gender,$finalprice)
{
	$women_final_array = array();
	$query = "SELECT * FROM pdp_widgets_product_pairings a  WHERE a.clothing_category_id IN ($categoryIds)  AND a.clothing_color_id IN ($colors)  AND a.theme_id IN ($theme) AND a.gender_id IN ($gender)";

    $resultArr = $this->_connection->fetchAll($query);
    if($resultArr){
	    $resultArr = $resultArr[0];
	    // echo '<pre>';
	    // print_r($resultArr);
	    // die;


	    $w_jwellery_catg = $resultArr['w_jwellery_catg'];
	    $w_jwellery_subcatg = $resultArr['w_jwellery_subcatg'];
	    $w_jwellery_color = $resultArr['w_jwellery_color'];	
	    $w_bag_catg = $resultArr['w_bag_catg'];	
	    $w_bag_color = $resultArr['w_bag_color'];	
	    $w_footwear_catg = $resultArr['w_footwear_catg'];	
	    $w_footwear_color = $resultArr['w_footwear_color'];

		$w_jwellery_catgArr = explode(',', $w_jwellery_catg);
	    if (is_array($w_jwellery_catgArr)) {
	        $w_jwellery_catgQuery = implode(' OR ', $w_jwellery_catgArr);
	    } else {
	        $w_jwellery_catgQuery = $w_jwellery_catg;
	    }

		$w_jwellery_subcatgArr = explode(',', $w_jwellery_subcatg);
	    if (is_array($w_jwellery_subcatgArr)) {
	        $w_jwellery_subcatgQuery = implode(' OR ', $w_jwellery_subcatgArr);
	    } else {
	        $w_jwellery_subcatgQuery = $w_jwellery_subcatg;
	    }

		$w_jwellery_colorArr = explode(',', $w_jwellery_color);
	    if (is_array($w_jwellery_colorArr)) {
	        $w_jwellery_colorQuery = implode(' OR ', $w_jwellery_colorArr);
	    } else {
	        $w_jwellery_colorQuery = $w_jwellery_color;
	    }
	    	$storeId = $this->_storeManager->getStore()->getId();
			$specialPriceField = "special_price_".$storeId;
			$actualPriceField = "actual_price_".$storeId;

			
			$jwelleryQuery = "q=*:*";
			$jwelleryQuery .= "&fl=actual_price:".$actualPriceField.",special_price:".$specialPriceField.",categories-store-".$storeId."_id,prod_en_id,prod_type,prod_sku,prod_plp_flag,prod_sd_flag,prod_sdm_flag,prod_rts_flag,enquire_".$storeId.",prod_exp_ship_flag,same_day_shipping_market,rts_flag_market,express_shipping_market,prod_name,short_desc,prod_url:prod_url_".$storeId.",prod_small_img,prod_thumb_img,prod_design,prod_is_salable";

			$jwelleryQuery .= "&fq=actual_price_".$storeId.":[".rawurlencode("1 TO *")."]";
			$jwelleryQuery .= "&fq=!prod_en_id:".$this->_request->getParam('pid');

			$jwelleryQuery .= "&fq=category_name_search:(".rawurlencode($w_jwellery_catgQuery).")";
			$jwelleryQuery .= "&fq=category_name_search:(".rawurlencode($w_jwellery_subcatgQuery).")";
			$jwelleryQuery .= "&fq=color_name:(".rawurlencode($w_jwellery_colorQuery).")";
			$jwelleryQuery .= "&fq=gender_id:".$gender;
			$jwelleryQuery .= "&fq=".$actualPriceField.":{*%20TO%20".$finalprice."}" ;


	    $randomNumber = rand(100, 999);
	    $jwelleryQuery .= '&sort=' . rawurlencode('random_' . $randomNumber . ' asc');
	    $jwelleryQuery .= "&start=0&rows=6";

	    $jwellery = json_decode($this->_solrHelper->getFilterCollection($jwelleryQuery), true);
	    $jwellery = isset($jwellery["response"]["docs"]) ? $jwellery["response"]["docs"]: [];
	    // echo '<pre>';
	    // print_r($jwellery);
	    // die;
	    // ----------------------------------------------------------------------
	    $w_bag_catgArr = explode(',', $w_bag_catg);
	    if (is_array($w_bag_catgArr)) {
	        $w_bag_catgQuery = implode(' OR ', $w_bag_catgArr);
	    } else {
	        $w_bag_catgQuery = $w_bag_catg;
	    }

	    $w_bag_colorArr = explode(',', $w_bag_color);
	    if (is_array($w_bag_colorArr)) {
	        $w_bag_colorQuery = implode(' OR ', $w_bag_colorArr);
	    } else {
	        $w_bag_colorQuery = $w_bag_color;
	    }
			$storeId = $this->_storeManager->getStore()->getId();
			$specialPriceField = "special_price_".$storeId;
			$actualPriceField = "actual_price_".$storeId;
			
			$bagQuery = "q=*:*";
			$bagQuery .= "&fl=actual_price:".$actualPriceField.",special_price:".$specialPriceField.",categories-store-".$storeId."_id,prod_en_id,prod_type,prod_sku,prod_plp_flag,prod_sd_flag,prod_sdm_flag,prod_rts_flag,enquire_".$storeId.",prod_exp_ship_flag,same_day_shipping_market,rts_flag_market,express_shipping_market,prod_name,short_desc,prod_url:prod_url_".$storeId.",prod_small_img,prod_thumb_img,prod_design,prod_is_salable";

			$bagQuery .= "&fq=actual_price_".$storeId.":[".rawurlencode("1 TO *")."]";
			$bagQuery .= "&fq=!prod_en_id:".$this->_request->getParam('pid');

			$bagQuery .= "&fq=category_name_search:(".rawurlencode($w_bag_catgQuery).")";
			$bagQuery .= "&fq=color_name:(".rawurlencode($w_bag_colorQuery).")";
			$bagQuery .= "&fq=gender_id:".$gender;
			$bagQuery .= "&fq=".$actualPriceField.":{*%20TO%20".$finalprice."}" ;



	    $randomNumber = rand(100, 999);
	    $bagQuery .= '&sort=' . rawurlencode('random_' . $randomNumber . ' asc');
	    $bagQuery .= "&start=0&rows=4";

	    $womenBag = json_decode($this->_solrHelper->getFilterCollection($bagQuery), true);
	    $womenBag = isset($womenBag["response"]["docs"]) ? $womenBag["response"]["docs"]: [];
	    // echo '<pre>';
	    // print_r($womenBag);
	    // die;

	    // ----------------------------------------------------------------------
	    $w_footwear_catgArr = explode(',', $w_footwear_catg);
	    if (is_array($w_footwear_catgArr)) {
	        $w_footwear_catgQuery = implode(' OR ', $w_footwear_catgArr);
	    } else {
	        $w_footwear_catgQuery = $w_footwear_catg;
	    }

	    $w_footwear_colorArr = explode(',', $w_footwear_color);
	    if (is_array($w_footwear_colorArr)) {
	        $w_footwear_colorQuery = implode(' OR ', $w_footwear_colorArr);
	    } else {
	        $w_footwear_colorQuery = $w_footwear_color;
	    }
			$storeId = $this->_storeManager->getStore()->getId();
			$specialPriceField = "special_price_".$storeId;
			$actualPriceField = "actual_price_".$storeId;

			$footwearQuery = "q=*:*";
			$footwearQuery .= "&fl=actual_price:".$actualPriceField.",special_price:".$specialPriceField.",categories-store-".$storeId."_id,prod_en_id,prod_type,prod_sku,prod_plp_flag,prod_sd_flag,prod_sdm_flag,prod_rts_flag,enquire_".$storeId.",prod_exp_ship_flag,same_day_shipping_market,rts_flag_market,express_shipping_market,prod_name,short_desc,prod_url:prod_url_".$storeId.",prod_small_img,prod_thumb_img,prod_design,prod_is_salable";

			$footwearQuery .= "&fq=actual_price_".$storeId.":[".rawurlencode("1 TO *")."]";
			$footwearQuery .= "&fq=!prod_en_id:".$this->_request->getParam('pid');

			$footwearQuery .= "&fq=category_name_search:(".rawurlencode($w_footwear_catgQuery).")";
			$footwearQuery .= "&fq=color_name:(".rawurlencode($w_footwear_colorQuery).")";
			$footwearQuery .= "&fq=gender_id:".$gender;
			$footwearQuery .= "&fq=".$actualPriceField.":{*%20TO%20".$finalprice."}" ;



	    $randomNumber = rand(100, 999);
	    $footwearQuery .= '&sort=' . rawurlencode('random_' . $randomNumber . ' asc');
	    $footwearQuery .= "&start=0&rows=5";

	    $womenfootwear = json_decode($this->_solrHelper->getFilterCollection($footwearQuery), true);
	    $womenfootwear = isset($womenfootwear["response"]["docs"]) ? $womenfootwear["response"]["docs"]: [];
	    // echo '<pre>';
	    // print_r($womenfootwear);
	    // die;
	    // ----------------------------------------------------------------------------
	    $women_final_array = array_merge($jwellery,$womenBag,$womenfootwear);
	    // echo '<pre>';
	    // print_r($women_final_array);
	    // die;
	  	$filteredProductIds = array_column($women_final_array, 'prod_en_id');
	    // print_r($filteredProductIds);die;
	    $this->renderedProductIds = array_merge($this->renderedProductIds, $filteredProductIds);
	}
    return $women_final_array;
}

public function getpairItWithHtml($pId,$theme,$categoryIds,$colors,$gender,$finalprice)
{
    $result =  array('count' => 0, 'reshtml' =>'');
    $productsData = array();

    if ($gender == 6301) {
    	$productsData = $this->getpairItWithMen($pId,$theme,$categoryIds,$colors,$gender,$finalprice);
    } elseif ($gender == 6302) {
    	$productsData = $this->getpairItWithWomen($pId,$theme,$categoryIds,$colors,$gender,$finalprice);
    }
    
    // $productsArr = isset($productsData) ? $productsData : [];
    // echo '<pre>';
    // print_r($productsArr);
    // die;
    if ($productsArrCount = (count($productsData) > 0) ? count($productsData) : 0){



	    // $productsArrCount = isset($productsData["response"]["numFound"]) ? $productsData["response"]["numFound"] : 0;

	    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	    $formKey = $this->_formKey->getFormKey();
	    $urlParamName = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;
	    $uenc = $this->_cartHelper->getProdUencForWidget($this->_request->getParam('pdp_url'));

	    $itemList = '
	    <div>
	        <div class="block widget block-suggested-products grid">
	            <div class="block-content">
	                <div class="products-grid grid">
	                    <ol id="pdp_slider_pait_it_with" class="owl-carousel owl-theme">';

	    foreach ($productsData as $prod) {
	        $pricehtml = '';
	        $enquirenow = isset($prod['enquire_1'][0]) ? $prod['enquire_1'][0] : '';
	        $prodUrl = isset($prod["prod_url"][0]) ? $prod["prod_url"][0] : '';
	        $prodUrl = preg_replace('/&?___store=[^&]*/', '', $prodUrl);
	        $prodUrl = str_replace('?', '', $prodUrl);
	        $smallImage = isset($prod["prod_small_img"]) ? $prod["prod_small_img"] : '';
	        $thumbnailImage = isset($prod["prod_thumb_img"]) ? $prod["prod_thumb_img"] : '';
	        $name = isset($prod['prod_name'][0]) ? $prod['prod_name'][0] : '';
	        $prodId = isset($prod['prod_en_id']) ? $prod['prod_en_id'] : '';
	        $routeParams = [
	            $urlParamName => $uenc,
	            'product' => $prodId,
	            '_secure' => 1
	        ];
	        $addToCartUrl = $this->_cartHelper->getProdCartUrl($routeParams);
	        $designerName = isset($prod['prod_design']) ? $prod['prod_design'] : '';
	        $shortDesc = isset($prod['short_desc']) ? $prod['short_desc'] : '';
	        $enquirehtml = '';

	        if ($enquirenow == 1) {
	            $enquirehtml = '<span id="modal-btn" style="padding: 5px 25px; position: relative; top: 8px; border:1px #000 solid;" data-toggle="modal" data-book-id=' . $prod['prod_sku'] . '>Enquire Now</span>';
	        } else {
	            $specialPrice = $this->getPrice(isset($prod["actual_price"]) ? $prod["actual_price"] : 0);
	            if ((isset($prod['special_price']) && isset($prod['actual_price'])) && ($prod['special_price'] < $prod['actual_price'])) {
	                $actualPrice = $prod['actual_price'];
	                $specialPrice = $prod['special_price'];
	                $specialPrice = $this->getPrice($specialPrice);
	                $actualPrice = $this->getPrice($actualPrice);
	                $pricehtml = '
	                <div class="price-box price-final_price" data-role="priceBox" data-product-id="227330" data-price-box="product-id-227330">
	                    <span class="normal-price">
	                        <span class="price-container price-final_price tax weee">
	                            <span id="product-price-227330" data-price-amount="35200" data-price-type="finalPrice" class="price-wrapper ">
	                                <span class="price">' . $specialPrice . '</span>
	                            </span>
	                        </span>
	                    </span>
	                    <span class="old-price sly-old-price">
	                        <span class="price-container price-final_price tax weee">
	                            <span class="price-label">Regular Price</span>
	                            <span id="old-price-227330" data-price-amount="44000" data-price-type="oldPrice" class="price-wrapper ">
	                                <span class="price">' . $actualPrice . '</span>
	                            </span>
	                        </span>
	                    </span>
	                </div>';
	            } else {
	                $actualPrice = $this->getPrice(isset($prod["actual_price"]) ? $prod["actual_price"] : 0);
	                $pricehtml = '
	                <div class="price-box price-final_price" data-role="priceBox" data-product-id="232055" data-price-box="product-id-232055">
	                    <span class="normal-price">
	                        <span class="price-container price-final_price tax weee">
	                            <span id="product-price-232055" data-price-amount="42000" data-price-type="finalPrice" class="price-wrapper ">
	                                <span class="price">' . $actualPrice . '</span>
	                            </span>
	                        </span>
	                    </span>
	                </div>';
	            }
	        }

	        $categoryRepository = $objectManager->get('\Magento\Catalog\Api\CategoryRepositoryInterface');
	        $parent = $objectManager->get('Magento\Catalog\Model\Product')->load($prodId);
	        $cats = $parent->getCategoryIds();
	        $category_name = '';
	        $category_id = isset($cats[0]) ? $cats[0] : 0;

	        if ($category_id) {
	            try {
	                $category = $categoryRepository->get($category_id);
	                $category_name = $category->getName();
	            } catch (Exception $e) {
	                $category_name = 'NA';
	            }
	        }

	        $actionLinkHtml = '';

	        if ($enquirenow != 1) {
	            $actionLinkHtml = '
	            <ul class="actions-link" data-role="add-to-links">
	                <li class="hidden-sm hidden-xs">
	                    <button data-title="Quick View" class="action mgs-quickview" data-quickview-url="/mgs_quickview/catalog_product/view/id/' . $prodId . '/" title="Quick View">
	                        <span class="fa fa-eye"></span>
	                    </button>
	                </li>
	                <li>
	                    <button data-title="Add to Wish List" id="add_to_wish_ymal" class="action towishlist" title="Add to Wish List" aria-label="Add to Wish List" data-post="{&quot;action&quot;:&quot;https:\/\/aashniandco.com\/wishlist\/index\/add\/&quot;,&quot;data&quot;:{&quot;product&quot;:' . $prodId . ',&quot;uenc&quot;:&quot;' . $uenc . '&quot;}}" data-action="add-to-wishlist" role="button" data-sku="' . $prod['prod_sku'] . '" data-name="' . $shortDesc . '" data-brand="' . $designerName . '" data-oldPrice="' . $actualPrice . '" data-value="' . $specialPrice . '" data-category="' . $category_name . '">
	                        <i class="fa fa-heart"></i>
	                    </button>
	                </li>
	                <li>
	                    <button data-title="Add to Compare" class="action tocompare" title="Add to Compare" aria-label="Add to Compare" data-post="{&quot;action&quot;:&quot;https:\/\/aashniandco.com\/catalog\/product_compare\/add\/&quot;,&quot;data&quot;:{&quot;product&quot;:&quot;' . $prodId . '&quot;,&quot;uenc&quot;:&quot;' . $uenc . '&quot;}}" role="button">
	                        <i class="fa fa-retweet"></i>
	                    </button>
	                </li>
	                <li>
	                    <form data-role="tocart-form" action="' . $addToCartUrl . '" method="post" novalidate="novalidate">
	                        <input type="hidden" name="product" value="' . $prodId . '">
	                        <input type="hidden" name="uenc" value="' . $uenc . '">
	                        <input name="form_key" type="hidden" value="' . $formKey . '">
	                        <button type="submit" title="Add to Cart" class="action tocart">
	                            <span class="fa fa-shopping-cart"></span>
	                        </button>
	                    </form>
	                </li>
	            </ul>';
	        }

	        $itemList .= '
	        <li class="product-item-info item">
	            <div class="product-top">
	                <a href="' . $prodUrl . '" class="product-item-photo utm_class">
	                    <img width="100px" src="' . $smallImage . '" alt="' . $name . '">
	                </a>
	                ' . $actionLinkHtml . '
	                <div class="product-item-details">
	                    <h4 class="product-item-name">
	                        <a title="' . $name . '" href="' . $prodUrl . '" class="product-item-link utm_class">' . $name . '</a>
	                    </h4>
	                    <h4 class="product description product-item-description">' . $shortDesc . '</h4>
	                    ' . $pricehtml . '
	                </div>
	            </div>
	            ' . $enquirehtml . '
	        </li>';
	    }

	    $itemList .= '
	                    </ol>
	                </div>
	            </div>
	        </div>
	    </div>';

	    $css = '
	    <style>
	        #pdp_sliderrelated { text-align: center; }
	        #pdp_slider_pait_it_with { text-align: center; }
	        .product-items { display: flex; }
	        .product-item { padding: 5px; text-align: center; }
	        .you_likebtn { background: #000; border: 1px #000 solid; padding: 5px 15px; margin-top: 10px; color: #fff; }
	        #pdp_slider_pait_it_with .price-label { color: #000; }
	        .owl-carousel .owl-nav.disabled { display: block !important; }
	        .owl-carousel .owl-nav button { background: #000 !important; width: 35px; height: 35px; }
	        .owl-carousel .owl-nav button span { font-size: 41px !important; color: #fff; line-height: 31px; }
	        .owl-carousel .owl-nav button.owl-prev { position: absolute; z-index: 1; top: 40%; left: 0px; }
	        .owl-carousel .owl-nav button.owl-next { position: absolute; z-index: 1; top: 40%; right: 0px; }
	        @media only screen and (max-width: 619px) { 
			#block-related-heading {
			    font-size: 20px;
			}
	    </style>';

	    $js = '
	    <script type="text/javascript">
	        require([\'jquery\', \'mgs/owlcarousel\'], function(jQuery) {
	            (function($) {
	                $("#pdp_slider_pait_it_with").owlCarousel({
	                    loop: false,
	                    rewind: true,
	                    items: 4,
	                    nav: true,
	                    margin: 10,
	                    dots: false,
	                    autoplay: false,
	                    autoplayTimeout: 5000,
	                    autoplayHoverPause: false,
	                    loop: true,
	                    navText: ["<i class=\'fa fa-arrow-left\'></i>", "<i class=\'fa fa-arrow-right\'></i>"],
	                    responsive: {
	                        0: { items: 2 },
	                        480: { items: 2 },
	                        768: { items: 4 },
	                        980: { items: 4 }
	                    }
	                });
	            })(jQuery);
	        });
	    </script>';
	    $result =  array('count' => $productsArrCount, 'reshtml' =>$css . $itemList . $js);
	}
    return $result;

    // return  $css . $itemList . $js;
}

public function getSimilarColor($pId,$gender,$colors,$kidsId, $categoryIds)
{
	// var_dump($gender);
	// if(isset($gender) && !empty($gender)){
	// 		echo  "&fq=gender_id:".$gender;
 //    	}
 //    die;
    $colorsArr = explode(',', $colors);
    if(count($colorsArr) <= 0){
    	return false;
    }

    if (is_array($colorsArr)) {
        $colorsQuery = implode(' OR ', $colorsArr);
    } else {
        $colorsQuery = $colors;
    }

    $catFilter = array();
    $catArr = explode(',', $categoryIds);
    if(in_array(1374, $catArr)){
    	array_push($catFilter, 1374);
    }
    if(in_array(4069, $catArr)){
    	array_push($catFilter, 4069);
    }
	
    $storeId = $this->_storeManager->getStore()->getId();
	$specialPriceField = "special_price_".$storeId;
	$actualPriceField = "actual_price_".$storeId;
		
		$query = "q=color_id:(".rawurlencode($colorsQuery).")";
		$query .= "&fl=actual_price:".$actualPriceField.",special_price:".$specialPriceField.",categories-store-".$storeId."_id,prod_en_id,prod_type,prod_sku,prod_plp_flag,prod_sd_flag,prod_sdm_flag,prod_rts_flag,enquire_".$storeId.",prod_exp_ship_flag,same_day_shipping_market,rts_flag_market,express_shipping_market,prod_name,short_desc,prod_url:prod_url_".$storeId.",prod_small_img,prod_thumb_img,prod_design,prod_is_salable";

		$query .= "&fq=actual_price_".$storeId.":[".rawurlencode("1 TO *")."]";
		$query .= "&fq=!prod_en_id:".$this->_request->getParam('pid');
		if(isset($gender) && !empty($gender)){
			$query .= "&fq=gender_id:".$gender;
    	}
    	if(isset($kidsId) && !empty($kidsId)){
			$query .= "&fq=kid_id:".$kidsId;
    	}
    	if(count($catFilter) > 0){
    		$query .= "&fq=categories-store-".$storeId."_id:".rawurlencode(implode(' OR ', $catFilter));
    	}
    	// Exclude already rendered products
        if (!empty($this->renderedProductIds)) {
            $excludedIds = implode(' OR ', $this->renderedProductIds);
            $query .= "&fq=!prod_en_id_int:(" . rawurlencode($excludedIds) . ")";
            //echo $query;die;
        }

    $randomNumber = rand(100, 999);
    $query .= '&sort=' . rawurlencode('random_' . $randomNumber . ' asc');
    $query .= "&start=0&rows=16";

    //echo $query;die;
    $filt_coll = $this->_solrHelper->getFilterCollection($query);
    $products = json_decode($filt_coll, true);
	$products = isset($products["response"]["docs"]) ? $products["response"]["docs"] : array();
    $filteredProductIds = array_column($products, 'prod_en_id');
    $this->renderedProductIds = array_merge($this->renderedProductIds, $filteredProductIds);

    return json_decode($filt_coll, true);
}

public function getSimilarColorHtml($pId,$gender,$colors,$kidsId, $categoryIds)
{
    // Fetch products from Solr
    $productsData = $this->getSimilarColor($pId,$gender,$colors,$kidsId, $categoryIds);
    $productsArr = isset($productsData["response"]["docs"]) ? $productsData["response"]["docs"] : [];
    $productsArrCount = isset($productsData["response"]["numFound"]) ? $productsData["response"]["numFound"] : 0;

    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $formKey = $this->_formKey->getFormKey();
    $urlParamName = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;
    $uenc = $this->_cartHelper->getProdUencForWidget($this->_request->getParam('pdp_url'));

    $itemList = '
    <div>
        <div class="block widget block-suggested-products grid">
            <div class="block-content">
                <div class="products-grid grid">
                    <ol id="pdp_slider_sim_color" class="owl-carousel owl-theme">';

    foreach ($productsArr as $prod) {
        $pricehtml = '';
        $enquirenow = isset($prod['enquire_1'][0]) ? $prod['enquire_1'][0] : '';
        $prodUrl = isset($prod["prod_url"][0]) ? $prod["prod_url"][0] : '';
        $prodUrl = preg_replace('/&?___store=[^&]*/', '', $prodUrl);
        $prodUrl = str_replace('?', '', $prodUrl);
        $smallImage = isset($prod["prod_small_img"]) ? $prod["prod_small_img"] : '';
        $thumbnailImage = isset($prod["prod_thumb_img"]) ? $prod["prod_thumb_img"] : '';
        $name = isset($prod['prod_name'][0]) ? $prod['prod_name'][0] : '';
        $prodId = isset($prod['prod_en_id']) ? $prod['prod_en_id'] : '';
        $routeParams = [
            $urlParamName => $uenc,
            'product' => $prodId,
            '_secure' => 1
        ];
        $addToCartUrl = $this->_cartHelper->getProdCartUrl($routeParams);
        $designerName = isset($prod['prod_design']) ? $prod['prod_design'] : '';
        $shortDesc = isset($prod['short_desc']) ? $prod['short_desc'] : '';
        $enquirehtml = '';

        if ($enquirenow == 1) {
            $enquirehtml = '<span id="modal-btn" style="padding: 5px 25px; position: relative; top: 8px; border:1px #000 solid;" data-toggle="modal" data-book-id=' . $prod['prod_sku'] . '>Enquire Now</span>';
        } else {
            $specialPrice = $this->getPrice(isset($prod["actual_price"]) ? $prod["actual_price"] : 0);
            if ((isset($prod['special_price']) && isset($prod['actual_price'])) && ($prod['special_price'] < $prod['actual_price'])) {
                $actualPrice = $prod['actual_price'];
                $specialPrice = $prod['special_price'];
                $specialPrice = $this->getPrice($specialPrice);
                $actualPrice = $this->getPrice($actualPrice);
                $pricehtml = '
                <div class="price-box price-final_price" data-role="priceBox" data-product-id="227330" data-price-box="product-id-227330">
                    <span class="normal-price">
                        <span class="price-container price-final_price tax weee">
                            <span id="product-price-227330" data-price-amount="35200" data-price-type="finalPrice" class="price-wrapper ">
                                <span class="price">' . $specialPrice . '</span>
                            </span>
                        </span>
                    </span>
                    <span class="old-price sly-old-price">
                        <span class="price-container price-final_price tax weee">
                            <span class="price-label">Regular Price</span>
                            <span id="old-price-227330" data-price-amount="44000" data-price-type="oldPrice" class="price-wrapper ">
                                <span class="price">' . $actualPrice . '</span>
                            </span>
                        </span>
                    </span>
                </div>';
            } else {
                $actualPrice = $this->getPrice(isset($prod["actual_price"]) ? $prod["actual_price"] : 0);
                $pricehtml = '
                <div class="price-box price-final_price" data-role="priceBox" data-product-id="232055" data-price-box="product-id-232055">
                    <span class="normal-price">
                        <span class="price-container price-final_price tax weee">
                            <span id="product-price-232055" data-price-amount="42000" data-price-type="finalPrice" class="price-wrapper ">
                                <span class="price">' . $actualPrice . '</span>
                            </span>
                        </span>
                    </span>
                </div>';
            }
        }

        $categoryRepository = $objectManager->get('\Magento\Catalog\Api\CategoryRepositoryInterface');
        $parent = $objectManager->get('Magento\Catalog\Model\Product')->load($prodId);
        $cats = $parent->getCategoryIds();
        $category_name = '';
        $category_id = isset($cats[0]) ? $cats[0] : 0;

        if ($category_id) {
            try {
                $category = $categoryRepository->get($category_id);
                $category_name = $category->getName();
            } catch (Exception $e) {
                $category_name = 'NA';
            }
        }

        $actionLinkHtml = '';

        if ($enquirenow != 1) {
            $actionLinkHtml = '
            <ul class="actions-link" data-role="add-to-links">
                <li class="hidden-sm hidden-xs">
                    <button data-title="Quick View" class="action mgs-quickview" data-quickview-url="/mgs_quickview/catalog_product/view/id/' . $prodId . '/" title="Quick View">
                        <span class="fa fa-eye"></span>
                    </button>
                </li>
                <li>
                    <button data-title="Add to Wish List" id="add_to_wish_ymal" class="action towishlist" title="Add to Wish List" aria-label="Add to Wish List" data-post="{&quot;action&quot;:&quot;https:\/\/aashniandco.com\/wishlist\/index\/add\/&quot;,&quot;data&quot;:{&quot;product&quot;:' . $prodId . ',&quot;uenc&quot;:&quot;' . $uenc . '&quot;}}" data-action="add-to-wishlist" role="button" data-sku="' . $prod['prod_sku'] . '" data-name="' . $shortDesc . '" data-brand="' . $designerName . '" data-oldPrice="' . $actualPrice . '" data-value="' . $specialPrice . '" data-category="' . $category_name . '">
                        <i class="fa fa-heart"></i>
                    </button>
                </li>
                <li>
                    <button data-title="Add to Compare" class="action tocompare" title="Add to Compare" aria-label="Add to Compare" data-post="{&quot;action&quot;:&quot;https:\/\/aashniandco.com\/catalog\/product_compare\/add\/&quot;,&quot;data&quot;:{&quot;product&quot;:&quot;' . $prodId . '&quot;,&quot;uenc&quot;:&quot;' . $uenc . '&quot;}}" role="button">
                        <i class="fa fa-retweet"></i>
                    </button>
                </li>
                <li>
                    <form data-role="tocart-form" action="' . $addToCartUrl . '" method="post" novalidate="novalidate">
                        <input type="hidden" name="product" value="' . $prodId . '">
                        <input type="hidden" name="uenc" value="' . $uenc . '">
                        <input name="form_key" type="hidden" value="' . $formKey . '">
                        <button type="submit" title="Add to Cart" class="action tocart">
                            <span class="fa fa-shopping-cart"></span>
                        </button>
                    </form>
                </li>
            </ul>';
        }

        $itemList .= '
        <li class="product-item-info item">
            <div class="product-top">
                <a href="' . $prodUrl . '" class="product-item-photo utm_class">
                    <img width="100px" src="' . $smallImage . '" alt="' . $name . '">
                </a>
                ' . $actionLinkHtml . '
                <div class="product-item-details">
                    <h4 class="product-item-name">
                        <a title="' . $name . '" href="' . $prodUrl . '" class="product-item-link utm_class">' . $name . '</a>
                    </h4>
                    <h4 class="product description product-item-description">' . $shortDesc . '</h4>
                    ' . $pricehtml . '
                </div>
            </div>
            ' . $enquirehtml . '
        </li>';
    }

    $itemList .= '
                    </ol>
                </div>
            </div>
        </div>
    </div>';

    $css = '
    <style>
        #pdp_sliderrelated { text-align: center; }
        #pdp_slider_sim_color { text-align: center; }
        .product-items { display: flex; }
        .product-item { padding: 5px; text-align: center; }
        .you_likebtn { background: #000; border: 1px #000 solid; padding: 5px 15px; margin-top: 10px; color: #fff; }
        #pdp_slider_sim_color .price-label { color: #000; }
        .owl-carousel .owl-nav.disabled { display: block !important; }
        .owl-carousel .owl-nav button { background: #000 !important; width: 35px; height: 35px; }
        .owl-carousel .owl-nav button span { font-size: 41px !important; color: #fff; line-height: 31px; }
        .owl-carousel .owl-nav button.owl-prev { position: absolute; z-index: 1; top: 40%; left: 0px; }
        .owl-carousel .owl-nav button.owl-next { position: absolute; z-index: 1; top: 40%; right: 0px; }
        @media only screen and (max-width: 619px) { 
		#block-related-heading {
		    font-size: 20px;
		}
    </style>';

    $js = '
    <script type="text/javascript">
        require([\'jquery\', \'mgs/owlcarousel\'], function(jQuery) {
            (function($) {
                $("#pdp_slider_sim_color").owlCarousel({
                    loop: ' . ($productsArrCount > 1 ? 'true' : 'false') . ',
                    rewind: true,
                    items: 4,
                    nav: true,
                    margin: 10,
                    dots: false,
                    autoplay: false,
                    autoplayTimeout: 5000,
                    autoplayHoverPause: false,
                    navText: ["<i class=\'fa fa-arrow-left\'></i>", "<i class=\'fa fa-arrow-right\'></i>"],
                    responsive: {
                        0: { items: 2 },
                        480: { items: 2 },
                        768: { items: 4 },
                        980: { items: 4 }
                    }
                });
            })(jQuery);
        });
    </script>';
    $result =  array('count' => $productsArrCount, 'reshtml' =>$css . $itemList . $js);
    return $result;
    // return  $css . $itemList . $js;
}

public function getNewArrivals($categoryIds, $gender)
{
    //$catFilter = array();
    $catArr = explode(',', $categoryIds);
    //$catFilter = $catArr;
    $mainCatArr = array(3374,1374,1381,1380,6023);

    $catToShow = array_intersect($catArr,$mainCatArr);
    // if(in_array(1374, $catArr)){
    // 	array_push($catFilter, 1374);
    // }
    // if(in_array(4069, $catArr)){
    // 	array_push($catFilter, 4069);
    // }
	
    $storeId = $this->_storeManager->getStore()->getId();
	$specialPriceField = "special_price_".$storeId;
	$actualPriceField = "actual_price_".$storeId;
		
		$query = "q=categories-store-".$storeId."_id:".rawurlencode(implode(' OR ', $catToShow));
		$query .= "&fl=actual_price:".$actualPriceField.",special_price:".$specialPriceField.",categories-store-".$storeId."_id,prod_en_id,prod_type,prod_sku,prod_plp_flag,prod_sd_flag,prod_sdm_flag,prod_rts_flag,enquire_".$storeId.",prod_exp_ship_flag,same_day_shipping_market,rts_flag_market,express_shipping_market,prod_name,short_desc,prod_url:prod_url_".$storeId.",prod_small_img,prod_thumb_img,prod_design,prod_is_salable";

		$query .= "&fq=actual_price_".$storeId.":[".rawurlencode("1 TO *")."]";
		$query .= "&fq=!prod_en_id:".$this->_request->getParam('pid');
		if(isset($gender) && !empty($gender)){
			$query .= "&fq=gender_id:".$gender;
    	}
    	
    	// Exclude already rendered products
        if (!empty($this->renderedProductIds)) {
            $excludedIds = implode(' OR ', $this->renderedProductIds);
            $query .= "&fq=!prod_en_id_int:(" . rawurlencode($excludedIds) . ")";
            //echo $query;die;
        }

    $randomNumber = rand(100, 999);
    $query .= '&sort=' . rawurlencode('prod_en_id_int desc');
    $query .= "&start=0&rows=15";

    //echo $query;die;
    //$query = "q=*:*";
    $filt_coll = $this->_solrHelper->getFilterCollection($query);
    $products = json_decode($filt_coll, true);
	$products = isset($products["response"]["docs"]) ? $products["response"]["docs"] : array();
	//echo '<pre>';print_r($products);die;
    $filteredProductIds = array_column($products, 'prod_en_id');
    $this->renderedProductIds = array_merge($this->renderedProductIds, $filteredProductIds);

    return json_decode($filt_coll, true);
}

public function getNewArrivalsHtml($categoryIds, $gender)
{
    // Fetch products from Solr
    $productsData = $this->getNewArrivals($categoryIds, $gender);
    $productsArr = isset($productsData["response"]["docs"]) ? $productsData["response"]["docs"] : [];
    $productsArrCount = isset($productsData["response"]["numFound"]) ? $productsData["response"]["numFound"] : 0;

    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $formKey = $this->_formKey->getFormKey();
    $urlParamName = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;
    $uenc = $this->_cartHelper->getProdUencForWidget($this->_request->getParam('pdp_url'));

    $itemList = '
    <div>
        <div class="block widget block-suggested-products grid">
            <div class="block-content">
                <div class="products-grid grid">
                    <ol id="pdp_slider_new_arrivals" class="owl-carousel owl-theme">';

    foreach ($productsArr as $prod) {
        $pricehtml = '';
        $enquirenow = isset($prod['enquire_1'][0]) ? $prod['enquire_1'][0] : '';
        $prodUrl = isset($prod["prod_url"][0]) ? $prod["prod_url"][0] : '';
        $prodUrl = preg_replace('/&?___store=[^&]*/', '', $prodUrl);
        $prodUrl = str_replace('?', '', $prodUrl);
        $smallImage = isset($prod["prod_small_img"]) ? $prod["prod_small_img"] : '';
        $thumbnailImage = isset($prod["prod_thumb_img"]) ? $prod["prod_thumb_img"] : '';
        $name = isset($prod['prod_name'][0]) ? $prod['prod_name'][0] : '';
        $prodId = isset($prod['prod_en_id']) ? $prod['prod_en_id'] : '';
        $routeParams = [
            $urlParamName => $uenc,
            'product' => $prodId,
            '_secure' => 1
        ];
        $addToCartUrl = $this->_cartHelper->getProdCartUrl($routeParams);
        $designerName = isset($prod['prod_design']) ? $prod['prod_design'] : '';
        $shortDesc = isset($prod['short_desc']) ? $prod['short_desc'] : '';
        $enquirehtml = '';

        if ($enquirenow == 1) {
            $enquirehtml = '<span id="modal-btn" style="padding: 5px 25px; position: relative; top: 8px; border:1px #000 solid;" data-toggle="modal" data-book-id=' . $prod['prod_sku'] . '>Enquire Now</span>';
        } else {
            $specialPrice = $this->getPrice(isset($prod["actual_price"]) ? $prod["actual_price"] : 0);
            if ((isset($prod['special_price']) && isset($prod['actual_price'])) && ($prod['special_price'] < $prod['actual_price'])) {
                $actualPrice = $prod['actual_price'];
                $specialPrice = $prod['special_price'];
                $specialPrice = $this->getPrice($specialPrice);
                $actualPrice = $this->getPrice($actualPrice);
                $pricehtml = '
                <div class="price-box price-final_price" data-role="priceBox" data-product-id="227330" data-price-box="product-id-227330">
                    <span class="normal-price">
                        <span class="price-container price-final_price tax weee">
                            <span id="product-price-227330" data-price-amount="35200" data-price-type="finalPrice" class="price-wrapper ">
                                <span class="price">' . $specialPrice . '</span>
                            </span>
                        </span>
                    </span>
                    <span class="old-price sly-old-price">
                        <span class="price-container price-final_price tax weee">
                            <span class="price-label">Regular Price</span>
                            <span id="old-price-227330" data-price-amount="44000" data-price-type="oldPrice" class="price-wrapper ">
                                <span class="price">' . $actualPrice . '</span>
                            </span>
                        </span>
                    </span>
                </div>';
            } else {
                $actualPrice = $this->getPrice(isset($prod["actual_price"]) ? $prod["actual_price"] : 0);
                $pricehtml = '
                <div class="price-box price-final_price" data-role="priceBox" data-product-id="232055" data-price-box="product-id-232055">
                    <span class="normal-price">
                        <span class="price-container price-final_price tax weee">
                            <span id="product-price-232055" data-price-amount="42000" data-price-type="finalPrice" class="price-wrapper ">
                                <span class="price">' . $actualPrice . '</span>
                            </span>
                        </span>
                    </span>
                </div>';
            }
        }

        $categoryRepository = $objectManager->get('\Magento\Catalog\Api\CategoryRepositoryInterface');
        $parent = $objectManager->get('Magento\Catalog\Model\Product')->load($prodId);
        $cats = $parent->getCategoryIds();
        $category_name = '';
        $category_id = isset($cats[0]) ? $cats[0] : 0;

        if ($category_id) {
            try {
                $category = $categoryRepository->get($category_id);
                $category_name = $category->getName();
            } catch (Exception $e) {
                $category_name = 'NA';
            }
        }

        $actionLinkHtml = '';

        if ($enquirenow != 1) {
            $actionLinkHtml = '
            <ul class="actions-link" data-role="add-to-links">
                <li class="hidden-sm hidden-xs">
                    <button data-title="Quick View" class="action mgs-quickview" data-quickview-url="/mgs_quickview/catalog_product/view/id/' . $prodId . '/" title="Quick View">
                        <span class="fa fa-eye"></span>
                    </button>
                </li>
                <li>
                    <button data-title="Add to Wish List" id="add_to_wish_ymal" class="action towishlist" title="Add to Wish List" aria-label="Add to Wish List" data-post="{&quot;action&quot;:&quot;https:\/\/aashniandco.com\/wishlist\/index\/add\/&quot;,&quot;data&quot;:{&quot;product&quot;:' . $prodId . ',&quot;uenc&quot;:&quot;' . $uenc . '&quot;}}" data-action="add-to-wishlist" role="button" data-sku="' . $prod['prod_sku'] . '" data-name="' . $shortDesc . '" data-brand="' . $designerName . '" data-oldPrice="' . $actualPrice . '" data-value="' . $specialPrice . '" data-category="' . $category_name . '">
                        <i class="fa fa-heart"></i>
                    </button>
                </li>
                <li>
                    <button data-title="Add to Compare" class="action tocompare" title="Add to Compare" aria-label="Add to Compare" data-post="{&quot;action&quot;:&quot;https:\/\/aashniandco.com\/catalog\/product_compare\/add\/&quot;,&quot;data&quot;:{&quot;product&quot;:&quot;' . $prodId . '&quot;,&quot;uenc&quot;:&quot;' . $uenc . '&quot;}}" role="button">
                        <i class="fa fa-retweet"></i>
                    </button>
                </li>
                <li>
                    <form data-role="tocart-form" action="' . $addToCartUrl . '" method="post" novalidate="novalidate">
                        <input type="hidden" name="product" value="' . $prodId . '">
                        <input type="hidden" name="uenc" value="' . $uenc . '">
                        <input name="form_key" type="hidden" value="' . $formKey . '">
                        <button type="submit" title="Add to Cart" class="action tocart">
                            <span class="fa fa-shopping-cart"></span>
                        </button>
                    </form>
                </li>
            </ul>';
        }

        $itemList .= '
        <li class="product-item-info item">
            <div class="product-top">
                <a href="' . $prodUrl . '" class="product-item-photo utm_class">
                    <img width="100px" src="' . $smallImage . '" alt="' . $name . '">
                </a>
                ' . $actionLinkHtml . '
                <div class="product-item-details">
                    <h4 class="product-item-name">
                        <a title="' . $name . '" href="' . $prodUrl . '" class="product-item-link utm_class">' . $name . '</a>
                    </h4>
                    <h4 class="product description product-item-description">' . $shortDesc . '</h4>
                    ' . $pricehtml . '
                </div>
            </div>
            ' . $enquirehtml . '
        </li>';
    }

    $itemList .= '
                    </ol>
                </div>
            </div>
        </div>
    </div>';

    $css = '
    <style>
        #pdp_sliderrelated { text-align: center; }
        #pdp_slider_new_arrivals { text-align: center; }
        .product-items { display: flex; }
        .product-item { padding: 5px; text-align: center; }
        .you_likebtn { background: #000; border: 1px #000 solid; padding: 5px 15px; margin-top: 10px; color: #fff; }
        #pdp_slider_new_arrivals .price-label { color: #000; }
        .owl-carousel .owl-nav.disabled { display: block !important; }
        .owl-carousel .owl-nav button { background: #000 !important; width: 35px; height: 35px; }
        .owl-carousel .owl-nav button span { font-size: 41px !important; color: #fff; line-height: 31px; }
        .owl-carousel .owl-nav button.owl-prev { position: absolute; z-index: 1; top: 40%; left: 0px; }
        .owl-carousel .owl-nav button.owl-next { position: absolute; z-index: 1; top: 40%; right: 0px; }
        @media only screen and (max-width: 619px) { 
		#block-related-heading {
		    font-size: 20px;
		}
    </style>';

    $js = '
    <script type="text/javascript">
        require([\'jquery\', \'mgs/owlcarousel\'], function(jQuery) {
            (function($) {
                $("#pdp_slider_new_arrivals").owlCarousel({
                    loop: ' . ($productsArrCount > 1 ? 'true' : 'false') . ',
                    rewind: true,
                    items: 4,
                    nav: true,
                    margin: 10,
                    dots: false,
                    autoplay: false,
                    autoplayTimeout: 5000,
                    autoplayHoverPause: false,
                    navText: ["<i class=\'fa fa-arrow-left\'></i>", "<i class=\'fa fa-arrow-right\'></i>"],
                    responsive: {
                        0: { items: 2 },
                        480: { items: 2 },
                        768: { items: 4 },
                        980: { items: 4 }
                    }
                });
            })(jQuery);
        });
    </script>';
    $result =  array('count' => $productsArrCount, 'reshtml' =>$css . $itemList . $js);
    return $result;
    // return  $css . $itemList . $js;
}

public function getRecentViewed($recentlyViewedSkus)
{
    $storeId = $this->_storeManager->getStore()->getId();
	$specialPriceField = "special_price_".$storeId;
	$actualPriceField = "actual_price_".$storeId;
		
	
		if (!is_array($recentlyViewedSkus) || count($recentlyViewedSkus) < 4) {
	        // Deliberately create a query that matches no records
	        $query = "q=*:*&fq=prod_en_id_int:0"; 
	    } else {
	        // Build the query for valid recently viewed SKUs
	        $query = "q=prod_sku:(" . rawurlencode(implode(' OR ', $recentlyViewedSkus)) . ")";
	    }
		$query .= "&fl=actual_price:".$actualPriceField.",special_price:".$specialPriceField.",categories-store-".$storeId."_id,prod_en_id,prod_type,prod_sku,prod_plp_flag,prod_sd_flag,prod_sdm_flag,prod_rts_flag,enquire_".$storeId.",prod_exp_ship_flag,same_day_shipping_market,rts_flag_market,express_shipping_market,prod_name,short_desc,prod_url:prod_url_".$storeId.",prod_small_img,prod_thumb_img,prod_design,prod_is_salable";

		$query .= "&fq=actual_price_".$storeId.":[".rawurlencode("1 TO *")."]";
		$query .= "&fq=!prod_en_id:".$this->_request->getParam('pid');
		if(isset($gender) && !empty($gender)){
			$query .= "&fq=gender_id:".$gender;
    	}
    	
    	// Exclude already rendered products
        if (!empty($this->renderedProductIds)) {
            $excludedIds = implode(' OR ', $this->renderedProductIds);
            $query .= "&fq=!prod_en_id_int:(" . rawurlencode($excludedIds) . ")";
            //echo $query;die;
        }

    // $randomNumber = rand(100, 999);
    // $query .= '&sort=' . rawurlencode('prod_en_id_int desc');
    $query .= "&start=0&rows=15";

    //echo $query;die;
    //$query = "q=*:*";
    $filt_coll = $this->_solrHelper->getFilterCollection($query);
    $products = json_decode($filt_coll, true);
	$products = isset($products["response"]["docs"]) ? $products["response"]["docs"] : array();
	//echo '<pre>';print_r($products);die;
    $filteredProductIds = array_column($products, 'prod_en_id');
    $this->renderedProductIds = array_merge($this->renderedProductIds, $filteredProductIds);

    return json_decode($filt_coll, true);
}

public function getRecentViewedHtml($recentlyViewedSkus)
{
    // Fetch products from Solr
    $productsData = $this->getRecentViewed($recentlyViewedSkus);
    $productsArr = isset($productsData["response"]["docs"]) ? $productsData["response"]["docs"] : [];
    $productsArrCount = isset($productsData["response"]["numFound"]) ? $productsData["response"]["numFound"] : 0;

    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $formKey = $this->_formKey->getFormKey();
    $urlParamName = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;
    $uenc = $this->_cartHelper->getProdUencForWidget($this->_request->getParam('pdp_url'));

    $itemList = '
    <div>
        <div class="block widget block-suggested-products grid">
            <div class="block-content">
                <div class="products-grid grid">
                    <ol id="pdp_slider_recently_viewed" class="owl-carousel owl-theme">';

    foreach ($productsArr as $prod) {
        $pricehtml = '';
        $enquirenow = isset($prod['enquire_1'][0]) ? $prod['enquire_1'][0] : '';
        $prodUrl = isset($prod["prod_url"][0]) ? $prod["prod_url"][0] : '';
        $prodUrl = preg_replace('/&?___store=[^&]*/', '', $prodUrl);
        $prodUrl = str_replace('?', '', $prodUrl);
        $smallImage = isset($prod["prod_small_img"]) ? $prod["prod_small_img"] : '';
        $thumbnailImage = isset($prod["prod_thumb_img"]) ? $prod["prod_thumb_img"] : '';
        $name = isset($prod['prod_name'][0]) ? $prod['prod_name'][0] : '';
        $prodId = isset($prod['prod_en_id']) ? $prod['prod_en_id'] : '';
        $routeParams = [
            $urlParamName => $uenc,
            'product' => $prodId,
            '_secure' => 1
        ];
        $addToCartUrl = $this->_cartHelper->getProdCartUrl($routeParams);
        $designerName = isset($prod['prod_design']) ? $prod['prod_design'] : '';
        $shortDesc = isset($prod['short_desc']) ? $prod['short_desc'] : '';
        $enquirehtml = '';

        if ($enquirenow == 1) {
            $enquirehtml = '<span id="modal-btn" style="padding: 5px 25px; position: relative; top: 8px; border:1px #000 solid;" data-toggle="modal" data-book-id=' . $prod['prod_sku'] . '>Enquire Now</span>';
        } else {
            $specialPrice = $this->getPrice(isset($prod["actual_price"]) ? $prod["actual_price"] : 0);
            if ((isset($prod['special_price']) && isset($prod['actual_price'])) && ($prod['special_price'] < $prod['actual_price'])) {
                $actualPrice = $prod['actual_price'];
                $specialPrice = $prod['special_price'];
                $specialPrice = $this->getPrice($specialPrice);
                $actualPrice = $this->getPrice($actualPrice);
                $pricehtml = '
                <div class="price-box price-final_price" data-role="priceBox" data-product-id="227330" data-price-box="product-id-227330">
                    <span class="normal-price">
                        <span class="price-container price-final_price tax weee">
                            <span id="product-price-227330" data-price-amount="35200" data-price-type="finalPrice" class="price-wrapper ">
                                <span class="price">' . $specialPrice . '</span>
                            </span>
                        </span>
                    </span>
                    <span class="old-price sly-old-price">
                        <span class="price-container price-final_price tax weee">
                            <span class="price-label">Regular Price</span>
                            <span id="old-price-227330" data-price-amount="44000" data-price-type="oldPrice" class="price-wrapper ">
                                <span class="price">' . $actualPrice . '</span>
                            </span>
                        </span>
                    </span>
                </div>';
            } else {
                $actualPrice = $this->getPrice(isset($prod["actual_price"]) ? $prod["actual_price"] : 0);
                $pricehtml = '
                <div class="price-box price-final_price" data-role="priceBox" data-product-id="232055" data-price-box="product-id-232055">
                    <span class="normal-price">
                        <span class="price-container price-final_price tax weee">
                            <span id="product-price-232055" data-price-amount="42000" data-price-type="finalPrice" class="price-wrapper ">
                                <span class="price">' . $actualPrice . '</span>
                            </span>
                        </span>
                    </span>
                </div>';
            }
        }

        $categoryRepository = $objectManager->get('\Magento\Catalog\Api\CategoryRepositoryInterface');
        $parent = $objectManager->get('Magento\Catalog\Model\Product')->load($prodId);
        $cats = $parent->getCategoryIds();
        $category_name = '';
        $category_id = isset($cats[0]) ? $cats[0] : 0;

        if ($category_id) {
            try {
                $category = $categoryRepository->get($category_id);
                $category_name = $category->getName();
            } catch (Exception $e) {
                $category_name = 'NA';
            }
        }

        $actionLinkHtml = '';

        if ($enquirenow != 1) {
            $actionLinkHtml = '
            <ul class="actions-link" data-role="add-to-links">
                <li class="hidden-sm hidden-xs">
                    <button data-title="Quick View" class="action mgs-quickview" data-quickview-url="/mgs_quickview/catalog_product/view/id/' . $prodId . '/" title="Quick View">
                        <span class="fa fa-eye"></span>
                    </button>
                </li>
                <li>
                    <button data-title="Add to Wish List" id="add_to_wish_ymal" class="action towishlist" title="Add to Wish List" aria-label="Add to Wish List" data-post="{&quot;action&quot;:&quot;https:\/\/aashniandco.com\/wishlist\/index\/add\/&quot;,&quot;data&quot;:{&quot;product&quot;:' . $prodId . ',&quot;uenc&quot;:&quot;' . $uenc . '&quot;}}" data-action="add-to-wishlist" role="button" data-sku="' . $prod['prod_sku'] . '" data-name="' . $shortDesc . '" data-brand="' . $designerName . '" data-oldPrice="' . $actualPrice . '" data-value="' . $specialPrice . '" data-category="' . $category_name . '">
                        <i class="fa fa-heart"></i>
                    </button>
                </li>
                <li>
                    <button data-title="Add to Compare" class="action tocompare" title="Add to Compare" aria-label="Add to Compare" data-post="{&quot;action&quot;:&quot;https:\/\/aashniandco.com\/catalog\/product_compare\/add\/&quot;,&quot;data&quot;:{&quot;product&quot;:&quot;' . $prodId . '&quot;,&quot;uenc&quot;:&quot;' . $uenc . '&quot;}}" role="button">
                        <i class="fa fa-retweet"></i>
                    </button>
                </li>
                <li>
                    <form data-role="tocart-form" action="' . $addToCartUrl . '" method="post" novalidate="novalidate">
                        <input type="hidden" name="product" value="' . $prodId . '">
                        <input type="hidden" name="uenc" value="' . $uenc . '">
                        <input name="form_key" type="hidden" value="' . $formKey . '">
                        <button type="submit" title="Add to Cart" class="action tocart">
                            <span class="fa fa-shopping-cart"></span>
                        </button>
                    </form>
                </li>
            </ul>';
        }

        $itemList .= '
        <li class="product-item-info item">
            <div class="product-top">
                <a href="' . $prodUrl . '" class="product-item-photo utm_class">
                    <img width="100px" src="' . $smallImage . '" alt="' . $name . '">
                </a>
                ' . $actionLinkHtml . '
                <div class="product-item-details">
                    <h4 class="product-item-name">
                        <a title="' . $name . '" href="' . $prodUrl . '" class="product-item-link utm_class">' . $name . '</a>
                    </h4>
                    <h4 class="product description product-item-description">' . $shortDesc . '</h4>
                    ' . $pricehtml . '
                </div>
            </div>
            ' . $enquirehtml . '
        </li>';
    }

    $itemList .= '
                    </ol>
                </div>
            </div>
        </div>
    </div>';

    $css = '
    <style>
        #pdp_sliderrelated { text-align: center; }
        #pdp_slider_recently_viewed { text-align: center; }
        .product-items { display: flex; }
        .product-item { padding: 5px; text-align: center; }
        .you_likebtn { background: #000; border: 1px #000 solid; padding: 5px 15px; margin-top: 10px; color: #fff; }
        #pdp_slider_recently_viewed .price-label { color: #000; }
        .owl-carousel .owl-nav.disabled { display: block !important; }
        .owl-carousel .owl-nav button { background: #000 !important; width: 35px; height: 35px; }
        .owl-carousel .owl-nav button span { font-size: 41px !important; color: #fff; line-height: 31px; }
        .owl-carousel .owl-nav button.owl-prev { position: absolute; z-index: 1; top: 40%; left: 0px; }
        .owl-carousel .owl-nav button.owl-next { position: absolute; z-index: 1; top: 40%; right: 0px; }
        @media only screen and (max-width: 619px) { 
		#block-related-heading {
		    font-size: 20px;
		}
    </style>';

    $js = '
    <script type="text/javascript">
        require([\'jquery\', \'mgs/owlcarousel\'], function(jQuery) {
            (function($) {
                $("#pdp_slider_recently_viewed").owlCarousel({
                    loop: ' . ($productsArrCount > 1 ? 'true' : 'false') . ',
                    rewind: true,
                    items: 4,
                    nav: true,
                    margin: 10,
                    dots: false,
                    autoplay: false,
                    autoplayTimeout: 5000,
                    autoplayHoverPause: false,
                    navText: ["<i class=\'fa fa-arrow-left\'></i>", "<i class=\'fa fa-arrow-right\'></i>"],
                    responsive: {
                        0: { items: 2 },
                        480: { items: 2 },
                        768: { items: 4 },
                        980: { items: 4 }
                    }
                });
            })(jQuery);
        });
    </script>';
    $result =  array('count' => $productsArrCount, 'reshtml' =>$css . $itemList . $js);
    return $result;
    // return  $css . $itemList . $js;
}

public function getReadyToShipProducts($pId,$pattern,$kidsId,$gender)
{
    $pattArr = explode(',', $pattern);

    if(count($pattArr) <= 0){
    	return false;
    }

    if (is_array($pattArr)) {
        $patternQuery = implode(' OR ', $pattArr);
    } else {
        $patternQuery = $pattern;
    }


		
    $storeId = $this->_storeManager->getStore()->getId();
	$specialPriceField = "special_price_".$storeId;
	$actualPriceField = "actual_price_".$storeId;
			
		/* field list params */
		$query = "q=patterns_id:(".rawurlencode($patternQuery).")";
		$query .= "&fl=actual_price:".$actualPriceField.",special_price:".$specialPriceField.",categories-store-".$storeId."_id,prod_en_id,prod_type,prod_sku,prod_plp_flag,prod_sd_flag,prod_sdm_flag,prod_rts_flag,enquire_".$storeId.",prod_exp_ship_flag,same_day_shipping_market,rts_flag_market,express_shipping_market,prod_name,short_desc,prod_url:prod_url_".$storeId.",prod_small_img,prod_thumb_img,prod_design,prod_is_salable";

		$query .= "&fq=actual_price_".$storeId.":[".rawurlencode("1 TO *")."]";
		$query .= "&fq=!prod_en_id:".$this->_request->getParam('pid');
		$query .= "&fq=delivery_id:6038";
		if(isset($kidsId) && !empty($kidsId)){
			$query .= "&fq=kid_id:".$kidsId;
    	}
    	if(isset($gender) && !empty($gender)){
			$query .= "&fq=gender_id:".$gender;
    	}
        // Exclude already rendered products
        if (!empty($this->renderedProductIds)) {
            $excludedIds = implode(' OR ', $this->renderedProductIds);
            $query .= "&fq=-prod_en_id_int:(" . rawurlencode($excludedIds) . ")";
            // echo $query;die;
        }


    $randomNumber = rand(100, 999);
    $query .= '&sort=' . rawurlencode('random_' . $randomNumber . ' asc');
    $query .= "&start=0&rows=16";

 	// echo $query;die;
    $filt_coll = $this->_solrHelper->getFilterCollection($query);
    $products = json_decode($filt_coll, true);
	$products = isset($products["response"]["docs"]) ? $products["response"]["docs"] : array();
    $filteredProductIds = array_column($products, 'prod_en_id');
    // print_r($filteredProductIds);die;
    $this->renderedProductIds = array_merge($this->renderedProductIds, $filteredProductIds);
    return json_decode($filt_coll, true);
}

public function getReadyToShipHtml($pId, $pattern,$kidsId, $gender)
{
    // Fetch products from Solr
    $productsData = $this->getReadyToShipProducts($pId,$pattern,$kidsId,$gender);
    $productsArr = isset($productsData["response"]["docs"]) ? $productsData["response"]["docs"] : [];
    $productsArrCount = isset($productsData["response"]["numFound"]) ? $productsData["response"]["numFound"] : 0;

    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $formKey = $this->_formKey->getFormKey();
    $urlParamName = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;
    $uenc = $this->_cartHelper->getProdUencForWidget($this->_request->getParam('pdp_url'));

    $itemList = '
    <div>
        <div class="block widget block-suggested-products grid">
            <div class="block-content">
                <div class="products-grid grid">
                    <ol id="pdp_slider_rts" class="owl-carousel owl-theme">';

    foreach ($productsArr as $prod) {
        $pricehtml = '';
        $enquirenow = isset($prod['enquire_1'][0]) ? $prod['enquire_1'][0] : '';
        $prodUrl = isset($prod["prod_url"][0]) ? $prod["prod_url"][0] : '';
        $prodUrl = preg_replace('/&?___store=[^&]*/', '', $prodUrl);
        $prodUrl = str_replace('?', '', $prodUrl);
        $smallImage = isset($prod["prod_small_img"]) ? $prod["prod_small_img"] : '';
        $thumbnailImage = isset($prod["prod_thumb_img"]) ? $prod["prod_thumb_img"] : '';
        $name = isset($prod['prod_name'][0]) ? $prod['prod_name'][0] : '';
        $prodId = isset($prod['prod_en_id']) ? $prod['prod_en_id'] : '';
        $routeParams = [
            $urlParamName => $uenc,
            'product' => $prodId,
            '_secure' => 1
        ];
        $addToCartUrl = $this->_cartHelper->getProdCartUrl($routeParams);
        $designerName = isset($prod['prod_design']) ? $prod['prod_design'] : '';
        $shortDesc = isset($prod['short_desc']) ? $prod['short_desc'] : '';
        $enquirehtml = '';

        if ($enquirenow == 1) {
            $enquirehtml = '<span id="modal-btn" style="padding: 5px 25px; position: relative; top: 8px; border:1px #000 solid;" data-toggle="modal" data-book-id=' . $prod['prod_sku'] . '>Enquire Now</span>';
        } else {
            $specialPrice = $this->getPrice(isset($prod["actual_price"]) ? $prod["actual_price"] : 0);
            if ((isset($prod['special_price']) && isset($prod['actual_price'])) && ($prod['special_price'] < $prod['actual_price'])) {
                $actualPrice = $prod['actual_price'];
                $specialPrice = $prod['special_price'];
                $specialPrice = $this->getPrice($specialPrice);
                $actualPrice = $this->getPrice($actualPrice);
                $pricehtml = '
                <div class="price-box price-final_price" data-role="priceBox" data-product-id="227330" data-price-box="product-id-227330">
                    <span class="normal-price">
                        <span class="price-container price-final_price tax weee">
                            <span id="product-price-227330" data-price-amount="35200" data-price-type="finalPrice" class="price-wrapper ">
                                <span class="price">' . $specialPrice . '</span>
                            </span>
                        </span>
                    </span>
                    <span class="old-price sly-old-price">
                        <span class="price-container price-final_price tax weee">
                            <span class="price-label">Regular Price</span>
                            <span id="old-price-227330" data-price-amount="44000" data-price-type="oldPrice" class="price-wrapper ">
                                <span class="price">' . $actualPrice . '</span>
                            </span>
                        </span>
                    </span>
                </div>';
            } else {
                $actualPrice = $this->getPrice(isset($prod["actual_price"]) ? $prod["actual_price"] : 0);
                $pricehtml = '
                <div class="price-box price-final_price" data-role="priceBox" data-product-id="232055" data-price-box="product-id-232055">
                    <span class="normal-price">
                        <span class="price-container price-final_price tax weee">
                            <span id="product-price-232055" data-price-amount="42000" data-price-type="finalPrice" class="price-wrapper ">
                                <span class="price">' . $actualPrice . '</span>
                            </span>
                        </span>
                    </span>
                </div>';
            }
        }

        $categoryRepository = $objectManager->get('\Magento\Catalog\Api\CategoryRepositoryInterface');
        $parent = $objectManager->get('Magento\Catalog\Model\Product')->load($prodId);
        $cats = $parent->getCategoryIds();
        $category_name = '';
        $category_id = isset($cats[0]) ? $cats[0] : 0;

        if ($category_id) {
            try {
                $category = $categoryRepository->get($category_id);
                $category_name = $category->getName();
            } catch (Exception $e) {
                $category_name = 'NA';
            }
        }

        $actionLinkHtml = '';

        if ($enquirenow != 1) {
            $actionLinkHtml = '
            <ul class="actions-link" data-role="add-to-links">
                <li class="hidden-sm hidden-xs">
                    <button data-title="Quick View" class="action mgs-quickview" data-quickview-url="/mgs_quickview/catalog_product/view/id/' . $prodId . '/" title="Quick View">
                        <span class="fa fa-eye"></span>
                    </button>
                </li>
                <li>
                    <button data-title="Add to Wish List" id="add_to_wish_ymal" class="action towishlist" title="Add to Wish List" aria-label="Add to Wish List" data-post="{&quot;action&quot;:&quot;https:\/\/aashniandco.com\/wishlist\/index\/add\/&quot;,&quot;data&quot;:{&quot;product&quot;:' . $prodId . ',&quot;uenc&quot;:&quot;' . $uenc . '&quot;}}" data-action="add-to-wishlist" role="button" data-sku="' . $prod['prod_sku'] . '" data-name="' . $shortDesc . '" data-brand="' . $designerName . '" data-oldPrice="' . $actualPrice . '" data-value="' . $specialPrice . '" data-category="' . $category_name . '">
                        <i class="fa fa-heart"></i>
                    </button>
                </li>
                <li>
                    <button data-title="Add to Compare" class="action tocompare" title="Add to Compare" aria-label="Add to Compare" data-post="{&quot;action&quot;:&quot;https:\/\/aashniandco.com\/catalog\/product_compare\/add\/&quot;,&quot;data&quot;:{&quot;product&quot;:&quot;' . $prodId . '&quot;,&quot;uenc&quot;:&quot;' . $uenc . '&quot;}}" role="button">
                        <i class="fa fa-retweet"></i>
                    </button>
                </li>
                <li>
                    <form data-role="tocart-form" action="' . $addToCartUrl . '" method="post" novalidate="novalidate">
                        <input type="hidden" name="product" value="' . $prodId . '">
                        <input type="hidden" name="uenc" value="' . $uenc . '">
                        <input name="form_key" type="hidden" value="' . $formKey . '">
                        <button type="submit" title="Add to Cart" class="action tocart">
                            <span class="fa fa-shopping-cart"></span>
                        </button>
                    </form>
                </li>
            </ul>';
        }

        $itemList .= '
        <li class="product-item-info item">
            <div class="product-top">
                <a href="' . $prodUrl . '" class="product-item-photo utm_class">
                    <img width="100px" src="' . $smallImage . '" alt="' . $name . '">
                </a>
                ' . $actionLinkHtml . '
                <div class="product-item-details">
                    <h4 class="product-item-name">
                        <a title="' . $name . '" href="' . $prodUrl . '" class="product-item-link utm_class">' . $name . '</a>
                    </h4>
                    <h4 class="product description product-item-description">' . $shortDesc . '</h4>
                    ' . $pricehtml . '
                </div>
            </div>
            ' . $enquirehtml . '
        </li>';
    }

    $itemList .= '
                    </ol>
                </div>
            </div>
        </div>
    </div>';

    $css = '
    <style>
        #pdp_sliderrelated { text-align: center; }
        #pdp_slider_rts { text-align: center; }
        .product-items { display: flex; }
        .product-item { padding: 5px; text-align: center; }
        .you_likebtn { background: #000; border: 1px #000 solid; padding: 5px 15px; margin-top: 10px; color: #fff; }
        #pdp_slider_rts .price-label { color: #000; }
        .owl-carousel .owl-nav.disabled { display: block !important; }
        .owl-carousel .owl-nav button { background: #000 !important; width: 35px; height: 35px; }
        .owl-carousel .owl-nav button span { font-size: 41px !important; color: #fff; line-height: 31px; }
        .owl-carousel .owl-nav button.owl-prev { position: absolute; z-index: 1; top: 40%; left: 0px; }
        .owl-carousel .owl-nav button.owl-next { position: absolute; z-index: 1; top: 40%; right: 0px; }
        @media only screen and (max-width: 619px) { 
		#block-related-heading {
		    font-size: 20px;
		}
    </style>';

    $js = '
    <script type="text/javascript">
        require([\'jquery\', \'mgs/owlcarousel\'], function(jQuery) {
            (function($) {
                $("#pdp_slider_rts").owlCarousel({
                    loop: ' . ($productsArrCount > 1 ? 'true' : 'false') . ',
                    rewind: true,
                    items: 4,
                    nav: true,
                    margin: 10,
                    dots: false,
                    autoplay: false,
                    autoplayTimeout: 5000,
                    autoplayHoverPause: false,
                    navText: ["<i class=\'fa fa-arrow-left\'></i>", "<i class=\'fa fa-arrow-right\'></i>"],
                    responsive: {
                        0: { items: 2 },
                        480: { items: 2 },
                        768: { items: 4 },
                        980: { items: 4 }
                    }
                });
            })(jQuery);
        });
    </script>';
    $result =  array('count' => $productsArrCount, 'reshtml' =>$css . $itemList . $js);
    return $result;
    // return $css . $itemList . $js;
}
	public function getYouMayAlsoLikeHtml($pattern , $lastChildId, $gender, $finalprice){
		$productsData = $this->getYouMayLikeProduct($pattern, $lastChildId, $gender , $finalprice);
		$productsArr = isset($productsData["response"]["docs"]) ? $productsData["response"]["docs"] : array();

		$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
		// $cartHelper = $objectManager->create('Magento\Checkout\Helper\Cart');
		$formKey = $this->_formKey->getFormKey();

		$urlParamName = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;

		$uenc = $this->_cartHelper->getProdUencForWidget($this->_request->getParam('pdp_url'));
		$itemList = '<div>
		<div class="block widget block-suggested-products grid">
			<div class="block-content">
			    <div class="products-grid grid">
			        <ol id="pdp_sliderlike" class="owl-carousel owl-theme">';

		foreach ($productsArr as $pkey => $prod) {
			$pricehtml = '';
			$enquirenow = isset($prod['enquire_1'][0]) ? $prod['enquire_1'][0] : '';
				

			$prodUrl = isset($prod["prod_url"][0]) ? $prod["prod_url"][0] : '';
		    $prodUrl = preg_replace('/&?___store=[^&]*/', '', $prodUrl);
		    $prodUrl = str_replace('?','', $prodUrl);
		    
			$smallImage = isset($prod["prod_small_img"]) ? $prod["prod_small_img"] : '';
			$thumbnailImage = isset($prod["prod_thumb_img"]) ? $prod["prod_thumb_img"] : '';
			$name = isset($prod['prod_name'][0]) ? $prod['prod_name'][0] : '';
			$prodId =  isset($prod['prod_en_id']) ? $prod['prod_en_id'] : '';
			$prodSku =  isset($prod['prod_sku']) ? $prod['prod_sku'] : '';
			$routeParams = [
	            $urlParamName => $uenc,
	            'product' => $prodId,
	            '_secure' => 1
	        ];
	        $addToCartUrl = $this->_cartHelper->getProdCartUrl($routeParams);
			$designerName = isset($prod['prod_design']) ? $prod['prod_design'] : '';
			$shortDesc = isset($prod['short_desc']) ? $prod['short_desc'] : '';
			$enquirehtml = '';
				if($enquirenow == 1){
					// $pricehtml = '<button type="button"                                                                          style="background: #fff; z-index:28; color: #000; padding: 5px 25px;                                                                          position: relative; top: 8px; border:1px #000 solid;"                                                                          class="btn btn-primary" data-toggle="modal"                                                                          data-target="#listEnquiryModal"                                                                          data-sku="'.$prod['prod_sku'].'"                                                                          data-description="'.$shortDesc.'"                                                                          data-name="'.$name.'">Enquire Now </button>';  

					$enquirehtml = '<span id="modal-btn" style="padding: 5px 25px; position: relative; top: 8px; border:1px #000 solid;" data-toggle="modal" data-book-id='.$prod['prod_sku'].'>Enquire Now</span>';
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
	            
	            	} else {

		                $actualPrice = $this->getPrice(isset($prod["actual_price"]) ? $prod["actual_price"] : 0);

		                $pricehtml = '<div class="price-box price-final_price" data-role="priceBox" data-product-id="232055" data-price-box="product-id-232055"><span class="normal-price"><span class="price-container price-final_price tax weee">
						        <span id="product-price-232055" data-price-amount="42000" data-price-type="finalPrice" class="price-wrapper "><span class="price">'.$actualPrice.'</span></span>
						        </span>
						</span></div>';
		            }
				}


			$categoryRepository = $objectManager->get('\Magento\Catalog\Api\CategoryRepositoryInterface');
	        $parent = $objectManager->get('Magento\Catalog\Model\Product')->load($prodId);
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
			$actionLinkHtml = '';

			if($enquirenow != 1){
				$actionLinkHtml = '<ul class="actions-link" data-role="add-to-links">
							
				<li class="hidden-sm hidden-xs"><button data-title="Quick View" class="action mgs-quickview" data-quickview-url="/mgs_quickview/catalog_product/view/id/'.$prodId.'/" title="Quick View">
				<span class="fa fa-eye"></span></button></li>
				<li><button data-title="Add to Wish List" id="add_to_wish_ymal" class="action towishlist" title="Add to Wish List" aria-label="Add to Wish List" data-post="{&quot;action&quot;:&quot;https:\/\/aashniandco.com\/wishlist\/index\/add\/&quot;,&quot;data&quot;:{&quot;product&quot;:'.$prodId.',&quot;uenc&quot;:&quot;'.$uenc.'&quot;}}" data-action="add-to-wishlist" role="button" data-sku="'.$prodSku.'" data-name="'.$shortDesc.'" data-brand="'.$designerName.'" data-value="'.$actualPrice.'" data-category="'.$category_name.'">
						<i class="fa fa-heart"></i>
					</button></li>
					<li><button data-title="Add to Compare" class="action tocompare" title="Add to Compare" aria-label="Add to Compare" data-post="{&quot;action&quot;:&quot;https:\/\/aashniandco.com\/catalog\/product_compare\/add\/&quot;,&quot;data&quot;:{&quot;product&quot;:&quot;'.$prodId.'&quot;,&quot;uenc&quot;:&quot;'.$uenc.'&quot;}}" role="button">
                        <i class="fa fa-retweet"></i>
                    </button></li>
					<li>																		<form data-role="tocart-form" action="'.$addToCartUrl.'" method="post" novalidate="novalidate">
						<input type="hidden" name="product" value="'.$prodId.'">
						<input type="hidden" name="uenc" value="'.$uenc.'">
						<input name="form_key" type="hidden" value="'.$formKey.'">										<button type="submit" title="Add to Cart" class="action tocart">
							<span class="fa fa-shopping-cart"></span>
						</button>
					</form></li>


											</ul>';
			}

			$itemList .= '<li class="product-item-info item">            <div class="product-top">
                        <a href="'.$prodUrl.'" class="product-item-photo">
            <img width="100px" src="'.$smallImage.'" alt="'.$name.'">
	    </a>  
	    	'.$actionLinkHtml.'

	    	
	    	    <div class="product-item-details">
		                <h4 class="product-item-name">
		                <a title="'.$name.'" href="'.$prodUrl.'" class="product-item-link">
		                    '.$name.'               </a>
		                </h4>
		                <h4 class="product description product-item-description">
		                    '.$shortDesc.'               </h4>
		                '.$pricehtml.'               </div>
				</div>'.$enquirehtml.'
                
                                            </li>';
			
		}

		$itemList .= '</ol></div></div></div></div>';


		$css = '<style>#pdp_sliderrelated {    text-align: center;}#pdp_sliderlike {    text-align: center;}.product-items {display:flex;}.product-item{padding:5px; text-align: center;}.you_likebtn{ background:#000; border:1px #000 solid; padding:5px 15px; margin-top:10px; color:#fff;}#pdp_sliderlike .price-label {    color: #000;}.owl-carousel .owl-nav.disabled {    display:block !important;}.owl-carousel .owl-nav button {    background: #000 !important;    width: 35px;    height: 35px;}.owl-carousel .owl-nav button span {    font-size: 41px !important;    color: #fff;    line-height: 31px;}.owl-carousel .owl-nav button.owl-prev{  position: absolute;    z-index: 1;    top: 40%;    left: 0px; }.owl-carousel .owl-nav button.owl-next{position: absolute;    z-index: 1;    top: 40%;    right: 0px; }</style>';
		$js = '<script type="text/javascript">    require([        \'jquery\',        \'mgs/owlcarousel\'    ], function(jQuery){        (function($) {$("#pdp_sliderlike").owlCarousel({loop:false,rewind:true,                items: 4,                nav: true,                margin:10,                dots: false,                autoplay: false,                autoplayTimeout:5000,                autoplayHoverPause:false,                loop:true,                navText: ["<i class=\'fa fa-arrow-left\'></i>","<i class=\'fa fa-arrow-right\'></i>"],                responsive:{                    0 : {items: 2},                    480 : {items: 2},                    768 : {items: 4},                    980 : {items: 4}                }            });        })(jQuery);    });</script>';


		return $css.$itemList.$js;
		
	}





	/* get filtered product collection */
	public function getYouMayLikeProduct($pattern, $lastChildId, $gender , $finalprice) {
		$current_price = $finalprice;
		$range = 10000;

		$lower_bound = max(0, $current_price - $range); // Ensure no negative lower bound
		$upper_bound = $current_price + $range;
		$st_id = $this->_storeManager->getStore()->getId();
		//$st_id = 1;
		$query = "q=patterns_id:".$pattern;
		
		//$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
		//$country = $this->_productModel->getCountryCode();
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
		$query .= "&fl=actual_price:".$actualPriceField.",special_price:".$specialPriceField.",categories-store-".$st_id."_id,prod_en_id,prod_type,prod_sku,prod_plp_flag,prod_sd_flag,prod_sdm_flag,prod_rts_flag,enquire_".$st_id.",prod_exp_ship_flag,same_day_shipping_market,rts_flag_market,express_shipping_market,prod_name,short_desc,prod_url:prod_url_".$st_id.",prod_small_img,prod_thumb_img,prod_design,prod_is_salable";

		$query .= "&fq=actual_price_" . $st_id . ":[" . rawurlencode($lower_bound . " TO " . $upper_bound) . "]";
		$query .= "&fq=!prod_en_id:".$this->_request->getParam('pid');
		//$query .= "&fq=prod_is_salable:1";
		//$query .= "&fq=-enquire_".$st_id.":1";
		// Exclude already rendered products
        if (!empty($this->renderedProductIds)) {
            $excludedIds = implode(' OR ', $this->renderedProductIds);
            $query .= "&fq=!prod_en_id_int:(" . rawurlencode($excludedIds) . ")";
            //echo $query;die;
        }
        if (!empty($lastChildId)) {
            $query .= "&fq=categories-store-".$st_id."_id:(" . rawurlencode($lastChildId) . ")";
        }
        if (!empty($lastChildId)) {
            $query .= "&fq=gender_id:".$gender;
        }


		$randomNumber = rand(100,999);
					
		$query .= '&sort='.rawurlencode('random_'.$randomNumber.' asc');

		/* pagination */	
		$query .= "&start=0&rows=15";		
		
		if(isset($_GET['otest']) && $_GET['otest'] == 'ntest') {
			echo $query;die;
		}
		//echo $query;die;
		//$solrHelper = $objectManager->create('\Fermion\Pagelayout\Helper\SolrHelper');
		$filt_coll = $this->_solrHelper->getFilterCollection($query);
		$products = json_decode($filt_coll, true);
		$products = isset($products["response"]["docs"]) ? $products["response"]["docs"] : array();
	    $filteredProductIds = array_column($products, 'prod_en_id');
	    $this->renderedProductIds = array_merge($this->renderedProductIds, $filteredProductIds);
		
		return json_decode($filt_coll, 1);
	}



	/* get filtered product collection */
	public function getRelatedProduct($designer) {

		$st_id = $this->_storeManager->getStore()->getId();
		//$st_id = 1;
		$query = "q=designer_id:".$designer;
		
		//$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
		//$country = $this->_productModel->getCountryCode();
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
		$query .= "&fl=actual_price:".$actualPriceField.",special_price:".$specialPriceField.",categories-store-".$st_id."_id,prod_en_id,prod_type,prod_sku,prod_plp_flag,prod_sd_flag,prod_sdm_flag,prod_rts_flag,enquire_".$st_id.",prod_exp_ship_flag,same_day_shipping_market,rts_flag_market,express_shipping_market,prod_name,short_desc,prod_url:prod_url_".$st_id.",prod_small_img,prod_thumb_img,prod_design,prod_is_salable";

		$query .= "&fq=actual_price_".$st_id.":[".rawurlencode("1 TO *")."]";
		$query .= "&fq=!prod_en_id:".$this->_request->getParam('pid');
		//$query .= "&fq=prod_is_salable:1";
		//$query .= "&fq=-enquire_".$st_id.":1";
		// Exclude already rendered products
        if (!empty($this->renderedProductIds)) {
            $excludedIds = implode(' OR ', $this->renderedProductIds);
            $query .= "&fq=!prod_en_id_int:(" . rawurlencode($excludedIds) . ")";
            // echo $query;die;
        }
		// if($this->_request->getParam('pid') == 250170){
		// 	echo $query;die;
		// }
		

		
		$randomNumber = rand(100,999);
					
		$query .= '&sort='.rawurlencode('random_'.$randomNumber.' asc');

		/* pagination */	
		$query .= "&start=0&rows=5";		
		
		if(isset($_GET['otest']) && $_GET['otest'] == 'ntest') {
			echo $query;die;
		}
		//$solrHelper = $objectManager->create('\Fermion\Pagelayout\Helper\SolrHelper');
		$filt_coll = $this->_solrHelper->getFilterCollection($query);
		$products = json_decode($filt_coll, true);
		$products = isset($products["response"]["docs"]) ? $products["response"]["docs"] : array();

	    $filteredProductIds = array_column($products, 'prod_en_id');
	    // print_r($filteredProductIds);die;
	    $this->renderedProductIds = array_merge($this->renderedProductIds, $filteredProductIds);

		return json_decode($filt_coll, 1);
	}
	public function getRelatedProductHtml($designer){
		$productsData = $this->getRelatedProduct($designer);
		$productsArr = isset($productsData["response"]["docs"]) ? $productsData["response"]["docs"] : array();

		$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
		//$cartHelper = $objectManager->create('Magento\Checkout\Helper\Cart');
		$formKey = $this->_formKey->getFormKey();

		$urlParamName = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;
		//die('====================');

		$uenc = $this->_cartHelper->getProdUencForWidget($this->_request->getParam('pdp_url'));
		$itemList = '<div>
		<div class="block widget block-suggested-products grid">
			<div class="block-content">
			    <div class="products-grid grid">
			        <ol id="pdp_sliderrelated" class="owl-carousel owl-theme">';

		foreach ($productsArr as $pkey => $prod) {
			$pricehtml = '';
			$enquirenow = isset($prod['enquire_1'][0]) ? $prod['enquire_1'][0] : '';
				

			$prodUrl = isset($prod["prod_url"][0]) ? $prod["prod_url"][0] : '';
		    $prodUrl = preg_replace('/&?___store=[^&]*/', '', $prodUrl);
		    $prodUrl = str_replace('?','', $prodUrl);
		    
			$smallImage = isset($prod["prod_small_img"]) ? $prod["prod_small_img"] : '';
			$thumbnailImage = isset($prod["prod_thumb_img"]) ? $prod["prod_thumb_img"] : '';
			$name = isset($prod['prod_name'][0]) ? $prod['prod_name'][0] : '';
			$prodId =  isset($prod['prod_en_id']) ? $prod['prod_en_id'] : '';
			$prodSku =  isset($prod['prod_sku']) ? $prod['prod_sku'] : '';
			$routeParams = [
	            $urlParamName => $uenc,
	            'product' => $prodId,
	            '_secure' => 1
	        ];
	        $addToCartUrl = $this->_cartHelper->getProdCartUrl($routeParams);
			$designerName = isset($prod['prod_design']) ? $prod['prod_design'] : '';
			$shortDesc = isset($prod['short_desc']) ? $prod['short_desc'] : '';
			$enquirehtml = '';
			if($enquirenow == 1){
				$enquirehtml = '<span id="modal-btn" style="padding: 5px 25px; position: relative; top: 8px; border:1px #000 solid;" data-toggle="modal" data-book-id='.$prod['prod_sku'].'>Enquire Now</span>';
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
	            
	            } else {

	                $actualPrice = $this->getPrice(isset($prod["actual_price"]) ? $prod["actual_price"] : 0);

	                $pricehtml = '<div class="price-box price-final_price" data-role="priceBox" data-product-id="232055" data-price-box="product-id-232055"><span class="normal-price"><span class="price-container price-final_price tax weee">
					        <span id="product-price-232055" data-price-amount="42000" data-price-type="finalPrice" class="price-wrapper "><span class="price">'.$actualPrice.'</span></span>
					        </span>
					</span></div>';
	            }
			}

			$categoryRepository = $objectManager->get('\Magento\Catalog\Api\CategoryRepositoryInterface');
	        $parent = $objectManager->get('Magento\Catalog\Model\Product')->load($prodId);
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
			$actionLinkHtml = '';

			if($enquirenow != 1){
				$actionLinkHtml = '<ul class="actions-link" data-role="add-to-links">
							
				<li class="hidden-sm hidden-xs"><button data-title="Quick View" class="action mgs-quickview" data-quickview-url="/mgs_quickview/catalog_product/view/id/'.$prodId.'/" title="Quick View">
				<span class="fa fa-eye"></span></button></li>
				<li><button data-title="Add to Wish List" id="add_to_wish_mfd" class="action towishlist" title="Add to Wish List" aria-label="Add to Wish List" data-post="{&quot;action&quot;:&quot;https:\/\/aashniandco.com\/wishlist\/index\/add\/&quot;,&quot;data&quot;:{&quot;product&quot;:'.$prodId.',&quot;uenc&quot;:&quot;'.$uenc.'&quot;}}" data-action="add-to-wishlist" role="button" data-sku="'.$prodSku.'" data-name="'.$shortDesc.'" data-brand="'.$designerName.'" data-value="'.$actualPrice.'" data-category="'.$category_name.'">
						<i class="fa fa-heart"></i>
					</button></li>
					<li><button data-title="Add to Compare" class="action tocompare" title="Add to Compare" aria-label="Add to Compare" data-post="{&quot;action&quot;:&quot;https:\/\/aashniandco.com\/catalog\/product_compare\/add\/&quot;,&quot;data&quot;:{&quot;product&quot;:&quot;'.$prodId.'&quot;,&quot;uenc&quot;:&quot;'.$uenc.'&quot;}}" role="button">
                        <i class="fa fa-retweet"></i>
                    </button></li>
					<li>																		<form data-role="tocart-form" action="'.$addToCartUrl.'" method="post" novalidate="novalidate">
						<input type="hidden" name="product" value="'.$prodId.'">
						<input type="hidden" name="uenc" value="'.$uenc.'">
						<input name="form_key" type="hidden" value="'.$formKey.'">										<button type="submit" title="Add to Cart" class="action tocart">
							<span class="fa fa-shopping-cart"></span>
						</button>
					</form></li>




					
											</ul>';
			}

			$itemList .= '<li class="product-item-info item">            <div class="product-top">
                        <a href="'.$prodUrl.'" class="product-item-photo">
            <img width="100px" src="'.$smallImage.'" alt="'.$name.'">
	    </a>  '.$actionLinkHtml.' <div class="product-item-details">
		                <h4 class="product-item-name">
		                <a title="'.$name.'" href="'.$prodUrl.'" class="product-item-link">
		                    '.$name.'               </a>
		                </h4>
		                <h4 class="product description product-item-description">
		                    '.$shortDesc.'               </h4>
		                '.$pricehtml.'               </div>
				</div>'.$enquirehtml.'
                
                                            </li>';
			
		}

		$itemList .= '</ol></div></div></div></div>';


		
		$js = '<script type="text/javascript">    require([        \'jquery\',        \'mgs/owlcarousel\'    ], function(jQuery){        (function($) {$("#pdp_sliderrelated").owlCarousel({loop:false,rewind:true,                items: 4,                nav: true,                margin:10,                dots: false,                autoplay: false,                autoplayTimeout:5000,                autoplayHoverPause:false,                loop:true,                navText: ["<i class=\'fa fa-arrow-left\'></i>","<i class=\'fa fa-arrow-right\'></i>"],                responsive:{                    0 : {items: 2},                    480 : {items: 2},                    768 : {items: 4},                    980 : {items: 4}                }            });        })(jQuery);    });</script>';


		return $itemList.$js;
		
	}


	/* get formatted price */
    public function getPrice($price) {

    	//echo $this->getCurrencyHelper($price);die;
    	//echo $this->formatPrice($this->getCurrencyHelper($price));die;
    	return $this->getCurrentCurrencySymbol() . $this->formatPrice($this->getCurrencyHelper($price));
    }

    public function getCurrentCurrencySymbol() {
		// $objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
  //       $priceCurrency = $objectManager->get('\Magento\Framework\Pricing\PriceCurrencyInterface');

        return $this->_priceCurrencyInterface->getCurrency()->getCurrencySymbol();
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

        //$priceHelper = ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data');
        return $this->_priceHelper->currency($price,false,false);
    }
}
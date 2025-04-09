<?php

namespace Fermion\NativeApp\Model\Api;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Fermion\NativeApp\Helper\AppNativeHelper;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManagerInterface;
use Fermion\NativeApp\Api\GetMegamenuInterface;
use Magento\Directory\Block\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Amasty\Geoip\Model\Geolocation;
use Magento\Framework\App\ResourceConnection;

class GetMegamenu implements GetMegamenuInterface
{

	/**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Fermion\NativeApp\Helper\AppNativeHelper
     */
    private $helper;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var Fermion\NativeApp\Model\NativeTokens
     */
    private $nativeTokens;

    /**
     * @var Fermion\NativeApp\Model\ResourceModel\NativeTokens
     */
    private $nativeTokensResource;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
	private $storeManager;

	/**
     * @var Magento\Directory\Block\Currency
     */
	private $currencyBlock;

	/**
     * @var Magento\Directory\Model\CurrencyFactory
     */
	private $currencyFactory;

	/**
     * @var Amasty\Geoip\Model\Geolocation
     */
	private $geoip;

	/**
     * @var Magento\Framework\App\ResourceConnection
     */
	private $resource;

	public function __construct(
		CustomerRepositoryInterface $customerRepository,
		AppNativeHelper $helper,
		Random $mathRandom,
		StoreManagerInterface $storeManager,
		Currency $currencyBlock,
		CurrencyFactory $currencyFactory,
		Geolocation $geoip,
		ResourceConnection $resource
	){
		$this->customerRepository = $customerRepository;
		$this->helper = $helper;
		$this->mathRandom = $mathRandom;
		$this->storeManager = $storeManager;
		$this->currencyBlock = $currencyBlock;
		$this->currencyFactory = $currencyFactory->create();
		$this->geoip = $geoip;
		$this->resource = $resource;
	}

	public function getMegamenu($ip)
	{
		// $requestJson = json_encode($ip);
        // $this->helper->validateRequests('/rest/V1/app_getmegamenu', $requestJson);

		$connection = $this->resource->getConnection();
		$tableName = $this->resource->getTableName('cms_block');

		$select = $connection->select()
			->from($tableName, ['content'])
			->where('identifier = ?','homepage_megamenu');

		$megamenuHtml = $connection->fetchOne($select);

		try{
			$result = $this->extractStructuredData($megamenuHtml, $ip);
		}
		catch(Exception $e){
			$this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", $e->getMessage());
		}

		$this->helper->sendResponse(200, 'Success', $result);
	}

	private function extractStructuredData($htmlContent, $ip)
	{
		$dom = new \DOMDocument();

		// Suppress errors due to malformed HTML
	    libxml_use_internal_errors(true);
	    $dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'));
	    libxml_clear_errors();

	    $menuData = [];
	    $hamburgerData = [];

	    foreach ($dom->getElementsByTagName('li') as $menuList) {
	    	if($menuList->getAttribute('class') === 'tab-li') {
	    		$categorySection = [];
	    		$contentData = [];

	    		$anchor = $menuList->getElementsByTagName('a')->item(0);
	    		$spanMenuTxt = $anchor->getElementsByTagName('span')->item(0);

	    		if($spanMenuTxt && $spanMenuTxt->getAttribute('class') === 'menu-txt') {
	    			$menuText = trim($spanMenuTxt->textContent);
	    			$categorySection['title'] = $menuText;
	    		}

	    		if($anchor) {
	    			$href = $anchor->getAttribute('href');
	    			$categorySection['url'] = $href;
	    		}

	    		$subMenus = $menuList->getElementsByTagName('ul')->item(0);
	    		if($subMenus) {
	    			$unique_value_arr = [];
	    			foreach ($subMenus->getElementsByTagName('p') as $subDivs) {
	    				if(strpos($subDivs->getAttribute('class'), 'sub_title') !== false) {
	    					$data = [];
	    					$mgsItems = [];
	    					$parentNode = $subDivs->parentNode;
	    					$unique_value_arr_two = [];

	    					if(strpos($parentNode->getAttribute('class'), 'single-row-multiple-title') !== false){
	    						$counter = 0;
	    						foreach($parentNode->getElementsByTagName('p') as $p){
	    							$items = [];
	    							$sub_title = trim($p->textContent);
	    							$data['sub_title'] = $sub_title;
	    							if(in_array($sub_title, $unique_value_arr)){
			    						continue;
			    					}
			    					array_push($unique_value_arr, $sub_title);
	    							$ul = $parentNode->getElementsByTagName('ul')->item($counter);
	    							foreach($ul->getElementsByTagName('a') as $anchor){
	    								$title = trim($anchor->textContent);
		    							$href = $anchor->getAttribute('href');
		    							$items = [
				    						'title' => $title,
				    						'href' => $href
				    					];
				    					array_push($mgsItems, $items);
	    							}
	    							$counter++;
		    						$data['sub_content'] = $mgsItems;
		    						array_push($contentData, $data);
	    							$data = [];
	    							$mgsItems = [];
	    						}
	    						continue;
	    					}

	    					$sub_title = trim($subDivs->textContent);
	    					$data['sub_title'] = $sub_title;
	    					if(in_array($sub_title, $unique_value_arr)){
	    						continue;
	    					}
	    					array_push($unique_value_arr, $sub_title);

	    					if(strpos($parentNode->getAttribute('class'), 'multiple-divs') !== false) {
	    						$grandParent = $parentNode->parentNode;
	    						foreach($grandParent->getElementsByTagName('div') as $parentDivs){
	    							if(strpos($parentDivs->getAttribute('class'), 'multiple-divs') !== false){
	    								foreach ($parentDivs->getElementsByTagName('a') as $anchor) {
			    							$title = trim($anchor->textContent);
			    							$href = $anchor->getAttribute('href');
			    							$items = [
					    						'title' => $title,
					    						'href' => $href
					    					];
					    					array_push($mgsItems, $items);
			    						}
	    							}
	    						}
	    					}
	    					else{
	    						foreach($parentNode->getElementsByTagName('a') as $anchor) {
		    						$items = [];
		    						$anchorParent = $anchor->parentNode;
		    						if($anchorParent->getAttribute('class') === 'productimg-plot'){
		    							$title = trim($anchorParent->getElementsByTagName('p')->item(0)->textContent);
		    							$href = $anchor->getAttribute('href');
		    							$image = $anchorParent->getElementsByTagName('img')->item(0)->getAttribute('src');
		    							$items = [
				    						'title' => $title,
				    						'image' => $image,
				    						'href' => $href
				    					];
		    						}
		    						else{
				    					$href = $anchor->getAttribute('href');
				    					$title = trim($anchor->textContent);
				    					if(empty($title)){
				    						$titlePtag = $anchor->getElementsByTagName('p')->item(0);
				    						if($titlePtag){
				    							$title = trim($titlePtag->textContent);
				    						}
				    					}
				    					$items = [
				    						'title' => $title,
				    						'href' => $href
				    					];
		    						}
		    						if(in_array($title, $unique_value_arr_two)){
		    							continue;
		    						}
		    						array_push($unique_value_arr_two, $title);
		    						array_push($mgsItems, $items);
			    				}
	    					}

		    				$data['sub_content'] = $mgsItems;
	    					array_push($contentData, $data);
	    				}

	    			}

	    			$main_mgs_image_data = [];
	    			foreach($subMenus->getElementsByTagName('div') as $divs){
	    				if($divs->getAttribute('class') === 'designer-menu'){
	    					$div = $divs->getElementsByTagName('div');
	    					$lastDiv = $div->item($div->length - 1);

	    					if($lastDiv->getAttribute('class') === 'productimg-plot'){
		    					$mgs_image = $lastDiv->getElementsByTagName('img')->item(0)->getAttribute('src');
		    					$mgs_image_url = $lastDiv->getElementsByTagName('a')->item(0)->getAttribute('href');
		    					$mgs_title = trim($lastDiv->getElementsByTagName('p')->item(0)->textContent);
		    					$main_mgs_image_data = [
		    						'mgs_title' => $mgs_title,
		    						'mgs_image' => $mgs_image,
		    						'mgs_image_url' => $mgs_image_url
		    					];
		    					array_push($contentData, $main_mgs_image_data);
		    				}
	    				}
	    			}
    				
	    			$categorySection['hover_content'] = $contentData;
	    		}
				
				array_push($menuData, $categorySection);
	    	}
	    }

	    $hamburgerData['menu'] = $menuData;
	    $hamburgerData['account'] = $this->getStructuredAccountData();
	    $hamburgerData['currency'] = $this->getStructuredCurrencyData($ip);

	    return $hamburgerData;
	}

	private function getStructuredAccountData()
	{
		return [
			[
				'button_title' => 'My Account',
				'button_link' => '/customer/account/'
			],
			[
				'button_title' => 'My Wish List',
				'button_link' => '/wishlist/'
			],
			[
				'button_title' => 'Create an Account',
				'button_link' => '/customer/account/create/'
			],
			[
				'button_title' => 'Sign In',
				'button_link' => '/customer/account/login/'
			],
			[
				'button_title' => 'My Gift Cards List',
				'button_link' => '/mageworx_giftcards/account/cardlist/'
			],
			[
				'button_title' => 'Track Your Order',
				'button_link' => '/sales/order/history/'
			],
			[
				'button_title' => 'Store Locator',
				'button_link' => '/storelocator'
			]
		];
	}

	private function getStructuredCurrencyData($ip)
	{
		$currencyData = [];
		$location = $this->geoip->locate($ip);
		$currenct_country_code = $location->getCountry();

		if (strtoupper($currenct_country_code) == 'IN') {
		    $store_code = 'default';
		} else if (strtoupper($currenct_country_code) == 'GB') {
		    $store_code = "store_view_uk";
		} else if (strtoupper($currenct_country_code) == 'US') {
		    $store_code = "store_view_us";
		} else {
		    $store_code = "store_view_international";
		}

		$current_store = $this->storeManager->getStore($store_code);
		$this->storeManager->setCurrentStore($current_store->getId());

		$currencies = $this->currencyBlock->getCurrencies();
		$current_currency_code = $this->currencyBlock->getCurrentCurrencyCode();
		foreach ($currencies as $_code => $_name){
			if($current_currency_code == $_code){
				$curr_currency_symbol = $this->currencyFactory->load($current_currency_code);
				$data = [
					'code' => $_code,
					'name' => $_name,
					'link' => '/directory/currency/switch/?currency='.$_code,
					'symbol' => $curr_currency_symbol->getCurrencySymbol(),
					'selected' => 1
				];
			}
			else{
				$symbol = $this->currencyFactory->load($_code);
				error_log("mgstesting :: ".json_encode(get_class_methods($symbol)));
				$data = [
					'code' => $_code,
					'name' => $_name,
					'link' => '/directory/currency/switch/?currency='.$_code,
					'symbol' => $symbol->getCurrencySymbol(),
					'selected' => 0
				];
			}
			array_push($currencyData, $data);
		}
		return $currencyData;
	}

}
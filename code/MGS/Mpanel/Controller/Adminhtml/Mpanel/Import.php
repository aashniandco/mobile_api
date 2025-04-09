<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Controller\Adminhtml\Mpanel;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
class Import extends \MGS\Mpanel\Controller\Adminhtml\Mpanel
{
	/**
     * Backend Config Model Factory
     *
     * @var \Magento\Config\Model\Config\Factory
     */
    protected $_configFactory;
	
	/**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $_string;
	
	/**
	 * @var \Magento\Framework\Xml\Parser
	 */
	private $_parser;
	
	protected $_filesystem;
	
	protected $_xmlArray;
	protected $_home;
	protected $_theme;
	protected $_storeManager;
	
	
	/**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(
		Action\Context $context,
		\Magento\Config\Model\Config\Factory $configFactory,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\Xml\Parser $parser,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\StringUtils $string
	){
        parent::__construct($context);
		$this->_configFactory = $configFactory;
        $this->_string = $string;
		$this->_filesystem = $filesystem;
		$this->_parser = $parser;
		$this->_storeManager = $storeManager;
    }
	
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if(($this->_theme = $this->getRequest()->getParam('theme')) && ($this->_home = $this->getRequest()->getParam('home'))){
			$dir = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/MGS/Mpanel/data/themes/'.$this->_theme.'/homes');
			$homepageFile = $dir.'/'.$this->_home.'.xml';
			
			if($websiteId = $this->getRequest()->getParam('website')){
				$stores = $this->_storeManager->getWebsite($websiteId)->getStores();
			}else{
				$stores = $this->_storeManager->getWebsite()->getStores();
			}
			$storeIds = [];
			foreach($stores as $_store){
				$storeIds[] = $_store->getId();
			}
			
			if (is_readable($homepageFile)){
				try {
					$this->_xmlArray = $this->_parser->load($homepageFile)->xmlToArray();
					/* Import Sections */
					
					$homeStores = $this->_objectManager->create('MGS\Mpanel\Model\Store')
						->getCollection();
					
					if($this->getRequest()->getParam('website')){
						$homeStores->addFieldToFilter('store_id', ['in'=>$storeIds]);
					}else{
						if($storeId = $this->getRequest()->getParam('store')){
							$homeStores->addFieldToFilter('store_id', $storeId);
						}
					}
					
					
					if (count($homeStores) > 0){
						foreach ($homeStores as $_homeStore){
							$_homeStore->delete();
						}
					}
					
					// Remove old sections
					$sections = $this->_objectManager->create('MGS\Mpanel\Model\Section')
						->getCollection();
					if($this->getRequest()->getParam('website')){
						$sections->addFieldToFilter('store_id', ['in'=>$storeIds]);
					}else{
						if($this->getRequest()->getParam('store')){
							$sections->addFieldToFilter('store_id', $storeId);
						}
					}

					if (count($sections) > 0){
						foreach ($sections as $_section){
							$_section->delete();
						}
					}
					
					// Remove old blocks
					$childs = $this->_objectManager->create('MGS\Mpanel\Model\Childs')
						->getCollection();
					if($this->getRequest()->getParam('website')){
						$childs->addFieldToFilter('store_id', ['in'=>$storeIds]);
					}else{
						if($this->getRequest()->getParam('store')){
							$childs->addFieldToFilter('store_id', $storeId);
						}
					}

					if (count($childs) > 0){
						foreach ($childs as $_child){
							$_child->delete();
						}
					}
					
					// Set use page builder for store view
					if($this->getRequest()->getParam('store')){
						$this->_objectManager->create('MGS\Mpanel\Model\Store')->setStoreId($storeId)->setStatus(1)->save();
					}
					else{
						foreach($storeIds as $_store){
							$this->_objectManager->create('MGS\Mpanel\Model\Store')->setStoreId($_store)->setStatus(1)->save();
						}
					}
					
					// Import new sections
					$sectionArray = $this->_xmlArray['home']['section'];
					if(isset($sectionArray)){
						if($this->getRequest()->getParam('store')){
							foreach($sectionArray as $section){
								$section['store_id'] = $storeId;
								$this->_objectManager->create('MGS\Mpanel\Model\Section')->setData($section)->save();
							}
						}
						else{
							foreach($storeIds as $_store){
								foreach($sectionArray as $section){
									$section['store_id'] = $_store;
									$this->_objectManager->create('MGS\Mpanel\Model\Section')->setData($section)->save();
								}
							}
						}
					}
					
					// Import new blocks
					$blockArray = $this->_xmlArray['home']['block'];
					if(isset($blockArray)){
						if($this->getRequest()->getParam('store')){
							foreach($blockArray as $block){
								$block['store_id'] = $storeId;
								$this->_objectManager->create('MGS\Mpanel\Model\Childs')->setData($block)->save();
							}
						}
						else{
							foreach($storeIds as $_store){
								foreach($blockArray as $block){
									$block['store_id'] = $_store;
									$this->_objectManager->create('MGS\Mpanel\Model\Childs')->setData($block)->save();
								}
							}
						}
					}

					$this->importPromoBanner();
					
					/* Import Theme Setting And Color Setting*/
					$this->_importSetting();
					
					$this->messageManager->addSuccess(__('%1 was successfully imported.', $this->convertString($this->_home)));
				}catch (\Exception $e) {
					// display error message
					$this->messageManager->addError($e->getMessage());
					//echo $e->getMessage();
				}
			}else{
				$this->messageManager->addError(__('Cannot import this homepage.'));
			}
		}else{
			$this->messageManager->addError(__('This homepage no longer exists.'));
		}
		$this->_redirect($this->_redirect->getRefererUrl());
		return;
    }
	
	public function convertString($theme){
		$themeName = str_replace('_',' ',$theme);
		return ucfirst($themeName);
	}
	
	/* Import Promotion Banners */
	public function importPromoBanner(){
		$parsedArray = $this->_xmlArray;
		if(isset($parsedArray['home']['promo_banner']['item'])){
			foreach($parsedArray['home']['promo_banner']['item'] as $banner){
				if(is_array($banner)){
					$identifier = $banner['identifier'];
					$bannerData = $banner;
				}else{
					$identifier = $parsedArray['home']['promo_banner']['item']['identifier'];
					$bannerData = $parsedArray['home']['promo_banner']['item'];
				}
				
				$banners = $this->_objectManager->create('MGS\Promobanners\Model\Promobanners')
					->getCollection()
					->addFieldToFilter('identifier', $identifier);
				if (count($banners) > 0){
					foreach ($banners as $_banner){
						$_banner->delete();
					}
				}
				
				$this->_objectManager->create('MGS\Promobanners\Model\Promobanners')->setData($bannerData)->save();
				
			}
		}
		return;
	}
	
	public function _importSetting(){
		/* Import Theme Setting */
		$this->imporSetting('theme_setting', 'mgstheme');
		
		/* Import Color Setting */
		$this->imporSetting('color_setting', 'color');
		
		/* Import Panel Setting */
		//$this->imporSetting('panel_setting', 'mpanel');
		
		return;
	}
	
	public function imporSetting($xmlNode, $section){
		$parsedArray = $this->_xmlArray;
		if(isset($parsedArray['home'][$xmlNode])){
			$website = $this->getRequest()->getParam('website');
			$store = $this->getRequest()->getParam('store');
			$groups = [];
			if(count($parsedArray['home'][$xmlNode])>0){
				foreach($parsedArray['home'][$xmlNode] as $groupName=>$_group){
					$fields = [];
					foreach($_group as $field=>$value){
						//if($value!=''){
							$fields[$field] = ['value'=>$value];
						//}
					}
					
					$groups[$groupName] = [
						'fields' => $fields
					];
				}
			}
			
			$configData = [
				'section' => $section,
				'website' => $website,
				'store' => $store,
				'groups' => $groups
			];

			/** @var \Magento\Config\Model\Config $configModel  */
			$configModel = $this->_configFactory->create(['data' => $configData]);
			$configModel->save();
		}
		return;
	}
	
	/**
     * Custom save logic for section
     *
     * @return void
     */
    protected function _saveSection()
    {
        $method = '_save' . $this->_string->upperCaseWords('design', '_', '');
        if (method_exists($this, $method)) {
            $this->{$method}();
        }
    }
}

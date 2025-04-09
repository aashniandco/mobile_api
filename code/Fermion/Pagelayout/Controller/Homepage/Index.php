<?php
namespace Fermion\Pagelayout\Controller\Homepage;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
    protected $_formKey;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
        $this->_formKey = $formKey;
		return parent::__construct($context);
	}

	public function execute(){
		$objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
        $storeObj = $objectManager->create('\Magento\Store\Model\StoreManagerInterface')->getStore();
        $storeId = $storeObj->getId();
        $formKey = $this->_formKey->getFormKey();
        $currencyCode = trim($storeObj->getCurrentCurrencyCode());
        $sRefFlag = isset($_GET['rf']) ? $_GET['rf'] : '';
        $currentPath = getcwd();
        $filePath = $currentPath.'/pub/media/pre_pages/';
        //$device = isset($_GET['eq']) ? $_GET['eq'] : '';
        $fileName = $filePath.'home_'.$storeId.'_'.$currencyCode.'.html';
        $homePageContent = '';



        $cacheInterface = $objectManager->get('Magento\Framework\App\CacheInterface');
        $homeCachekey = 'homepageepi_01_12'.$storeId.'_'.$currencyCode;
        
        $homeCacheData = $cacheInterface->load($homeCachekey);

		$fExists = file_exists($fileName);


		if (!empty($homeCacheData) && $sRefFlag != 'OGpgref2021' && 0){
			$homeArr = json_decode($homeCacheData, 1);
            $homePageContent = isset($homeArr['homepage_html']) ? $homeArr['homepage_html'] : '';

        }else{
                
            $resultPage = $this->_pageFactory->create();
            $headerHtml = $resultPage->getLayout()
            ->createBlock("Fermion\Pagelayout\Block\Home")
            ->setCurrentPage("cms_index_index")
            ->setTemplate('Fermion_Pagelayout::header.phtml')
            ->setFormKey($formKey)
            ->toHtml();        
             $contentHtml = $resultPage->getLayout()
                    ->createBlock("Fermion\Pagelayout\Block\Home")
                    ->setTemplate('Fermion_Pagelayout::homepage.phtml')
                    ->toHtml();
            $footerHtml = $resultPage->getLayout()
                ->createBlock("Fermion\Pagelayout\Block\Home")
                ->setCurrentPage("cms_index_index")
                ->setTemplate('Fermion_Pagelayout::footer.phtml')
                ->toHtml();

   	 		$homePageContent = $headerHtml.$contentHtml.$footerHtml;
            //$homePageContent = $headerHtml.$contentHtml;
   
    		$htmlArr['homepage_html'] = $homePageContent;
    		$cacheInterface->save(json_encode($htmlArr), $homeCachekey, array(),86400000);
            

        }
                
        header('Cache-Control: public');
        header('Pragma: public');
        header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 24 * 7))); // 7 days
        
        echo $homePageContent;
        die;
               
    }
}
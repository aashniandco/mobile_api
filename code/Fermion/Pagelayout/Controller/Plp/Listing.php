<?php 
namespace Fermion\Pagelayout\Controller\Plp;

class Listing extends \Magento\Framework\App\Action\Action {
    protected $_pageFactory;
    protected $_listMod;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,        
        \Fermion\Pagelayout\Model\Listing\ListInfo $list
    ) {
        $this->_pageFactory = $pageFactory;          
        $this->_listMod = $list;
        return parent::__construct($context);
    }

    public function execute() {
        $respArr = array();

        $objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
        $rows = 12;
        $requestParams = $this->getRequest()->getParam('req_data');
        $page_no = isset($_GET['req_data']['p_no']) ? $_GET['req_data']['p_no'] : '';
        $catId = isset($_GET['req_data']['c_id']) ? $_GET['req_data']['c_id'] : '';
        
        $filterParam = isset($requestParams['filt_to_apply']) ? $requestParams['filt_to_apply'] : array();
        
        $start = (int) (($page_no - 1) * $rows);
        if($catId != '' && $page_no != ''){
            $listMod = $objectManager->create('Fermion\Pagelayout\Model\Listing\ListInfo');
            // if (!empty($filterParam)) {
            //     $facet_to_filt = $listMod->sanitizeFacets($filterParam);
            //     print_r($facet_to_filt);die;

            // }  
            // die('----');
            // /* get filtered collection from solr*/          
            // $filt_data = $this->_listMod->getFilteredData($start, $rows, $cat_data, $facet_to_filt, $coun_code);
            $pageScroll = isset($requestParams['scroll_req']) ? $requestParams['scroll_req'] : 0;
            $filterParam['is_scroll_req'] = $pageScroll;
             //$requestParams = array();
            $listingBlock = $objectManager->create('Fermion\Pagelayout\Block\Index');
            $catData = $listingBlock->getListingPageData($catId,$filterParam,$start,1,$pageScroll);
            $respArr['cat_data'] = isset($catData['cat_data']) ? $catData['cat_data'] : array();
            $respArr['prod_grid_html'] = isset($catData['prod_grid_html']['prod_html']) ? $catData['prod_grid_html']['prod_html'] : '';
            $respArr['no_of_pages'] = isset($catData['no_of_pages']) ? $catData['no_of_pages'] : '';
            $respArr['filt_facets'] = isset($catData['facets']) ? $catData['facets'] : '';
            $respArr['error'] = 0;
            $respArr['msg'] = "success";
            
        }else{
            $respArr['error'] = 0;
            $respArr['msg'] = "Something went wrong.";
        }
        
       
        echo json_encode($respArr);die;
         
        


        try {                                              
            $is_search = 0;
            $start = 0;
            $rows = $this->_listMod->getPageLimit();
            $req_params = $this->getRequest()->getParams();
            $coun_code = isset($req_params["ci"]) ? $req_params["ci"] : "IN";
            $cat_id = isset($req_params["id"]) ? $req_params["id"] : "";
            if(!$this->canShow($cat_id)){
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $url = '/';
                    $resultRedirect->setUrl($url);
                    return $resultRedirect;
            }
            $cat_data = $this->_listMod->getCategoryData($cat_id); 
            $parentCatId = isset($cat_data['pare_cat_id']) ? $cat_data['pare_cat_id'] : ''; 
            $catLevel = isset($cat_data['cat_level']) ? $cat_data['cat_level'] : '';
            $catPath = isset($cat_data['cat_path']) ? $cat_data['cat_path'] : '';

            $filt_to_apply = $this->_listMod->getFiltersFromQuery($filterParam);    
            //print_r($filt_to_apply);die;
            /* get filtered collection from solr*/         
            $filt_data = $this->_listMod->getFilteredData($start, $rows, $cat_data, $filt_to_apply, $coun_code);
            /* get facets from collection */            
            $facets = $this->_listMod->filterResponseFacets($filt_data["facet_counts"]["facet_fields"], 1, isset($filt_to_apply["priceFilter"]) ? $filt_to_apply["priceFilter"] : '',$parentCatId,$cat_id,$catLevel,$catPath); 
            //print_r($facets);die;
            /* get product grid html from collection */
            $prod_grid_html = $this->_listMod->getProductGrid($filt_data["response"]["docs"], $cat_data);
            $prod_grid_data_html = isset($prod_grid_html['prod_html']) ? $prod_grid_html['prod_html'] : '';
            $prod_grid_script_html = isset($prod_grid_html['script_html']) ? $prod_grid_html['script_html'] : '';
            //echo json_encode($prod_grid_script_html);die();     
            /* total no of pages*/
            $no_docu = (int)$filt_data["response"]["numFound"];
            $pages = ceil($no_docu / $rows);
            /* get current store currency */
            $currency = $this->_listMod->getCurrentCurrencyCode();


            $breacrumbData = isset($cat_data['cat_bread_crumb']) ? $cat_data['cat_bread_crumb'] : array();
            $catSchemaArr = $this->_listMod->getBreadCrumbsListingSchema($cat_id,$breacrumbData);


            


            /* render listing page */
            $page = $this->_pageFactory->create(); 
            $header = $page->getLayout()
                            ->createBlock('Fermion\Pagelayout\Block\Media')
                            ->setCurrentPage('catalog_category_view')
                            ->setScriptHtml($prod_grid_script_html)
                            ->setBreadCrumbsSchema(json_encode(isset($catSchemaArr) ? $catSchemaArr : '')) 
                            ->setCurrentCategoryId(isset($cat_id) ? $cat_id : '')
                            ->setTemplate('Fermion_Pagelayout::header.phtml')
                            ->toHtml();
            $list_body = $page->getLayout()
                            ->createBlock('Fermion\Pagelayout\Block\Media')
                            ->setCategoryData(isset($cat_data) ? $cat_data : '')
                            ->setCountryCode($coun_code)
                            ->setIsSearchList($is_search)
                            ->setGridHtml($prod_grid_data_html)
                            ->setFacets($facets)
                            ->setNoOfPages($pages)
                            ->setStoreCurrency($currency)  
                            ->setPageLimit($rows)                         
                            ->setTemplate('Fermion_Pagelayout::listing/list.phtml')
                            ->toHtml(); 
            $footer = $page->getLayout()
                            ->createBlock('Fermion\Pagelayout\Block\Media')
                            ->setCurrentPage('catalog_category_view')
                            ->setPlpType("new")
                            ->setTemplate('Fermion_Pagelayout::footer.phtml')
                            ->toHtml();            
        header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 24 * 7))); // 7 days
        echo $header.$list_body.$footer;
        die;
            //$this->getResponse()->setBody($header.$list_body.$footer);
        } catch(\Exception $e) {
            echo $e->getMessage(); die();
        }        
    }

    public function canShow($cat_id){
        $objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('\Magento\Framework\App\ResourceConnection');
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getId();
        $connection = $resource->getConnection();
        $checkCategoryActive = 1;
            try {
                $sql = "SELECT VALUE,store_id FROM catalog_category_entity_int WHERE entity_id = ".$cat_id." AND attribute_id= 42 and store_id in(0,".$storeId.")";
                $isCategoryActive = $connection->fetchAll($sql);
                    foreach($isCategoryActive as $categoryActive){
                    $store_id = $categoryActive['store_id'];
                    $value = $categoryActive['VALUE'];
                        if(isset($store_id) && $store_id == $storeId){
                            $checkCategoryActive = $value;
                            break;
                        }else if(isset($store_id) && $store_id == 0){
                            $checkCategoryActive = $value;
                        }                  
                    }
            }catch (NoSuchEntityException $e) {
                return false;
            }
        return $checkCategoryActive;
    }
    
    public function getSearchPage($qTxt) {
        try {   
            
            
            $is_search = 1;
            $start = 0;
            $rows = $this->_listMod->getPageLimit();

            $req_params = $this->getRequest()->getParams();
            $coun_code = isset($req_params["ci"]) ? $req_params["ci"] : "IN";
            $cat_id = isset($req_params["id"]) ? $req_params["id"] : "";
            $pagetype = isset($req_params["ptype"]) ? $req_params["ptype"] : "ogaan";
            
            // $cat_data = $this->_listMod->getCategoryData($cat_id); 
            // $parentCatId = isset($cat_data['pare_cat_id']) ? $cat_data['pare_cat_id'] : ''; 
            // $catLevel = isset($cat_data['cat_level']) ? $cat_data['cat_level'] : '';
            // $catPath = isset($cat_data['cat_path']) ? $cat_data['cat_path'] : '';
            $filt_to_apply = $this->_listMod->getFiltersFromQuery($req_params); 

            //print_r($filt_to_apply);die;
            /* get filtered collection from solr*/         
            $filt_data = $this->_listMod->getSearchFilteredData($start, $rows,urlencode($qTxt), $filt_to_apply, $coun_code,$pagetype);

            
             
           
            /* get facets from collection */            
            $facets = $this->_listMod->searchfilterResponseFacets($filt_data["facet_counts"]["facet_fields"], 1, isset($filt_to_apply["priceFilter"]) ? $filt_to_apply["priceFilter"] : ''); 
           
            /* get product grid html from collection */
            $prod_grid_html = $this->_listMod->getProductGrid($filt_data["response"]["docs"]);   
            $prod_grid_data_html = isset($prod_grid_html['prod_html']) ? $prod_grid_html['prod_html'] : '';
            $prod_grid_script_html = isset($prod_grid_html['script_html']) ? $prod_grid_html['script_html'] : '';
           


            /* total no of pages*/
            $no_docu = (int)$filt_data["response"]["numFound"];
            $pages = ceil($no_docu / $rows);
            /* get current store currency */
            $currency = $this->_listMod->getCurrentCurrencyCode();

            /* render listing page */
            $page = $this->_pageFactory->create(); 
            $header = $page->getLayout()
                            ->createBlock('Fermion\Pagelayout\Block\Media')
                            ->setCurrentPage('catalog_category_view')
                            ->setCurrentCategoryId(isset($cat_id) ? $cat_id : '')
                            ->setScriptHtml(json_encode($prod_grid_script_html))
                            ->setQueryText($qTxt)
                            ->setTemplate('Fermion_Pagelayout::header.phtml')
                            ->toHtml();
            $list_body = $page->getLayout()
                            ->createBlock('Fermion\Pagelayout\Block\Media')
                            ->setCategoryData(isset($cat_data) ? $cat_data : '')
                            ->setCountryCode($coun_code)
                            ->setIsSearchList($is_search)
                            ->setSearchTerms($qTxt)
                            ->setGridHtml($prod_grid_data_html)
                            ->setFacets($facets)
                            ->setNoOfPages($pages)
                            ->setStoreCurrency($currency)  
                            ->setPageLimit($rows)                         
                            ->setTemplate('Fermion_Pagelayout::listing/search.phtml')
                            ->toHtml(); 

            $footer = $page->getLayout()
                            ->createBlock('Fermion\Pagelayout\Block\Media')
                            ->setCurrentPage('catalog_category_view')
                            ->setPlpType("new")
                            ->setTemplate('Fermion_Pagelayout::footer.phtml')
                            ->toHtml();  
             return($header.$list_body.$footer);          
            $this->getResponse()->setBody($header.$list_body.$footer);
        } catch(\Exception $e) {
            echo "<h1>Page Under Updation</h1>";            
            echo $e->getMessage(); die();
        }        
    }
}
?>

<?php
namespace Fermion\Pagelayout\Block;
class Search extends \Magento\Framework\View\Element\Template
{
    protected $_catdata = null;
    protected $_solrFilterData = null;
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,        
        \Fermion\Pagelayout\Model\Listing\ListInfo $list
    ) {
        $this->_pageFactory = $pageFactory;          
        $this->_listMod = $list;
        return parent::__construct($context);
    }

    public function _prepareLayout()
        {

            //error_log("---------breadcrum----------");
            // $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
            // $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
            // $catId = $this->getRequest()->getParam('id');
            
            // $cat_data = $this->_listMod->getCategoryData($catId);
            // $this->_catdata = $cat_data;
            // //error_log(json_encode($cat_data));
            // $categoryName = isset($cat_data['cat_name']) ? $cat_data['cat_name'] : '';
            // if ($breadcrumbsBlock) {
    
            //     $breadcrumbsBlock->addCrumb(
            //         'home',
            //         [
            //         'label' => __('Home'), //lable on breadCrumbes
            //         'title' => __('Home'),
            //         'link' => $baseUrl
            //         ]
            //     );
            //     $breadcrumbsBlock->addCrumb(
            //         'coderkube',
            //         [
            //         'label' => __($categoryName ),
            //         'title' => __($categoryName ),
            //         'link' => '' //set link path
            //         ]
            //     );
            // }
            //$this->pageConfig->getTitle()->set(__('Lehenga')); // set page name
            return parent::_prepareLayout();
        }

	public function getSearchPageData($searchString,$req_params,$start=0,$isAjax=0,$pageScroll=0
){
		
        
        $rows = $this->_listMod->getPageLimit();
       
		

		
        if($isAjax == 1){
            $filt_to_apply = $this->_listMod->sanitizeFacets($req_params);
            $filt_to_apply['is_scroll_req'] = isset($filt_to_apply['is_scroll_req']) ? $filt_to_apply['is_scroll_req'] :$pageScroll; 
           
        }else{
            $filt_to_apply = $this->_listMod->getFiltersFromQuery($req_params);
        }
        //echo $searchString;die;
        $filt_data = $this->_listMod->getSearchFilteredData($start, $rows, $searchString, $filt_to_apply,'');
        $facets = '';
        if(isset($filt_to_apply['is_scroll_req']) && $filt_to_apply['is_scroll_req'] != 1){ 
            $facets = $this->_listMod->searchfilterResponseFacets(isset($filt_data["facet_counts"]["facet_fields"]) ? $filt_data["facet_counts"]["facet_fields"] : array(), 1, isset($filt_to_apply["priceFilter"]) ? $filt_to_apply["priceFilter"] : '');
        }
        
        

		 

        $prod_grid_html = $this->_listMod->getProductGrid($filt_data["response"]["docs"],array());
            $prod_grid_data_html = isset($prod_grid_html['prod_html']) ? $prod_grid_html['prod_html'] : '';
            $prod_grid_script_html = isset($prod_grid_html['script_html']) ? $prod_grid_html['script_html'] : '';
             
            /* total no of pages*/
            $no_docu = (int)$filt_data["response"]["numFound"];
            $pages = ceil($no_docu / $rows);

            $response = array();

            
            $response['prod_grid_html'] = $prod_grid_html;
            $response['no_of_pages'] = $pages;
            $response['facets'] = $facets;
            return $response;
	}


    public function getSearchFilterdata($searchString,$requestParams){
        if(is_null($this->_solrFilterData)){
            error_log("======get data from solr===========");
            $response = $this->getSearchPageData($searchString,$requestParams);
            $this->_solrFilterData = $response;

        }
        //error_log("======get data from solr==2=========");
        return $this->_solrFilterData;
    }
}

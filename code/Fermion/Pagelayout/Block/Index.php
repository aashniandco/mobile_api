<?php
namespace Fermion\Pagelayout\Block;
class Index extends \Magento\Framework\View\Element\Template
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
            $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
            $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
            $catId = $this->getRequest()->getParam('id');
            
            // $cat_data = $this->_listMod->getCategoryData($catId);
            $cat_data = $this->_listMod->getCategoryFilteredDataSolr($catId);
            if(is_null($cat_data) || count($cat_data) <= 0){
            $cat_data = $this->_listMod->getCategoryData($catId);
            }
            $this->_catdata = $cat_data;
            //error_log(json_encode($cat_data));
            $categoryName = isset($cat_data['cat_name']) ? $cat_data['cat_name'] : '';
            $metaTitle = isset($cat_data['meta_title']) && !empty($cat_data['meta_title']) ? $cat_data['meta_title'] : $categoryName;
            if ($breadcrumbsBlock) {
    
                $breadcrumbsBlock->addCrumb(
                    'home',
                    [
                    'label' => __('Home'), //lable on breadCrumbes
                    'title' => __('Home'),
                    'link' => $baseUrl
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'coderkube',
                    [
                    'label' => __($categoryName ),
                    'title' => __($categoryName ),
                    'link' => '' //set link path
                    ]
                );
            }
            //error_log("-------------meta title---------".$metaTitle);
            $this->pageConfig->getTitle()->set(__($metaTitle)); // set page name

            $metadescription = isset($cat_data['meta_description']) ? $cat_data['meta_description'] : '';
            $metakeywords = isset($cat_data['meta_keywords']) ? $cat_data['meta_keywords'] : '';
            $this->pageConfig->setDescription($metadescription);
            
            $this->pageConfig->setKeywords($metakeywords);
            return parent::_prepareLayout();
        }

	public function getListingPageData($cat_id,$req_params,$start=0,$isAjax=0,$pageScroll=0
){
		
        $rows = $this->_listMod->getPageLimit();
       if((!is_null($this->_catdata)) && isset($this->_catdata) && !empty($this->_catdata)){
            $cat_data = $this->_catdata;

            //error_log("==category data===if======".json_encode($this->_catdata));
       }else{
            $cat_data = $this->_listMod->getCategoryData($cat_id);
       }
		

		$parentCatId = isset($cat_data['pare_cat_id']) ? $cat_data['pare_cat_id'] : ''; 
        $catLevel = isset($cat_data['cat_level']) ? $cat_data['cat_level'] : '';
        $catPath = isset($cat_data['cat_path']) ? $cat_data['cat_path'] : '';
        if($isAjax == 1){
            $filt_to_apply = $this->_listMod->sanitizeFacets($req_params);
            $filt_to_apply['is_scroll_req'] = isset($filt_to_apply['is_scroll_req']) ? $filt_to_apply['is_scroll_req'] :$pageScroll; 
           
        }else{
            $filt_to_apply = $this->_listMod->getFiltersFromQuery($req_params);
        }
        
        $filt_data = $this->_listMod->getFilteredData($start, $rows, $cat_data, $filt_to_apply,'');
        $facets = '';
        if(isset($filt_to_apply['is_scroll_req']) && $filt_to_apply['is_scroll_req'] != 1){ 
            $facets = $this->_listMod->filterResponseFacets($filt_data["facet_counts"]["facet_fields"], 1, isset($filt_to_apply["priceFilter"]) ? $filt_to_apply["priceFilter"] : '',$parentCatId,$cat_id,$catLevel,$catPath);
            if($cat_id != 6397){
                unset($facets['navratri_colors']);
            }
        }
        
        

		 

        $prod_grid_html = $this->_listMod->getProductGrid($filt_data["response"]["docs"], $cat_data);
            $prod_grid_data_html = isset($prod_grid_html['prod_html']) ? $prod_grid_html['prod_html'] : '';
            $prod_grid_script_html = isset($prod_grid_html['script_html']) ? $prod_grid_html['script_html'] : '';
             
            /* total no of pages*/
            $no_docu = (int)$filt_data["response"]["numFound"];
            $pages = ceil($no_docu / $rows);

            $response = array();

            $response['cat_data'] = $cat_data;
            $response['prod_grid_html'] = $prod_grid_html;
            $response['no_of_pages'] = $pages;
            $response['facets'] = $facets;
            return $response;
	}


    public function getListingFilterdata($catId,$requestParams){
        if(is_null($this->_solrFilterData)){
            //error_log("======get data from solr===========");
            $response = $this->getListingPageData($catId,$requestParams);
            $this->_solrFilterData = $response;

        }
       // error_log("======get data from solr==2=========");
        return $this->_solrFilterData;
    }
}

<?php 
namespace Fermion\Pagelayout\Controller\Search;

class Pagerequest extends \Magento\Framework\App\Action\Action {
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
        $searchString = isset($_GET['q']) ? $_GET['q'] : '';
        
        $filterParam = isset($requestParams['filt_to_apply']) ? $requestParams['filt_to_apply'] : array();
        
        $start = (int) (($page_no - 1) * $rows);
        if($searchString != '' && $page_no != ''){
            $listMod = $objectManager->create('Fermion\Pagelayout\Model\Listing\ListInfo');
            
            $pageScroll = isset($requestParams['scroll_req']) ? $requestParams['scroll_req'] : 0;
            $filterParam['is_scroll_req'] = $pageScroll;
             //$requestParams = array();
            $searchBlock = $objectManager->create('Fermion\Pagelayout\Block\Search');
            $searchData = $searchBlock->getSearchPageData($searchString,$filterParam,$start,1,$pageScroll);
            // $respArr['cat_data'] = isset($catData['cat_data']) ? $catData['cat_data'] : array();
            $respArr['prod_grid_html'] = isset($searchData['prod_grid_html']['prod_html']) ? $searchData['prod_grid_html']['prod_html'] : '';
            $respArr['no_of_pages'] = isset($searchData['no_of_pages']) ? $searchData['no_of_pages'] : '';
            $respArr['filt_facets'] = isset($searchData['facets']) ? $searchData['facets'] : '';
            $respArr['error'] = 0;
            $respArr['msg'] = "success";
            
        }else{
            $respArr['error'] = 0;
            $respArr['msg'] = "Something went wrong.";
        }
        
        echo json_encode($respArr);die;
                
    }

    
}

?>

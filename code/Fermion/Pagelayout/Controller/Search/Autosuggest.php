<?php 
namespace Fermion\Pagelayout\Controller\Search;
use Magento\Framework\App\ObjectManager;
class Autosuggest extends \Magento\Framework\App\Action\Action {
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
        $searchString = isset($_GET['q']) ? $_GET['q'] : '';
        $rawSearchText = $searchString;
        

        if($searchString != ''){
            $searchCategoryHtml = '';
            $searchString = preg_replace('/\s+/', ' ', $searchString);
            $searchString = trim($searchString);
            $parentsString = '(1375 OR 3374 OR 1374 OR 1380 OR 1381 OR 1393 OR 1392)';
            $encodedParentsString = rawurlencode($parentsString);
            if (strpos($searchString,  ' ' ) > 0){
                $searchtext = str_replace(' ', '* ', $searchString);
                $query = 'q=cat_name:('.rawurlencode($searchtext).'*)&fq=all_parents_id:'. $encodedParentsString .'&fq=cat_level:3&rows=1000';
            }else{
                 $query = 'q=cat_name:('.rawurlencode($searchString).'*)&fq=all_parents_id:'. $encodedParentsString .'&fq=cat_level:3&rows=1000';
            }

            if(strpos($searchString, 'gift') !== false || strpos($searchString, 'card') !== false){
                $giftcardQuery = 'fl=prod_url_1,prod_name&q=prod_type:mageworx_giftcards';
                $giftCardsData = $objectManager->create('\Fermion\Pagelayout\Helper\SolrHelper')->getFilterCollection($giftcardQuery);
                $giftCardsData = json_decode($giftCardsData, 1);
                $giftCards = isset($giftCardsData['response']['docs']) ? $giftCardsData['response']['docs'] : array();
                if(!empty($giftCards)){
                    $searchCategoryHtml .= '<div class="cat-list"><div class="sub-head">Gift Cards</div>';
                    foreach ($giftCards as $key => $value) {
                        $searchCategoryHtml .= '<div class="list"><a href="'.$value['prod_url_1'][0].'">'.$value['prod_name'][0].'</a></div>';
                    }
                    $searchCategoryHtml .='</div>';
                }
            }
            
            // echo $query;die;
            $catData = $objectManager->create('\Fermion\Pagelayout\Helper\SolrHelper')->getCatCollection($query);

            $catArr = json_decode($catData,1);
            
            $result = isset($catArr['response']['docs']) ? $catArr['response']['docs'] : array();
            $designerId = 1375;
            $newCategorySearchArr =  array();
            $newCategoryArr =  array();
            $newCatArr = array();
            $designerCount = 0;
            $respArr['title_text'] = 'CATEGORIES';

            // $attQuery = "select group_concat(entity_id) as category_id from catalog_category_entity_int where attribute_id = (SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'show_in_filters') and value = 0";
            //     $resourceConnection = ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
            //     $connection = $resourceConnection->getConnection();
            //     $excludeCatArr = $connection->fetchAll($attQuery);
            //     $excludeCatIds = array();
            //     if(isset($excludeCatArr[0]['category_id']) && !empty($excludeCatArr[0]['category_id'])){
            //         $excludeCatIds = explode(",",$excludeCatArr[0]['category_id']);
            //     }

                if(isset($_GET['otest']) && $_GET['otest'] == 'ntest') {
                    // print_r($result);
                 
                    // echo $query;die;
                }
            if(!empty($result)){
                $result = array_slice($result, 0, 6);
                foreach ($result as $cdata) {
                // print_r($cdata);
                    $categoryName = isset($cdata['cat_name'][0]) ? $cdata['cat_name'][0] : '';
                    $categoryUrl = isset($cdata['cat_url'][0]) ? $cdata['cat_url'][0] : '';
                    $categoryParentId = isset($cdata['cat_parent_id'][0]) ? $cdata['cat_parent_id'][0] : '';
                    $categoryId = isset($cdata['cat_en_id_int'][0]) ? $cdata['cat_en_id_int'][0] : '';
                    // if(in_array($categoryId,$excludeCatIds)){
                    //    // continue;
                    // }
                    if(in_array(1375,$cdata['all_parents_id'])){
                        $headerName = "Designer";
                        $newCatArr[1]['parent_cat_name'] = "Designer";
                        $newCatArr[1]['child_cat'][$categoryId]['name'] = $categoryName;
                        $newCatArr[1]['child_cat'][$categoryId]['url'] = $categoryUrl;

                    }elseif (in_array(3374,$cdata['all_parents_id'])) {
                        $newCatArr[2]['parent_cat_name'] = "Women's Clothing";
                        $newCatArr[2]['child_cat'][$categoryId]['name'] = $categoryName;
                        $newCatArr[2]['child_cat'][$categoryId]['url'] = $categoryUrl;
                    }elseif (in_array(1374,$cdata['all_parents_id'])) {
                        $newCatArr[5]['parent_cat_name'] = "Accessories";
                        $newCatArr[5]['child_cat'][$categoryId]['name'] = $categoryName;
                        $newCatArr[5]['child_cat'][$categoryId]['url'] = $categoryUrl;
                    }elseif (in_array(1380,$cdata['all_parents_id'])) {
                        $newCatArr[4]['parent_cat_name'] = "Kids Wear";
                        $newCatArr[4]['child_cat'][$categoryId]['name'] = $categoryName;
                        $newCatArr[4]['child_cat'][$categoryId]['url'] = $categoryUrl;
                    }elseif (in_array(1381,$cdata['all_parents_id'])) {
                        $newCatArr[3]['parent_cat_name'] = "Men";
                        $newCatArr[3]['child_cat'][$categoryId]['name'] = $categoryName;
                        $newCatArr[3]['child_cat'][$categoryId]['url'] = $categoryUrl;
                    }elseif (in_array(1393,$cdata['all_parents_id'])) {
                        $newCatArr[6]['parent_cat_name'] = "Homeware";
                        $newCatArr[6]['child_cat'][$categoryId]['name'] = $categoryName;
                        $newCatArr[6]['child_cat'][$categoryId]['url'] = $categoryUrl;
                    }
                }
                ksort($newCatArr);
                
                    foreach($newCatArr as $parentCatId => $catArrData){
                        $parentCatName = isset($catArrData['parent_cat_name']) ? $catArrData['parent_cat_name'] : '';
                        if($parentCatName == "Designer"){
                            $designerCount++;
                        }
                        // $searchCategoryHtml .= '<div class="cat-list"><div class="sub-head">'.$parentCatName.'</div>';
                        $searchCategoryHtml .= '<div class="cat-list">';
                        $childCatArr = isset($catArrData['child_cat']) ? $catArrData['child_cat'] : array();
                        foreach($childCatArr as $catKey => $catVal){
                            $childCatUrl = isset($catVal['url']) ? $catVal['url'] : '';
                            $childCatName = isset($catVal['name']) ? $catVal['name'] : '';

                            // $searchCategoryHtml .= '<div class="list"><a href="'.$childCatUrl.'">'.$childCatName.'</a></div>';
                            $searchCategoryHtml .= '<div class="list test2"><a href="'.$childCatUrl.'"><span>'.$parentCatName.' /</span><span class="categorysearchresults">'.$childCatName.'</span></a></div>';
                            if(strtolower($childCatName) == strtolower($rawSearchText)){
                                if(strpos($childCatUrl, 'designers') > 0 && strlen($childCatUrl) >= 10){
                                    if(!isset($respArr["designer_url"])){
                                        if(strpos($childCatUrl, '.html') > 0){
                                            if(strpos($childCatUrl, 'https') === 0){
                                                $respArr["designer_url"] = $childCatUrl;
                                            }
                                            else{
                                                $respArr["designer_url"] = "/".$childCatUrl;
                                            } 
                                        }
                                        else{
                                            if(strpos($childCatUrl, 'https') === 0){
                                                $respArr["designer_url"] = $childCatUrl.".html";
                                            }
                                            else{
                                                $respArr["designer_url"] = "/".$childCatUrl.".html";
                                            }
                                        }
                                    }
                                }
                            }

                        }
                        $searchCategoryHtml .='</div>';
                    }
                    if($designerCount == count($newCatArr)){
                        $respArr['title_text'] = 'DESIGNERS';
                    }
                    else{
                        $respArr['title_text'] = 'CATEGORIES';
                    }
            }
         

            $listMod = $objectManager->create('Fermion\Pagelayout\Model\Listing\ListInfo');
            
            $pageScroll = isset($requestParams['scroll_req']) ? $requestParams['scroll_req'] : 0;
            $filterParam['is_scroll_req'] = $pageScroll;
             //$requestParams = array();
            $solrModel = $objectManager->create('Fermion\Pagelayout\Model\Listing\ListInfo');
            $searchData = $solrModel->getSearchSuggestionData($searchString);

            if(isset($_GET['otest']) && $_GET['otest'] == 'ntest') {
                     print_r($searchData);die;
                 
                    // echo $query;die;
                }

            $facetField = isset($searchData["facet_counts"]["facet_fields"]) ? $searchData["facet_counts"]["facet_fields"] : array();
            $facets = $this->searchSuggestionFacets($facetField);
            $productsData = isset($searchData["response"]["docs"]) ? $searchData["response"]["docs"] : array();
            $productTotalcount = isset($searchData["response"]["numFound"]) ? $searchData["response"]["numFound"] : 0;

            if($searchCategoryHtml == ''){
                $newCatArr = array();
                foreach($productsData as $prod){
                    $catToken = isset($prod['categories-store-1_token']) ? $prod['categories-store-1_token'] : array();
                    if(strtolower($prod['prod_name'][0]) == strtolower($rawSearchText)){
                        foreach ($prod['categories-store-1_url_path'] as $key => $value) {
                            if(strpos($value, 'designers') === 0 && strlen($value) >= 10){
                                if(!isset($respArr["designer_url"])){
                                    if(strpos($value, '.html') > 0){
                                        $respArr["designer_url"] = "/".$value;
                                    }
                                    else{
                                        $respArr["designer_url"] = "/".$value.".html";
                                    }
                                }
                            }
                        }
                    }

                    foreach($catToken as $tokenval){

                        if($tokenval != ''){
                            $tokenArr = explode("|",$tokenval);
                            $catPath = isset($tokenArr[0]) ? $tokenArr[0] : '';
                            $catPathArr = explode("/",$catPath);
                            if(isset($catPathArr[3]) && $catPathArr[3] != '' && isset($catPathArr[2]) && $catPathArr[2] != '' && isset($tokenArr[1]) && isset($tokenArr[2]) && $tokenArr[1] != '' && $tokenArr[2] != ''){

                                // if(in_array($catPathArr[3],$excludeCatIds)){
                                //     continue;
                                // }
                                
                                if($catPathArr[2] == 1375){
                                    $newCatArr[6]['parent_cat_name'] = "Designer";
                                    $newCatArr[6]['child_cat'][$catPathArr[3]]['name'] = $tokenArr[2];
                                    $newCatArr[6]['child_cat'][$catPathArr[3]]['url'] = "https://aashniandco.com/".$tokenArr[1].".html";

                                }elseif ($catPathArr[2] == 3374) {
                                    $newCatArr[1]['parent_cat_name'] = "Women's Clothing";
                                    $newCatArr[1]['child_cat'][$catPathArr[3]]['name'] = $tokenArr[2];
                                    $newCatArr[1]['child_cat'][$catPathArr[3]]['url'] = "https://aashniandco.com/".$tokenArr[1].".html";
                                }elseif ($catPathArr[2] == 1374) {
                                    $newCatArr[4]['parent_cat_name'] = "Accessories";
                                    $newCatArr[4]['child_cat'][$catPathArr[3]]['name'] = $tokenArr[2];
                                    $newCatArr[4]['child_cat'][$catPathArr[3]]['url'] = "https://aashniandco.com/".$tokenArr[1].".html";
                                }elseif ($catPathArr[2] == 1380) {
                                    $newCatArr[3]['parent_cat_name'] = "Kids Wear";
                                    $newCatArr[3]['child_cat'][$catPathArr[3]]['name'] = $tokenArr[2];
                                    $newCatArr[3]['child_cat'][$catPathArr[3]]['url'] = "https://aashniandco.com/".$tokenArr[1].".html";
                                }elseif ($catPathArr[2] == 1381) {
                                    $newCatArr[2]['parent_cat_name'] = "Men";
                                    $newCatArr[2]['child_cat'][$catPathArr[3]]['name'] = $tokenArr[2];
                                    $newCatArr[2]['child_cat'][$catPathArr[3]]['url'] = "https://aashniandco.com/".$tokenArr[1].".html";
                                }elseif ($catPathArr[2] == 1393) {
                                    $newCatArr[5]['parent_cat_name'] = "Homeware";
                                    $newCatArr[5]['child_cat'][$catPathArr[3]]['name'] = $tokenArr[2];
                                    $newCatArr[5]['child_cat'][$catPathArr[3]]['url'] = "https://aashniandco.com/".$tokenArr[1].".html";
                                }
                            }
                        }
                    }
                    
                }
                ksort($newCatArr);

                foreach($newCatArr as $parentCatId => $catArrData){
                    $parentCatName = isset($catArrData['parent_cat_name']) ? $catArrData['parent_cat_name'] : '';
                     // $searchCategoryHtml .= '<div class="cat-list"><div class="sub-head">'.$parentCatName.'</div>';
                    $searchCategoryHtml .= '<div class="cat-list">';
                    $childCatArr = isset($catArrData['child_cat']) ? $catArrData['child_cat'] : array();
                    foreach($childCatArr as $catKey => $catVal){
                        $childCatUrl = isset($catVal['url']) ? $catVal['url'] : '';
                        $childCatName = isset($catVal['name']) ? $catVal['name'] : '';

                        // $searchCategoryHtml .= '<div class="list"><a href="'.$childCatUrl.'">'.$childCatName.'</a></div>';
                        $searchCategoryHtml .= '<div class="list test2"><a href="'.$childCatUrl.'"><span>'.$parentCatName.' /</span><span class="categorysearchresults">'.$childCatName.'</span></a></div>';

                    }
                    $searchCategoryHtml .= '</div>';
                }
            }

            if($productTotalcount > 0){
                $respArr["SearchCategoryHtml"] = $searchCategoryHtml;
                // $respArr["occasions"] = isset($facets['occasions']) ? array_slice($facets['occasions'], 0,4) : array();
                $occasions = isset($facets['occasions']) ? $facets['occasions'] : array();
                // 
                // array_rand($occasions);
                // print_r($occasions);
                $respArr['occasions'] = array_slice($occasions, 0, 4, true);
                $respArr["totalItems"] = $productTotalcount;
                $respArr["query"] = $searchString;
                $respArr["indices"][] = array("identifier"=>"magento_catalog_product","title"=>"Products","order"=>3,
        "items"=>$solrModel->getSearchSuggestItemHtml($productsData),"totalItems"=> $productTotalcount,"isShowTotals"=> true);
                $respArr["noResults"] = false;
                $respArr["urlAll"] = "/catalogsearch/result/?q=".$searchString;
                $respArr["optimize"] = true;
                $respArr["isMisspelled"] = false;
                $respArr["textMisspelled"] = "Your search returned no results. Did you mean \"".$searchString."\" ?";
                $respArr["textAll"] = "Show all ".$productTotalcount." results →";
                $respArr["textEmpty"] = "Sorry, nothing has been found for \"".$searchString."\".";
            }else{
                $respArr["totalItems"] = 0;
                $respArr["query"] = $searchString;
                $respArr["indices"][] = array("identifier"=>"magento_catalog_product","title"=>"Products","order"=>3,
        "items"=> [],"totalItems"=> 0,"isShowTotals"=> true);
                $respArr["noResults"] = true;
                $respArr["urlAll"] = "/catalogsearch/result/?q=".$searchString;
                $respArr["optimize"] = true;
                $respArr["isMisspelled"] = true;
                $respArr["textMisspelled"] = "Your search returned no results. Did you mean \"".$searchString."\" ?";
                $respArr["textAll"] = "Show all 0 results \u2192";
                $respArr["textEmpty"] = "Sorry, nothing has been found for \"".$searchString."\".";
            }
           
            // $respArr['error'] = 0;
            // $respArr['msg'] = "success";
            
        }else{
            $respArr['error'] = 1;
            $respArr['msg'] = "Something went wrong.";
        }
        
       
        echo json_encode($respArr);die;
         
             
    }

    public function searchSuggestionFacets($facet_arr){
        // print_r($facet_arr);
        $st_id = 1;
        $all_facets_arr = [];
        $occ_id_arr = [];
        $occ_name_arr = [];
         $newOccasionArr = array();
        $newOccasion = array();
        if (!empty($facet_arr)) {
            foreach ($facet_arr as $f_k => $f_arr) {
                if (!empty($f_arr)) {
                    foreach ($f_arr as $sf_k => $fac_v) {
                        if ($sf_k % 2 == 0 && $fac_v != NULL) {
                            if ($f_k == "occasion_token") {
                                $occ_da = explode('|', $fac_v);
                                array_push($occ_id_arr, $occ_da[0]);
                                array_push($occ_name_arr, strtoupper($occ_da[1]));
                                $newOccasion['Id']=$occ_da[0];
                                 $newOccasion['name'] = $occ_da[1];
                                $newOccasionArr[$occ_da[0]] = $newOccasion;
                            }                       
                        }
                    }
                }
            }
        }
        if (!empty($occ_id_arr) && !empty($occ_name_arr)) {
            $occ_arr = array_combine($occ_id_arr, $occ_name_arr);   
            asort($occ_arr);
        }
        
        
        if (isset($newOccasionArr)) {
            if(isset($newOccasionArr[6055])){
                unset($newOccasionArr[6055]);
            }if(isset($newOccasionArr[6056])){
                unset($newOccasionArr[6056]);
            }
             shuffle($newOccasionArr);
             // print_r($occ_arr);
            $all_facets_arr["occasions"] = $newOccasionArr;    
        }
        // print_r($all_facets_arr);
        return $all_facets_arr;
    }
    
    
}
?>

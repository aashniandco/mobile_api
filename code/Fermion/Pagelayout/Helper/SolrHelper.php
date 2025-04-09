<?php 
namespace Fermion\Pagelayout\Helper;

class SolrHelper extends \Magento\Framework\App\Helper\AbstractHelper {
    protected $_solrUtil;
    const SOLR_UPDATE_URL = "http://127.0.0.1:8983/solr/aashni_dev/update";
    
    const QUERY_URL = "http://127.0.0.1:8983/solr/aashni_dev/select";
    const SOLR_SEARCH_UPDATE_URL = "http://127.0.0.1:8983/solr/aashni_cat/update";
    const SEARCH_QUERY_URL = "http://127.0.0.1:8983/solr/aashni_cat/select";
    
    
    public function __construct(
        \Fermion\Pagelayout\Model\Solr\SolrUtil $solrUtil
    ) {
        $this->_solrUtil = $solrUtil;       
    }

    /* convert product collection array into xml and post for indexing to solr */
    public function indexDataForSearch($searchData = array()) {     
        // print_r($searchData);die;
        if (!empty($searchData)) {          
            $sOutputXml = $this->_solrUtil->generateXMLFormatData($searchData);            
        $responce = '';
        $responce = $this->postToSearchIndex($sOutputXml);
        echo "resp::".$responce."\n";
        }
    }

    /* convert product collection array into xml and post for indexing to solr */
    public function indexDataForCatSearch($searchData = array()) {   
        if (!empty($searchData)) {   
            $sOutputXml = $this->_solrUtil->generateCatXMLFormatData($searchData);
            $responce = $this->postToCatSearchIndex($sOutputXml);
        }
    }

    public function indexDataForChild($searchData = array()) {     
        if (!empty($searchData)) {          
            $sOutputXml = $this->_solrUtil->generateXMLFormatData($searchData);  

                     
            $responce = $this->postToChildIndex($sOutputXml);
        }
    }

      /* post data for indexing to solr */
    public function postToSearchIndex($xmlDoc) {
        $solrUrl = self::SOLR_UPDATE_URL;        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $solrUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Agent: ' . phpversion());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlDoc);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type:text/xml; charset=utf-8'));
        $content = curl_exec($ch);
        $response = curl_getinfo($ch);
        // echo "response ========\n";
        // print_r($response);

        curl_close($ch);
    echo "resp IN postToSearchIndex ::".$content."\n";
        return $content;
    }


    /* post data for indexing to solr */
    public function postToCatSearchIndex($xmlDoc) {
        $solrUrl = self::SOLR_SEARCH_UPDATE_URL;        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $solrUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Agent: ' . phpversion());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlDoc);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type:text/xml; charset=utf-8'));
        $content = curl_exec($ch);
        $response = curl_getinfo($ch);
        curl_close($ch);
        return $content;
    }

    public function postToChildIndex($xmlDoc) {
        $solrUrl = self::SOLR_CHILD_UPDATE_URL;        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $solrUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Agent: ' . phpversion());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlDoc);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type:text/xml; charset=utf-8'));
        $content = curl_exec($ch);
        $response = curl_getinfo($ch);
        curl_close($ch);
        print_r($content);
        return $content;
    }

    /* commit data for indexing */
    public function commitToSearchIndex() {
        $solrUrl = self::SOLR_UPDATE_URL;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $solrUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Agent: ' . phpversion());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '<commit/>');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type:text/xml; charset=utf-8'));
        $content = curl_exec($ch);
        $response = curl_getinfo($ch);
        curl_close($ch);
        return $content;
    }

     public function commitToCatSearchIndex() {
        $solrUrl = self::SOLR_SEARCH_UPDATE_URL;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $solrUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Agent: ' . phpversion());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '<commit/>');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type:text/xml; charset=utf-8'));
        $content = curl_exec($ch);
        $response = curl_getinfo($ch);
        curl_close($ch);
        return $content;
    }

    public function commitToChildIndex() {
        $solrUrl = self::SOLR_CHILD_UPDATE_URL;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $solrUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Agent: ' . phpversion());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '<commit/>');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type:text/xml; charset=utf-8'));
        $content = curl_exec($ch);
        $response = curl_getinfo($ch);
        curl_close($ch);
        print_r($content);
        return $content;
    }

    /* return data from solr */
    public function getFilterCollection($appliedConditions) {
        $solrUrl = self::QUERY_URL . "?$appliedConditions"; 
        $solrUrl = self::QUERY_URL . "?$appliedConditions";    
        // if(isset($_GET['req_data']['filt_to_apply']['test'])){
        //     echo $solrUrl;

        //     die("here==");
        // }   
        if(isset($_GET['test'])){
            echo $solrUrl;

            die("here==");
        } 

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $solrUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Agent: ' . phpversion());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);                        
        $content = curl_exec($ch);        
        curl_close($ch);
        return $content;
    }

    public function getCatCollection($appliedConditions) {
        
        $solrUrl = self::SEARCH_QUERY_URL . "?$appliedConditions";   
         
        // if(isset($_GET['req_data']['filt_to_apply']['test']) || 1){
        //     echo $solrUrl;

        //     die("here==");
        // }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $solrUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Agent: ' . phpversion());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);                        
        $content = curl_exec($ch);        
        curl_close($ch);
        return $content;
    }

     public function getChildFilterCollection($appliedConditions) {
        $solrUrl = self::CHILD_QUERY_URL . "?$appliedConditions"; 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $solrUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Agent: ' . phpversion());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);                        
        $content = curl_exec($ch);        
        curl_close($ch);
        return $content;
    }
    
    /* delete indexed product by id from solr*/
    public function deleteProduct($p_ids = array()) {
        if (!empty($p_ids)) {
            $xml_data = "<delete>";        
            foreach ($p_ids as $p_id) {
                $xml_data .= "<query>prod_en_id_int:" . $p_id["entity_id"] . "</query>";
            }
            $xml_data .= "</delete>";
            $this->postToSearchIndex($xml_data);        
            $this->commitToSearchIndex();                    
        }
    }

    /* delete indexed product by id from solr*/
    public function deleteProductbySku($p_ids = array()) {
        if (!empty($p_ids)) {
            $xml_data = "<delete>";        
            foreach ($p_ids as $p_id) {
                $xml_data .= "<query>prod_sku:" . $p_id["sku"] . "</query>";
            }
            $xml_data .= "</delete>";
            $this->postToSearchIndex($xml_data);        
            $this->commitToSearchIndex();                    
        }
    }

     public function deleteCategory($c_ids = array()) {
        if (!empty($c_ids)) {
            $xml_data = "<delete>";        
            foreach ($c_ids as $c_id) {
                $xml_data .= "<query>cat_en_id_int:" . $c_id["entity_id"] . "</query>";
            }
            $xml_data .= "</delete>";
           // echo $xml_data;die;
            //$xml_data = '<delete><query>cat_en_id_int:*</query></delete>';
            $this->postToCatSearchIndex($xml_data);        
            $this->commitToCatSearchIndex();                    
        }
        $xml_data = '<delete><query>*:*</query></delete>';
        // echo $xml_data;die;
        $this->postToCatSearchIndex($xml_data);        
            $this->commitToCatSearchIndex();  
     }
    public function deleteChildProduct($p_ids = array()) {
        if (!empty($p_ids)) {
            $xml_data = "<delete>";
            foreach ($p_ids as $p_id) {
                $xml_data .= "<query>prod_en_id_int:" . $p_id["entity_id"] . "</query>";
            }
            $xml_data .= "</delete>";
            $this->postToChildIndex($xml_data);
            $this->commitToChildIndex();
        }
    }

     public function getCurrentDevice(){
            $useragent = $_SERVER['HTTP_USER_AGENT'];
                if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))){
                        $currentDevice = 'Mobile';
                    }else{
                         $currentDevice = 'Desktop';
                     }

        return $currentDevice;
    }
}

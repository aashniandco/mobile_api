<?php 
namespace Fermion\SolrApiTest\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class SolrHelper extends AbstractHelper
{
   
    const SOLR_UPDATE_URL = "http://130.61.224.212:8983/solr/aashni_dev/update";
    const QUERY_URL = "http://130.61.224.212:8983/solr/aashni_dev/select";
    const SOLR_SEARCH_UPDATE_URL = "http://130.61.224.212:8983/solr/aashni_cat/update";
    const SEARCH_QUERY_URL = "http://130.61.224.212:8983/solr/aashni_cat/select";
    
   

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    public function sendRequest($url, $method = 'GET', $postData = [])
    {
        $ch = curl_init();

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function getCategoryProducts($categoryIds)
    {
        $categoryFilter = str_replace(",", " OR ", $categoryIds);
        $solrUrl = self::SEARCH_QUERY_URL . "?q=category_ids:($categoryFilter)&rows=20";

        return $this->sendRequest($solrUrl);
    }
}
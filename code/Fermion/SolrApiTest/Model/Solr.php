<?php
namespace Fermion\SolrApiTest\Model;

use Fermion\SolrApiTest\Api\SolrInterface;
use Fermion\SolrApiTest\Helper\SolrHelper;

class Solr implements SolrInterface
{
    protected $solrHelper;

    public function __construct(SolrHelper $solrHelper)
    {
        $this->solrHelper = $solrHelper;
    }

    public function getCategoryProducts($categoryIds)
    {
        $categoryFilter = str_replace(",", " OR ", $categoryIds);
        $solrUrl = SolrHelper::SEARCH_QUERY_URL . "?q=category_ids:($categoryFilter)&rows=20";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $solrUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}

<?php
namespace Fermion\SolrApiTest\Api;

interface SolrInterface
{
    /**
     * Fetch products from Solr based on category IDs
     * @param string $categoryIds
     * @return string
     */
    public function getCategoryProducts($categoryIds);
}

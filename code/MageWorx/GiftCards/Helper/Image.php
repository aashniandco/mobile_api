<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context as ContextHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Image extends AbstractHelper
{

    protected $store_manager;    
    protected $connection;
    private $objectManager;

    public function __construct(
        ContextHelper $context,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\App\ResourceConnection $resourceConn,
         \Magento\Store\Model\StoreManagerInterface $storeManager 

    ) {
         $this->store_manager = $storeManager;
        $this->connection = $resourceConn->getConnection();
        $this->objectManager = $objectmanager;
        parent::__construct($context);
    }

    public function getImageUploadUrl($quote_id)
    {
        // $respArr = array();
        try{
            $query = "SELECT qio.value FROM quote_item_option qio INNER JOIN quote_item qi ON qi.item_id = qio.item_id AND qi.quote_id = $quote_id WHERE qio.code = 'image_url'"; 
            $result=$this->connection->query($query);
            error_log($result);
            // $respArr['error'] = 0;
            // $respArr['msg'] = 'success'; 
            // $respArr['image_url'] = $result['value'];
        }catch(Exception $e){
            // $respArr['error'] = 1;
             $e->getMessage();
        }
        // return $respArr;
    }

}

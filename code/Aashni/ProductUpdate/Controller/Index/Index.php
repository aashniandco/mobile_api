<?php

namespace Aashni\ProductUpdate\Controller\Index ;

use \Magento\Framework\App\Action\Action ;
use \Magento\Framework\App\Action\Context ;
use \Magento\Framework\View\Result\PageFactory ;
use \Magento\Framework\Controller\Result\JsonFactory ;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $resultJsonFactory;
    
    /**
     * @var PageFactory
     */
    protected $_urlInterface;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;


    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
         ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resultPageFactory = $resultPageFactory ;
        $this->resultJsonFactory = $resultJsonFactory ;
        $this->_productRepository = $productRepository;
         $this->scopeConfig = $scopeConfig;
        $this->_resource = $resource;
        parent::__construct($context);
    }
    
    /**
     * Get Chatbot Id and Status
     */
    public function execute()
    {  $connection = $this->_resource->getConnection();
       $response = array();
       $input_sku  =   $this->getRequest()->getParam('sku');
       $input_size =   $this->getRequest()->getParam('size');
       $connection->query("INSERT INTO rts_log(`sku`,`size`)values('$input_sku','$input_size')");
       $lastInsertId = $connection->lastInsertId();
       $new_sku = $input_sku.'-'.$input_size;
       $input_sku = $input_sku.'-%';
       $input_size = '%-'.$input_size ;
       $result = $connection->fetchRow("select sku , count(sku) as total_count from catalog_product_entity where sku like '$input_sku' and sku like  '$input_size'");
       if($result['total_count']==1)
       {
           if($result['sku']==$new_sku)
           {
              $response['message'] = 'same sku already exist so no update can be made.' ;
              $response['status'] = '205' ;

           }
           else 
           {
           $website_sku = $result['sku'] ;
           $stockData = 10;
           $is_in_stock = 1 ;
           $product =  $this->_productRepository->get($website_sku);
           $designer = $product->getDesigner();
           $designer_list =  explode(',',$this->scopeConfig->getValue('productupdate/general/designer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            if(in_array($designer, $designer_list))
            {
              $stockData = 0;
              $is_in_stock = 0 ;
            }
           if($website_sku!='' && $designer!='')
            {
               $query = "SELECT cpe.entity_id FROM catalog_product_entity AS cpe LEFT JOIN catalog_product_entity_int cpei1 on cpei1.entity_id=cpe.entity_id AND cpei1.store_id = 0 AND cpei1.attribute_id = 149 where cpe.type_id = 'simple' and cpe.sku!='$website_sku'  and cpei1.value = '$designer' order by cpe.sku limit 1";
               $reference_id = $connection->fetchOne($query);
               if($reference_id!='')
                 {
                   $query = "select 
                   cpei.value as deliverytimes ,
                   cpev1.value as delivery,
                   cpev2.value as delivery_days 
                   from 
                   catalog_product_entity_int cpei
                   LEFT JOIN catalog_product_entity_varchar cpev1 on cpei.entity_id = cpev1.entity_id and cpev1.attribute_id = 144
                   LEFT JOIN catalog_product_entity_varchar cpev2 on cpei.entity_id = cpev2.entity_id and cpev2.attribute_id = 268
                   where cpei.attribute_id = 151 and cpei.entity_id = '$reference_id'";

                   $data = $connection->fetchAll($query);
                   $delivery = $data[0]['delivery'] ;
                   $delivery_times = $data[0]['deliverytimes'] ;
                   $delivery_days = $data[0]['delivery_days'] ;
                   $stock_type = '6650';
                   if($new_sku=='' || $stock_type=='' || $delivery=='' || $delivery_times=='' || $delivery_days=='')
                   {
                      $response['message'] = 'delivery related details missing from another product of same designer. Please contact magento admin' ;
                      $response['status'] = '201' ;
                   }
                    else {

                           $product->setStoreId(0); 
                           $product->setSku($new_sku);
                           $product->setQuantityAndStockStatus(['qty' => $stockData, 'is_in_stock' => $is_in_stock]);
                           $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                           $product->setStockType($stock_type);
                           $product->setDelivery($delivery);
                           $product->setDeliverytimes($delivery_times);
                           $product->setDeliveryDays($delivery_days);
                           $product->save();
                           $connection->query("INSERT INTO solr_indexer_skus(`sku`)values('$new_sku')");
                           $response['message'] = $website_sku.' has been updated successfully' ;
                            $response['status'] = '200' ;

                      }
                           
                 }
                 else 
                 {
                     $response['message'] = 'No other product found of designer to which input product belongs to ' ;
                     $response['status'] = '202' ;
                 }
             }
             else 
             {
                 $response['message'] = 'No designer found for input sku. Kindly contact mageto admin' ;
                 $response['status'] = '203' ;
             }
           }
         }
         elseif($result['total_count']>1)
         {
            $response['message'] = "2 sku found matching input sku and size. Kindly contact magento admin" ;
             $response['status'] = '204' ;

         }
         else
         { 
              $response['message'] = "sku doesn not exist." ;
             $response['status'] = '206' ;
         }
         $response_message = $response['status'].','.$response['message'];
         $connection->query("update rts_log set response ='$response_message' where id ='$lastInsertId'");
          $resultJson = $this->resultJsonFactory->create();
         return  $resultJson->setData($response);  exit ;
    }
}

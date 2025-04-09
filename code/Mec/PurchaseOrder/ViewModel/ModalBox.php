<?php
namespace Mec\PurchaseOrder\ViewModel;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Request\Http;

class ModalBox implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    
    /**
    * @var \Magento\Framework\Serialize\Serializer\Json
    */
    
    protected $_json;

    protected $logger;
    protected $request;
    //protected $order;
    protected $_urlInterface;
    private $_coreRegistry;
    protected $productrepository;  
    

    /**
    * Index constructor.
    * @param \Magento\Framework\Serialize\Serializer\Json $json
    */

    public function __construct(
        LoggerInterface $logger,
        Http $request,
        //\Magento\Sales\Api\OrderRepositoryInterface $order,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\UrlInterface $urlInterface,   
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productrepository
    ) {
        $this->logger = $logger;
        $this->request = $request;
        //$this->order = $order;
        $this->_json = $json;
        $this->_urlInterface = $urlInterface;
        $this->_coreRegistry = $registry;
        $this->productrepository = $productrepository;


    }
  
    public function getInfo(){
            return "alex";
    }

    public function getProductData($productid) {
       return $this->productrepository->getById($productid);
    }
   
    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }
    /**
     * Retrieve order model instance
     *
     * @return int
     *Get current id order
     */
    public function getOrderId()
    {
        return $this->getOrder()->getEntityId();
    }

    /**
     * Retrieve order increment id
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }

    public function getOrderItems()
    {
        return $this->getOrder()->getAllItems();
    }

    public function getOrderItemsJsonData()
    {
        $items = $this->getOrder()->getAllItems();
        error_log("poimagepath order id ::".$this->getOrder()->getId());   
        //print_r($item);die;
        $arr = [];
        $i = 1;
        foreach ($items as $item) {
            //echo "<pre>"; print_r($item->debug());
            
            if($item['product_type'] != 'configurable'){ 
                //$product = $this->getProductData($item->getProductId());
                //$productParent = $this->getProductData($item['parent_item']['product_id']);
                //$image =  $productParent->getData('image');

                if(isset($item['parent_item']['product_id'])){
                   $product_id = $item['parent_item']['product_id'];  
                    error_log("poimagepath modal id 1 ::".$product_id);                 
                }else{

                  $product_id = $item['product_id'];  
                    error_log("poimagepath modal id 2 ::".$product_id);                 
                }

                $product = $this->getProductData($product_id);

                $created_at = $item['create_at'];
                $delivery_date = date('Y-m-d', strtotime($created_at. ' + 5 days'));

                $arr["id_".$item->getItemId()] = [
                      "sl"=>$i,
                      "sku"=>$product->getSku(),
                      "product_id"=> $product_id,
                      "product_name"=>strip_tags($product->getShortDescription()),
                      "vendor_code"=>$product->getDesignerCode(),
                      "vendor_name"=>$product->getName(),
                      "description"=>strip_tags($product->getShortDescription()),
                      "attribute_name"=>$item['parent_item']['product_options']['attributes_info'][0]['label'], 
                      "attribute_value"=>$item['parent_item']['product_options']['attributes_info'][0]['value'],          
                      "price"=>$item->getPrice(),
                      "delivery_date"=>$delivery_date
                      
                      ]                 
                ;
                $i++;
                }

           }
           //print_r($arr);
           //exit('here');
           //return $this->_json->serialize($arr);
           return json_encode($arr, JSON_HEX_APOS);
    }

    public function getFormUrl()
    {
        $orderId = false;
        //if($this->hasData('order')){
        //$orderId = $this->getOrderId();
        //}
        return $this->getUrl('mec_purchaseorder/purchaseorder/save',[
            'order_id' => $orderId
        ]);
    }

     public function getFormAction()
    {
        //return $this->getUrl('mec_purchaseorder/purchaseorder/save', ['_secure' => true]);
         return $this->_urlInterface->getUrl('mec_purchaseorder/purchaseorder/download');
       // return "1234";
    }

    /*public function getOrderId()
    {
        $this->logger->info('order id '.$this->request->getParam('order_id'));
        //$order = $this->order->get(60);
        //$this->logger->info('purchase order id '.$order->getData('basys_order_id'));
        return "hello"; //$order->getData('basys_order_id');
    }*/
}

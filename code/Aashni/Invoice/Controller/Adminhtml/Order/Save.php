<?php

namespace Aashni\Invoice\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Aashni\Invoice\Helper\Data as Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;
class Save extends Action
{
    protected $resultRedirectFactory;
    protected $orderFactory;
    protected $scopeConfig;

    public function __construct(
        Context $context,
        RedirectFactory $resultRedirectFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        Helper $helper,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->orderFactory = $orderFactory;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {

        $stripe_secret_Key = $this->getStripeSecretKey() ;
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderFactory->create()->load($orderId);
        $increment_id = $order->getIncrementId();

        $stripe_customer_id = $this->getStripeCustomerId($order,$stripe_secret_Key);
            
        $invoice_id = $this->createInvoice($increment_id,strtolower($order->getOrderCurrencyCode()),$stripe_secret_Key,$stripe_customer_id);
        $invoice_finalized = '' ;
        $invoice_sent = '' ;
        if($invoice_id!='')
        {
          $this->createInvoiceItem($order,$stripe_secret_Key,$stripe_customer_id,$invoice_id); 
          $finalize_invoice =  $this->helper->getFinalizeInvoice();
          if($finalize_invoice)
          {
             $this->finalizeInvoice($stripe_secret_Key,$invoice_id);
             $invoice_finalized = 'invoice finalized' ;
          }    
           $send_invoice =  $this->helper->getSendInvoice();
          if($send_invoice)
           {
             $this->sendInvoice($stripe_secret_Key,$invoice_id);
             $invoice_sent = 'invoice sent' ;
           }
 
        }
        
        $success_message = 'Strip invoice created' ;
        if($invoice_finalized) { $success_message .= ' and Invoice has been finalized' ; }
        if($invoice_sent) { $success_message .= ' and invoice has been sent' ; }
        
        
        $this->messageManager->addSuccessMessage(__($success_message));
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        return $resultRedirect;
    }

    

    public function getStripeCustomerId($order,$stripe_secret_Key)
    {

    $email_id = $order->getCustomerEmail();
    $name = $order->getCustomerFirstName().' '.$order->getCustomerLastName();
    $increment_id = $order->getIncrementId();
    $description = 'customer created from magento' ;
    $customers = $this->checkIfCustomerExist($email_id,$stripe_secret_Key) ;
      if(count($customers) > 0) 
      {
        $stripe_customer_id = $customers[0]['id'] ; 
      } 
        else 
        {  
            $url = 'https://api.stripe.com/v1/customers';
            $headers = ['Authorization'=> 'Bearer ' . $stripe_secret_Key];
            $data = [
                'email' => $email_id,  
                'description' => $description,   
                'name' => $name,               
            ];
            $response = $this->helper->makeCurlCall($url,'POST',$headers,http_build_query($data));
            $response_data = json_decode($response, true);
            $stripe_customer_id = $response_data['id'];

         }

         return $stripe_customer_id ;
    }

    public function checkIfCustomerExist($email_id,$stripe_secret_Key)
    {
        $customers = [];
        $has_more = true;
        $starting_after = null;

        while ($has_more) {
        $url = 'https://api.stripe.com/v1/customers?email=' . urlencode($email_id);
        if ($starting_after) {
            $url .= '&starting_after=' . $starting_after; // Add the starting_after parameter for pagination
        }
        $headers = ['Authorization'=> 'Bearer ' . $stripe_secret_Key,'Content-Type' => 'application/x-www-form-urlencoded'];
         $response = $this->helper->makeCurlCall($url,'GET',$headers);
       
        $data = json_decode($response, true);

        if (isset($data['data'])) {
            $customers = array_merge($customers, $data['data']);
        }
        $has_more = isset($data['has_more']) && $data['has_more'];
        if ($has_more) {
            $starting_after = end($data['data'])['id'];
        }
        }
        return $customers ;
    }

    public function getStripeSecretKey()
    {
         return  $this->helper->getStripeSecretKey();
    }

    public function getInvoiceDueDays()
    {
        return  $this->helper->getInvoiceDueDays();

    }


    public function createInvoiceItem($order,$stripe_secret_Key,$stripe_customer_id,$invoice_id)
    {  
        
       $currency_code = strtolower($order->getOrderCurrencyCode()); 
        $items = $order->getItems();
         foreach ($items as $item) {

         if ($item->getProductType() === 'configurable' && is_null($item->getParentItemId())) {
        $quantity = (int)$item->getQtyOrdered();
        $unit_amount = $item->getPrice() ;
        $description = $item->getName().' SKU: '.$item->getSku() ;
      
        $this->addProductToInvoice($stripe_secret_Key,$stripe_customer_id,$currency_code,$quantity,$unit_amount,$description,$invoice_id);
        }
         if ($item->getProductType() === 'simple' && is_null($item->getParentItemId())) {

            $quantity = (int)$item->getQtyOrdered();
            $unit_amount = $item->getPrice() ;
            $description = $item->getName().' SKU: '.$item->getSku() ;
            $this->addProductToInvoice($stripe_secret_Key,$stripe_customer_id,$currency_code,$quantity,$unit_amount,$description,$invoice_id);

        }
         
         }
           if(abs($order->getDiscountAmount())>0)
           {

        $discount_amount = $order->getDiscountAmount();
         $description = 'Discount' ;
         $this->addOtherItemToInvoice($stripe_secret_Key,$stripe_customer_id,$currency_code,$discount_amount,$description,$invoice_id);
         }
         if($order->getShippingAmount()>0)
           {

         $shipping_amount = $order->getShippingAmount();
         $description = 'Shipping' ;
         $this->addOtherItemToInvoice($stripe_secret_Key,$stripe_customer_id,$currency_code,$shipping_amount,$description,$invoice_id);
           }

         if(!is_null($order->getAmstorecreditAmount())){
         $store_credit_amount = -$order->getAmstorecreditAmount();
         $description = 'Store Credit' ;
         $this->addOtherItemToInvoice($stripe_secret_Key,$stripe_customer_id,$currency_code,$store_credit_amount,$description,$invoice_id);
     }
    
     }


    public function addProductToInvoice($stripe_secret_Key,$stripe_customer_id,$currency_code,$quantity,$unit_amount,$description,$invoice_id)
    {
        $url = 'https://api.stripe.com/v1/invoiceitems';
        $headers = ['Authorization'=> 'Bearer ' . $stripe_secret_Key];
        $data = [
            'customer' => $stripe_customer_id,  // Customer ID (replace with an actual customer ID)
            'currency' => $currency_code,  // Currency
            'quantity'=>$quantity,
            'unit_amount' => $unit_amount*100, // Amount in cents (1000 = $10)
            'description' => $description,  // Description of the item
            'invoice' => $invoice_id,
        ];
        $response = $this->helper->makeCurlCall($url,'POST',$headers,http_build_query($data));

        $data = json_decode($response, true);
    }

     public function addOtherItemToInvoice($stripe_secret_Key,$stripe_customer_id,$currency_code,$discount_amount,$description,$invoice_id)
    {
        $url = 'https://api.stripe.com/v1/invoiceitems';
        $headers = ['Authorization'=> 'Bearer ' . $stripe_secret_Key];
       $data = [
            'customer' => $stripe_customer_id,  // Customer ID (replace with an actual customer ID)
            'currency' => $currency_code,  // Currency
            'amount' => $discount_amount*100,  // Amount in cents (1000 = $10)
            'description' => $description,  // Description of the item
            'invoice' => $invoice_id,
        ];
        $response = $this->helper->makeCurlCall($url,'POST',$headers,http_build_query($data));

        $data = json_decode($response, true);
    }

    public function createInvoice($increment_id,$currency_code,$stripe_secret_Key,$stripe_customer_id)
    {
        $url = "https://api.stripe.com/v1/invoices";
        $idempotency_key = 'unique-key-for-idempotency-' . time();
        $due_date = $this->getInvoiceDueDays();
        $description = 'Order number - '.$increment_id ;
         $headers = ['Authorization'=> 'Bearer ' . $stripe_secret_Key,'Idempotency-Key: ' . $idempotency_key,];
        $data = [
            'customer' => $stripe_customer_id,
            'auto_advance' => 'true',
            'collection_method'   => 'send_invoice',
            'days_until_due' => $due_date,
             'description' => $description,
             'pending_invoice_items_behavior' => 'exclude',
             'currency' => $currency_code,
        ];
        $response = $this->helper->makeCurlCall($url,'POST',$headers,http_build_query($data));
           
        $invoice_data = json_decode($response, true);
        if($invoice_data['id']!='')
        {

          $this->helper->insertData($increment_id,$invoice_data['id'],'draft');
        }
         return $invoice_data['id'];
    }

        public function finalizeInvoice($stripe_secret_Key,$invoice_id)
    {

       $url = "https://api.stripe.com/v1/invoices/".$invoice_id."/finalize";
       $headers = ['Authorization'=> 'Bearer ' . $stripe_secret_Key,'Content-Type: application/x-www-form-urlencoded'];
       $response = $this->helper->makeCurlCall($url,'POST',$headers);

        }

                public function sendInvoice($stripe_secret_Key,$invoice_id)
        {

        $url = "https://api.stripe.com/v1/invoices/".$invoice_id."/send";
        $headers = ['Authorization'=> 'Bearer ' . $stripe_secret_Key,'Content-Type: application/x-www-form-urlencoded'];
        $response = $this->helper->makeCurlCall($url,'POST',$headers);
    
    }
}

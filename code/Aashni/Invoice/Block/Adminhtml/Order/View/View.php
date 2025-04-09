<?php

namespace Aashni\Invoice\Block\Adminhtml\Order\View;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Backend\Block\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Aashni\Invoice\Helper\Data as Helper;
class View extends Template
{
    protected $orderRepository;
    protected $authSession;
     protected $scopeConfig;
       public function __construct(
        Template\Context $context,
        OrderRepositoryInterface $orderRepository,
        \Magento\Backend\Model\Auth\Session $authSession, 
         ScopeConfigInterface $scopeConfig,
         Helper $helper,
        array $data = []
    ) { 
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
        $this->authSession = $authSession;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }


        public function checkPermission()
    {
       $admin_user_id = $this->authSession->getUser()->getId();
        $user_id_list = $this->scopeConfig->getValue('invoice/general/user_id_list', \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
       return (in_array($admin_user_id, explode(',',$user_id_list)))?'true':'false';

    }
         public function checkIfEnabled()
    {
        $module_enabled = $this->scopeConfig->getValue('invoice/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
       return ($module_enabled)?'true':'false';

    }

    public function checkInvoiceStatus()
    {   
        $order = $this->orderRepository->get($this->getOrderId());
        $invoice_data = $this->helper->getInvoice($order->getIncrementId());
        $firstItem = $invoice_data->getFirstItem();
        $invoice_status = $firstItem->getStatus();
        
        if($invoice_status!='paid')
        {   
            $invoice_id = $firstItem->getInvoiceId(); 
            $stripe_secret_key = $this->helper->getStripeSecretKey();  
            $url = 'https://api.stripe.com/v1/invoices/' . $invoice_id;
            $headers = ['Authorization'=> 'Bearer ' . $stripe_secret_key];
            $response = $this->helper->makeCurlCall($url,'GET',$headers,$data=[]);
            $data = json_decode($response, true);

            if (isset($data['id'])) {
               $this->helper->updateData($invoice_id,$data['status']);
               return  $status = 'invoice status = '.$data['status'].' and invoice link = <a target="_blank" href="'.$data['hosted_invoice_url'].'">click here</a>'; 
            }
         }
      else 
      {
        return $invoice_status ;
      }
        
    }

    public function checkIfInvoiceCreated()
    {
       $order = $this->orderRepository->get($this->getOrderId());
        $invoice_exist = $this->helper->getInvoice($order->getIncrementId());
        return count($invoice_exist) ; 
    }

      /**
     * Get the billing address country ID of the order
     *
     * @param int $orderId
     * @return string|null
     */
    public function getBillingCountryId()
    {   
        try {
            $orderId = $this->getOrderId();
            $order = $this->orderRepository->get($orderId);
            $billingAddress = $order->getBillingAddress();
            if ($billingAddress) {
               return $billingAddress->getCountryId();
                
            }
        } catch (\Exception $e) {
            // Log or handle the exception as needed
        }
        return null;
    }
    public function getFormAction()
    {
        return $this->getUrl('aashni_invoice/order/save', ['order_id' => $this->getRequest()->getParam('order_id')]);
    }
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id') ;
    }
}
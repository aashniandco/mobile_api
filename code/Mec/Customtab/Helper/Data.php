<?php

namespace Mec\Customtab\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
     protected $_storeManager;     
     protected $_solrHelper;
   public function __construct(
     \Magento\Store\Model\StoreManagerInterface $storeManager,        
     \Fermion\Pagelayout\Helper\SolrHelper $solrHelper,
        \Magento\Framework\App\Helper\Context $context,
         ScopeConfigInterface $scopeConfig
    ) {
        $this->_storeManager = $storeManager;                  
        $this->_solrHelper = $solrHelper;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

        /* get current store id */
     protected function getCurrentStoreId() {
          return $this->_storeManager->getStore()->getId();
     }

     public function getSolrProductTag($id){
          $st_id = $this->getCurrentStoreId();
          $query = 'q=prod_en_id:'.rawurlencode($id)."&fl=product_tags_name:product_tags_name,prod_availability_label:prod_availability_label";
          $price = $this->_solrHelper->getFilterCollection($query);
          return json_decode($price, 1);
     }

    public function getWhatsappNumber()
    {
         return $this->scopeConfig->getValue('catalog/productpage_data/whatsapp_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
     public function getTelephoneNumber()
    {
         return $this->scopeConfig->getValue('catalog/productpage_data/phone_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
     public function getCustomerCareEmail()
    {
         return $this->scopeConfig->getValue('catalog/productpage_data/email_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}

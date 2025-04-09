<?php

namespace Aashni\Invoice\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\HTTP\Client\Curl;
use Aashni\Invoice\Model\StripeInvoiceFactory;
use Aashni\Invoice\Model\ResourceModel\StripeInvoice\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    protected $curl;
    protected $stripeinvoiceFactory;
    protected $collectionFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StripeInvoiceFactory $stripeInvoiceFactory,
        Curl $curl,
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $collectionFactory
    ) {
        $this->curl = $curl;
        $this->stripeinvoiceFactory = $stripeInvoiceFactory;
        $this->collectionFactory = $collectionFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function getStripeSecretKey()
    {
         return $this->scopeConfig->getValue('invoice/general/secret_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

     public function getInvoiceDueDays()
    {
        return $this->scopeConfig->getValue('invoice/general/due_days', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
     public function getFinalizeInvoice()
     {
        return $this->scopeConfig->getValue('invoice/general/finalize_invoice', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
     }

     public function getSendInvoice()
     {
       return $this->scopeConfig->getValue('invoice/general/send_invoice', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
     }

      public function getInvoice($increment_id)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('increment_id', $increment_id); // Filtering by ID

        return $collection ; // Returns an array of models
    }

    public function makeCurlCall($url, $method = 'GET', $headers = [], $data = [])
    {
        try {

        if (!empty($headers)) {
            foreach ($headers as $key => $value) {
                $this->curl->addHeader($key, $value);
            }
        }

        if (strtoupper($method) === 'POST') {
            $this->curl->post($url, $data);
        } else {
            $this->curl->get($url);
        }

        $statusCode = $this->curl->getStatus();
        if ($statusCode >= 200 && $statusCode < 300) {
            return $this->curl->getBody(); // Success response
        } else {
            throw new \Exception("HTTP Error: Status Code $statusCode");
         }

      } catch (\Exception $e) {
            throw new \Exception('Unable to complete the cURL request: ' . $e->getMessage());
        }

     }

     public function insertData($increment_id, $invoice_id,$status)
    {  if($increment_id!='' && $invoice_id!='' && $status!='')
          {
        $customTable = $this->stripeinvoiceFactory->create();
         $existingRecord = $customTable->getCollection()
            ->addFieldToFilter('invoice_id', $invoice_id)
            ->getFirstItem();

         if ($existingRecord->getId()) {
           
        } else { 
             
               $customTable->setData([
            'increment_id' => $increment_id,
            'invoice_id' => $invoice_id,
            'status' => $status
                ]);
                $customTable->save();

         }
       }
    }

       public function updateData($invoice_id,$status)
    {
        $customTable = $this->stripeinvoiceFactory->create();
         $existingRecord = $customTable->getCollection()
            ->addFieldToFilter('invoice_id', $invoice_id)
            ->getFirstItem();

         if ($existingRecord->getId()) {
        $customTable->load($existingRecord->getId());
        $customTable->setStatus($status);
        $customTable->save();
        }
       
    }

}

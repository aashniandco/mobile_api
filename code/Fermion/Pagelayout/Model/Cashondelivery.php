<?php
namespace Fermion\Pagelayout\Model;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;

class Cashondelivery extends \Magento\OfflinePayments\Model\Cashondelivery {
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null) {
        if (!$this->isActive($quote ? $quote->getStoreId() : null)) {
            return false;
        }
        
        $objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get("Magento\Framework\App\ResourceConnection");
        $quotecod = $objectManager->get('\Magento\Checkout\Model\Session');
        $quote = $quotecod->getQuote();
        $test=  $quote->getStoreId();
        // if($quote){
        //     $postcode = $quote->getShippingAddress()->getPostcode();
        //     $countryId = $quote->getShippingAddress()->getData('country_id');
        //     if($countryId != 'IN'){
        //         return false;
        //     }

        //     $cod = "Y";
        //     $connection = $resource->getConnection();
        //     $query = $connection->select()
        //             ->from(['capn' => 'cod_deliverable_pincode'])
        //             ->where('capn.pincode = ?',$postcode);
        //     $result = $connection->fetchAll($query);
        //     if (count($result) < 1) {      
        //         return false;
        //     }
        //     $ProductRepository = $objectManager->create('\Magento\Catalog\Model\ProductRepository');
        //     $visibleItems = $quote->getAllVisibleItems();
        //     foreach ($visibleItems as $item) {
        //         $product = $ProductRepository->get($item->getSku());
        //         $productType = $product->getTypeId();
        //         if($productType == 'mageworx_giftcards'){
        //           return false;
        //         }
        //         $attributestocktype = $product->getData('stock_type');
        //         $stockattributeId = $product->getResource()->getAttribute('stock_type');
        //         $stockoptionText = $stockattributeId->getSource()->getOptionText($attributestocktype);
        //         if(trim($stockoptionText) == 'Backorder' || trim($stockoptionText) == 'Back Order' || trim($stockoptionText) == 'Bought Stock' || trim($stockoptionText) == 'Consignment Stock'){
        //             return false;
        //         }

        //     }
        // }else{
        //     return false;
        // }
        // return false;
        $checkResult = new DataObject();
        $checkResult->setData('is_available', true);
        $this->_eventManager->dispatch(
            'payment_method_is_active',
            [
                'result' => $checkResult,
                'method_instance' => $this,
                'quote' => $quote
            ]
        );     
        return $checkResult->getData('is_available');
    }
}

<?php
namespace Fermion\Pagelayout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;

class ProductSaveAdminAction implements ObserverInterface
{
	public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        ResourceConnection $resource
    ) {
        $this->productRepository = $productRepository;
        $this->resource = $resource;
    }

	public function execute(Observer $observer)
	{
		$bunch = $observer->getBunch();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$authSession = $objectManager->create('\Magento\Backend\Model\Auth\Session');
        $username = $authSession->getUser()->getUsername();
        $adminuserId = $authSession->getUser()->getUserId();
        $resource = $objectManager->create("Magento\Framework\App\ResourceConnection");
        $connection = $resource->getConnection();
        foreach ($bunch as $bkey => $bvalue) {
        	$sku = isset($bvalue['sku']) ? $bvalue['sku'] : ''; 
        	$batchData = json_encode($bvalue);
        	if($sku != ''){
                try{
	                $product = $objectManager->create('\Magento\Catalog\Model\ProductRepository')->get($sku);
	                $productId = $product->getId();
	                $specialFromDate = $product->getSpecialFromDate();
	                $specialToDate = $product->getSpecialToDate();
	                
	               
	                // $stock = $stockItemRepository->get($productId);
	                $qty = $product->getExtensionAttributes()->getStockItem()->getQty();
	                $specialPrice = $product->getSpecialPrice();

	                $price = $product->getPrice();
	                
	                
	                if($qty == ''){
	                    $qty = 'null';
	                }

	                if($price == ''){
	                    $price = 'null';
	                }

	                if($specialPrice == ''){
	                    $specialPrice = 'null';
	                }

	                $priceDefault = 0;
	                $specialPriceDefault = 0;

	                // error_log("defulat price::".$product->getAttributeDefaultValue('price'));
	                // error_log("defulat special price::".$product->getAttributeDefaultValue('special_price'));
	                if($product->getAttributeDefaultValue('price') === false){
	                    $priceDefault = 1;
	                }

	                if($product->getAttributeDefaultValue('special_price') === false){
	                    $specialPriceDefault = 1;
	                }
	                $status = $product->getStatus();
	                $categoryIds = implode(",",$product->getCategoryIds());

	                $dataToInsert = ['product_id' => $productId, 'qty' => $qty, 'price' => $price, 'special_price' => $specialPrice,'username' =>$username,'price_default'=>$priceDefault,'special_price_default'=>$specialPriceDefault,'admin_user_id' => $adminuserId, 'special_from_date'=>$specialFromDate, 'special_to_date' =>$specialToDate,'status'=>$status,'category_ids'=>$categoryIds,'batch_data'=>$batchData,'source'=>'Bunch Import Magento'];
	                $tableName = "admin_action_log";
	                
	                $connection->insert($tableName, $dataToInsert);
        		}
        		catch(Exception $e){
        			error_log("Bunch Import Magento :: Error => ".$e->getMessage());
        		}
        	}
        }
	}
}
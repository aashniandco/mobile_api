<?php

namespace Fermion\Pagelayout\Controller\Cart;

class Update extends \Magento\Framework\App\Action\Action {
    const SIZE_ATTRIBUTE_CODE = 'size';
    protected $_cart;
    protected $_productRepository;
    protected $_configurable;
    protected $_storeManager;
    protected $_resource;
    protected $_stockRegistry;

    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
        ) {
        $this->_cart = $cart;
        $this->_productRepository = $productRepository;
        $this->_configurable = $configurable;
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
        $this->_stockRegistry = $stockRegistry;
        return parent::__construct($context);
    }

    public function execute(){
    	$respArr = array();
        try {
            $requestedParams = $this->getRequest()->getParams();
            $childSku = $requestedParams['sku'];
            $quote = $this->_cart->getQuote();
            $childProduct = $this->_productRepository->get($childSku);
            $stockItem = $childProduct->getExtensionAttributes()->getStockItem();
            if($stockItem->getQty() > 0) {
                $parentIds = $this->_configurable->getParentIdsByChild($childProduct->getId()); 
                $productId = $parentIds[0];
                $quoteItems = $quote->getAllItems();
                foreach ($quoteItems as $item) {
                    if ($item->getProduct()->getId() == $productId) {
                        $quoteItemId = $item->getId();
                        break;
                    }
                }
                $storeId = $this->_storeManager->getStore()->getStoreId();
                $product = $this->_productRepository->getById($productId, false, $storeId);
                $params = array();
                $params['product'] = $product->getId(); 
                $params['qty'] = 1;
                $options = [];
                $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
                foreach($productAttributeOptions as $option){
                    $options[$option['attribute_id']] =  $childProduct->getData($option['attribute_code']);
                }
                $params['super_attribute'] = $options; 
                $this->_cart->addProduct($product, $params);
                $this->_cart->removeItem($quoteItemId)->save();
                $respArr['error'] = 0;
                $respArr['message'] = 'size updated';
            }
            else{
                $respArr['error'] = 1;
                $respArr['message'] = 'The requested size is not available';
            }
        }
        catch(\Exception $e){
            $respArr['error'] = 1;
            $respArr['message'] = $e->getMessage();
        }
        echo json_encode($respArr);die;
    }
}
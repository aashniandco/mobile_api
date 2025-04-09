<?php

namespace Fermion\Pagelayout\Controller\Pdp;

class PDPAction extends \Magento\Framework\App\Action\Action {
	protected $store_manager;    
    protected $conn;
   
    public function __construct(        
        \Magento\Store\Model\StoreManagerInterface $storeManager,        
        \Magento\Framework\App\ResourceConnection $resourceConn,
        \Magento\Framework\App\Action\Context $context
        
    ) {        
        $this->store_manager = $storeManager;
        $this->conn = $resourceConn->getConnection();
        return parent::__construct($context);
    }

    public function execute(){
    	$data = $this->getRequest()->getParams();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $jsonFactory = $objectManager->get("Magento\Framework\Controller\Result\JsonFactory");
        $resultJson = $jsonFactory->create();
    	$respArr = array();
    	if($data['items']){
    		$itemIds = $data['items'];
    		$itemIds = implode(', ', $itemIds);

            // Get the current store's base currency code
            $currentCurrencyCode = $this->store_manager->getStore()->getCurrentCurrencyCode();
            $currentCurrencySymbol = $this->store_manager->getStore()->getCurrentCurrency()->getCurrencySymbol();
            $respArr['currency_symbol'] = $currentCurrencySymbol;

            // Modify the SQL query to join catalog_product_index_price for currency-specific prices
            $sql = "SELECT e.entity_id, cpei.value, cpev.value AS 'deliverytimes', cped.final_price AS 'special_price', cped.price AS 'regular_price'
                FROM catalog_product_entity e
                INNER JOIN catalog_product_entity_int cpei 
                    ON cpei.entity_id = e.entity_id AND cpei.attribute_id = 151
                LEFT JOIN catalog_product_entity_varchar cpev
                    ON cpev.entity_id = e.entity_id AND cpev.attribute_id = 144
                LEFT JOIN catalog_product_index_price cped
                    ON cped.entity_id = e.entity_id AND cped.customer_group_id = 0 
                    AND cped.website_id = " . $this->store_manager->getStore()->getWebsiteId() . "
                
                WHERE e.entity_id IN (".$itemIds.") GROUP BY e.entity_id";

            $respArr['rts'] = $this->conn->fetchAll($sql);

            // Format prices according to the current currency
            foreach ($respArr['rts'] as &$item) {
                $item['regular_price'] = $this->convertPriceToCurrency($item['regular_price'], $currentCurrencyCode);
                $item['special_price'] = $this->convertPriceToCurrency($item['special_price'], $currentCurrencyCode);
            }

            return $resultJson->setData($respArr);die;
    	}
    }

    // Helper function to convert price to the current currency
    protected function convertPriceToCurrency($price, $currencyCode) {
        return $this->store_manager->getStore()->getBaseCurrency()->convert($price, $currencyCode);
    }
}
<?php

namespace Mec\Shipitem\Controller\Index;

use Zend\Log\Filter\Timestamp;
use Magento\Framework\App\ResourceConnection;

class Index extends \Magento\Framework\App\Action\Action
{

	//const XML_PATH_EMAIL_RECIPIENT_NAME = 'trans_email/ident_support/name';
    //const XML_PATH_EMAIL_RECIPIENT_EMAIL = 'trans_email/ident_support/email';
     
    protected $_inlineTranslation;
    protected $_transportBuilder;
    protected $_scopeConfig;
    protected $_logLoggerInterface;
    protected $productRepository;
    protected $_storeManager;
    protected $resourceConnection;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,  
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        ResourceConnection $resourceConnection,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $loggerInterface,
        array $data = []
         
        )
    {
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_logLoggerInterface = $loggerInterface;
        $this->messageManager = $context->getMessageManager();
        $this->_storeManager = $storeManager;   
        $this->configurable = $configurable;
        $this->productRepository = $productRepository;        
        $this->resourceConnection = $resourceConnection; 
        parent::__construct($context);
         
         
    }

    public function execute()
    {

        //$this->_view->loadLayout();
        //$this->_view->getLayout()->initMessages();
        //$this->_view->renderLayout();

		$Action   = $_REQUEST['action'];
		$OrderId  = $_REQUEST['orderid'];
		$ItemId   = $_REQUEST['itemid'];
		$ItemSku  = $_REQUEST['itemsku'];

		$ProductId = (int) $this->getProductId($ItemSku);
		$ParentProductId = (int) $this->getParentProductId($ProductId);
                if(empty($ParentProductId)){
                    $ParentProductId = $ProductId;
                }
        
		$Status   =$_REQUEST['status'];
		$Dates    =$_REQUEST['dates'];
		$Tracker  =$_REQUEST['tracker'];
                if($Status != 6) {
		if(!empty($Dates)){
	            $str=explode(',',$Dates);
				if(count($str) != 5){
					echo 'Please add valid dates!';
					return;
				}
                }else{
                	echo 'Please add sheduled dates!';
        	        return;
                }
                }

		if($Action == 'updatestatus'){	
		    $this->updateStatus($OrderId,$ItemSku,$ItemId,$ParentProductId,$Status,$Dates,$Tracker);
                    //echo "Status has been updated.";
		}

		if($Action == 'updatedates'){			
		    $this->updateDates($OrderId,$ItemSku,$ItemId,$ParentProductId,$Dates,$Status,$Tracker);
		}

		if($Action == 'updatetracker'){			
		    $this->updateTracker($OrderId,$ItemSku,$ItemId,$ParentProductId,$Dates,$Status,$Tracker);
		}

                if($Action == 'delay'){
	            $this->sendDelayEmail($OrderId,$ItemSku,$ParentProductId,$Status,$Dates);
		}
    }


    protected function getProductById($productId)
	{   
		$product = $this->productRepository->getById($productId);
		return $product;
	}

    protected function getProductId($ItemSku){   // function one
        $connection  = $this->resourceConnection->getConnection();
		$tableName   = $connection->getTableName('catalog_product_entity');
		$sql =  "SELECT * FROM $tableName WHERE `sku` = '$ItemSku'";
		$ProductId = $connection->fetchOne($sql);		
		return $ProductId;
	
   }

   protected function getOrderData($Orderid){   // function one
        $connection  = $this->resourceConnection->getConnection();
		$tableName   = $connection->getTableName('sales_order');
		$sql =  "SELECT customer_email,customer_firstname,customer_lastname,customer_middlename,increment_id FROM $tableName WHERE `entity_id` = '$Orderid'";
		$result = $connection->fetchAll($sql);		
		return $result;
	
   }

   protected function getParentProductId($childProductId){   // function one
		$parentConfigObject = $this->configurable->getParentIdsByChild($childProductId);
		if($parentConfigObject) {
		return $parentConfigObject[0];
		}
		return false;	
   }

    protected function updateStatus($Orderid,$ItemSku,$Itemid,$ParentProductId,$Status,$Dates,$Tracker){   // function one
        $connection  = $this->resourceConnection->getConnection();
		$tableName = $connection->getTableName('sales_order_item'); 

		$sql =  "UPDATE $tableName SET `itemstatus` = '$Status' WHERE `order_id` = '$Orderid' AND `item_id` = '$Itemid'";
		if($connection->query($sql)){
		        
                        if($Status != 6) {
                             echo 'Status has been updated:';
		             $this->sendEmail($Orderid,$ItemSku,$ParentProductId,$Status,$Dates,$Tracker);	
                        }else{
                            echo 'Status has been updated as canceled...';
                        }
		}else{
			echo 'failed';
		}
   }

   protected function updateDates($OrderId,$ItemSku,$ItemId,$ParentProductId,$Dates,$Status,$Tracker){   // function one
        $connection  = $this->resourceConnection->getConnection();
		$tableName = $connection->getTableName('sales_order_item'); 

		$sql =  "UPDATE $tableName SET `statusdate` = '$Dates' WHERE `order_id` = '$OrderId' AND `item_id` = '$ItemId'";
		if($connection->query($sql)){
                     echo 'The dates have been updated...';
                     //$this->sendEmail($OrderId,$ItemSku,$ParentProductId,$Status,$Dates,$Tracker);	
		}else{
                     echo 'failed';
		}
   }

   protected function updateTracker($Orderid,$ItemSku,$Itemid,$ParentProductId,$Dates,$Status,$Tracker){   // function one
        $connection  = $this->resourceConnection->getConnection();
		$tableName = $connection->getTableName('sales_order_item'); 

		$sql =  "UPDATE $tableName SET `tracking` = '$Tracker' WHERE `order_id` = '$Orderid' AND `item_id` = '$Itemid'";
		if($connection->query($sql)){
                   echo 'The tracking code has been updated...';
                   //$this->sendEmail($Orderid,$ItemSku,$ParentProductId,$Status,$Dates,$Tracker);          	
		}else{
	           echo 'failed';
		}
   }

   protected function sendEmail($Orderid,$ItemSku,$ParentProductId,$Status,$Dates,$Tracker='')
    {
        //$post = $this->getRequest()->getPost();
        try
        {
			// Send Mail
			$this->_inlineTranslation->suspend();
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

            //Get Order Data
			$orders = $this->getOrderData($Orderid);
			$increment_id = $orders[0]['increment_id'];
			$customer_email = $orders[0]['customer_email'];
			$customer_firstname = $orders[0]['customer_firstname'];
			$customer_middlename = $orders[0]['customer_middlename'];
			$customer_lastname = $orders[0]['customer_lastname'];

			$customer_full_name = $customer_firstname.' ';
			if(!empty($customer_middlename)){
				$customer_full_name .= $customer_middlename.' ';
			}
            if(!empty($customer_lastname)){
				$customer_full_name .= $customer_lastname;
		    }			
         
			//Product Info
			$product  = $this->getProductById($ParentProductId);		
			$prodname = $product->getName();
			$sdesc    = $product->getShortDescription();
			$imgurl   = $product->getData('image');
			$store    = $this->_storeManager->getStore();
			$imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $imgurl;
            
			if(empty($Status)){
				$Status = 1;
			}

			$Orderstatus="";
			if($Status==1){$Orderstatus="In production";}
			if($Status==2){$Orderstatus="Quality Control";}
			if($Status==3){$Orderstatus="Shipped";}
			if($Status==4){$Orderstatus="Custom Clearance";}
			if($Status==5){$Orderstatus="Delivered";}
			if($Status==6){$Orderstatus="Cancelled";}

			$Proddate='';
			$Qcdate='';
			$Shipdate='';
			$Ccdate='';
			$Deliverdate='';

            if(!empty($Dates)){
	            $str=explode(',',$Dates);
				$Proddate=$str[0];
				$Qcdate=$str[1];
				$Shipdate=$str[2];
				$Ccdate=$str[3];
				$Deliverdate=$str[4];
            }
            
		   //exit('inside email');
             
            $sender = [
                'name' => 'AASHNI + CO',
                'email' => 'orders@aashniandco.com'
            ];
             
            /*$sentToEmail = $this->_scopeConfig ->getValue('trans_email/ident_general/email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
             
            $sentToName = $this->_scopeConfig ->getValue('trans_email/ident_general/name',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);*/
             $sentToEmail = $customer_email;
             $sentToName  = $customer_full_name;
             
            $transport = $this->_transportBuilder
            ->setTemplateIdentifier('customemail_email_template')
            ->setTemplateOptions(
                [
                    'area' => 'frontend',
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
                )
                ->setTemplateVars([
                	'orderid'     => $increment_id,
                	'prodname'    => $prodname,
                	'SKU'         => $ItemSku,
                	'imgurl'      => $imageUrl,
                	'sdesc'       => $sdesc,
                	'Status'      => $Orderstatus,
                	'qcdate'      => $Qcdate,
                	'shippeddate' => $Shipdate,
                	'ccdate'      => $Ccdate,
                	'deldate'     => $Deliverdate,
                        'name'        => $customer_full_name,
                        'email'       => $customer_email,
                        'tracking_code' => $Tracker
                ])
                ->setFrom($sender)
                ->addTo($sentToEmail,$sentToName)
                //->addTo('owner@example.com','owner')
                ->getTransport();
                 
                $transport->sendMessage();
                 
                $this->_inlineTranslation->resume();
                //$this->messageManager->addSuccess('Email sent successfully');
                echo 'Email sent successfully...';
                //$this->_redirect('customemail/index/index');
                 
        } catch(\Exception $e){
            //$this->messageManager->addError($e->getMessage());
            //$this->_logLoggerInterface->debug($e->getMessage());
            echo $e->getMessage();
        } 
    }

   protected function sendDelayEmail($Orderid,$ItemSku,$ParentProductId,$Status,$Dates)
	{
		//$post = $this->getRequest()->getPost();
		try
		{
			// Send Mail
			$this->_inlineTranslation->suspend();
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

			//Get Order Data
			$orders = $this->getOrderData($Orderid);
			$increment_id = $orders[0]['increment_id'];
			$customer_email = $orders[0]['customer_email'];
			$customer_firstname = $orders[0]['customer_firstname'];
			$customer_middlename = $orders[0]['customer_middlename'];
			$customer_lastname = $orders[0]['customer_lastname'];

			$customer_full_name = $customer_firstname.' ';
			if(!empty($customer_middlename)){
				$customer_full_name .= $customer_middlename.' ';
			}
			if(!empty($customer_lastname)){
				$customer_full_name .= $customer_lastname;
			}

			//Product Info
			$product  = $this->getProductById($ParentProductId);
			$prodname = $product->getName();
			$sdesc    = $product->getShortDescription();
			$imgurl   = $product->getData('image');
			$store    = $this->_storeManager->getStore();
			$imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $imgurl;

			if(empty($Status)){
				$Status = 1;
			}

			$Orderstatus="";
			if($Status==1){$Orderstatus="In production";}
			if($Status==2){$Orderstatus="Quality Control";}
			if($Status==3){$Orderstatus="Shipped";}
			if($Status==4){$Orderstatus="Custom Clearance";}
			if($Status==5){$Orderstatus="Delivered";}
			if($Status==6){$Orderstatus="Cancelled";}

			$Proddate='';
			$Qcdate='';
			$Shipdate='';
			$Ccdate='';
			$Deliverdate='';

			if(!empty($Dates)){
				$str=explode(',',$Dates);
				$Proddate=$str[0];
				$Qcdate=$str[1];
				$Shipdate=$str[2];
				$Ccdate=$str[3];
				$Deliverdate=$str[4];
			}

			//exit('inside email');

			$sender = [
				'name' => 'Aashniandco',
				'email' => 'orders@aashniandco.com'
			];

			/*$sentToEmail = $this->_scopeConfig ->getValue('trans_email/ident_general/email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $sentToName = $this->_scopeConfig ->getValue('trans_email/ident_general/name',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);*/
			$sentToEmail = $customer_email;
			$sentToName  = $customer_full_name;

			$transport = $this->_transportBuilder
				->setTemplateIdentifier('delay_order_email_template')
				->setTemplateOptions(
					[
						'area' => 'frontend',
						'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
					]
				)
				->setTemplateVars([
					'orderid'     => $increment_id,
					'prodname'    => $prodname,
					'SKU'         => $ItemSku,
					'imgurl'      => $imageUrl,
					'sdesc'       => $sdesc,
					'Status'      => $Orderstatus,
					'qcdate'      => $Qcdate,
					'shippeddate' => $Shipdate,
					'ccdate'      => $Ccdate,
					'deldate'     => $Deliverdate,
					'name'        => $customer_full_name,
					'email'       => $customer_email
				])
				->setFrom($sender)
				->addTo($sentToEmail,$sentToName)
				//->addTo('owner@example.com','owner')
				->getTransport();

			$transport->sendMessage();

			$this->_inlineTranslation->resume();
			//$this->messageManager->addSuccess('Email sent successfully');
			echo 'Email sent successfully';
			//$this->_redirect('customemail/index/index');

		} catch(\Exception $e){
			//$this->messageManager->addError($e->getMessage());
			//$this->_logLoggerInterface->debug($e->getMessage());
			echo $e->getMessage();
		}
	}  
  

}

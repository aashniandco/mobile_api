<?php

namespace Mec\Shipitem\Controller\Adminhtml\Index;


class Index extends \Magento\Backend\App\Action
{

    public function execute(){
    //$this->_view->loadLayout();
    //$this->_view->getLayout()->initMessages();
    //$this->_view->renderLayout();
     $action  = $_REQUEST['action'];
     $Orderid = $_REQUEST['orderid'];
     $Itemid  = $_REQUEST['itemid'];
     
     if($action == 'updatestatus'){
     	    $Status=$_REQUEST['status'];
            $this->updateStatus($Orderid,$Itemid,$Status);
     }

    }

    protected function updateStatus($Orderid,$Itemid,$Status){   // function one
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('sales_order_item'); 

		$sql =  "UPDATE $tableName SET `itemstatus` = '$Status' WHERE `order_id` = '$Orderid' AND `item_id` = '$Itemid'";
		if($connection->query($sql)){
                echo 'updated';

		}else{
			echo 'failed';
		}

   }

}

?>

<?php

namespace PayUIndia\Payu\Controller\Standard;

class Cancel extends \PayUIndia\Payu\Controller\PayuAbstract {

    public function execute() {
        //$this->getOrder()->cancel()->save();
        $order = $this->getOrder();
        if($order->getEntityId()){
            $order->cancel()->save();
        }else{
            $params = $this->getRequest()->getParams();
            $orderIncrementId = isset($params['txnid']) ? $params['txnid'] : '';
            if($orderIncrementId != ''){
                 $order = $this->_orderFactory->create()->loadByIncrementId(
                   $orderIncrementId
                );
                $order->cancel()->save();
            }
           
        }
        
        $this->messageManager->addErrorMessage(__('Your order has been can cancelled'));
        $this->getResponse()->setRedirect(
                $this->getCheckoutHelper()->getUrl('checkout')
        );
    }

}

<?php

namespace PayUIndia\Payu\Controller\Standard;

class Response extends \PayUIndia\Payu\Controller\PayuAbstract {
    //Added by Alex
    //private $orderNotifier;

    /*public function __construct(        
        \Magento\Sales\Model\OrderNotifier $orderNotifier
    ) {        
        $this->orderNotifier = $orderNotifier;
    }*/
    public function execute() {
        $returnUrl = $this->getCheckoutHelper()->getUrl('checkout');

        try {
            error_log('Payu debug 1');
            $paymentMethod = $this->getPaymentMethod();
            $params = $this->getRequest()->getParams();
            
            //Alex:Adding logger to capture response
            $writer = new \Zend\Log\Writer\Stream(BP.'/var/log/payu_response.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info(json_encode($params));
            //End:Adding logger
            
            //Added By Alex
            $email = $params['email'];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerSession = $objectManager->create('Magento\Customer\Model\Session');
            if(!$customerSession->isLoggedIn()) {
                error_log('Payu debug 1.1');
                $customerObj = $objectManager->create('Magento\Customer\Model\Customer');
                $loadCustomer = $customerObj->setWebsiteId(1)->loadByEmail($email);
                $customerSession->setCustomerAsLoggedIn($loadCustomer);
            }
            
            //End
error_log($email.'Payu debug 2');
            if ($paymentMethod->validateResponse($params)) {
                
                //
                //$order = $this->getOrder();
               
                //$order = $this->getOrder();
                //Added By Alex
                $orderIncrementId= $params['txnid'];
                $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderIncrementId);
                $order_id = $order->getId();
                //$returnUrl = $this->getCheckoutHelper()->getUrl('sales/order/view/order_id/'.$order_id );
                $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/success');
                //$returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/success');
                error_log($returnUrl.' ::Payu debug 3');
                error_log($order_id.' ::Payu debug 4');
                $objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
                $payment = $order->getPayment();
                $paymentMethod->postProcessing($order, $payment, $params);
                $msg = 'Thank you for your purchase! Your order number is:'.$orderIncrementId.'.';
                $this->messageManager->addSuccessMessage(__($msg));
                //End
                if(!$this->getOrder()->getId()){
                    error_log($order_id.' ::Payu debug 4.1');
                    $this->_checkoutSession->setLastOrderId($order->getId());
                    error_log($order_id.' ::Payu debug 4.2');
                    $this->_checkoutSession->setLastQuoteId($order->getQuoteId());
                    error_log($order_id.' ::Payu debug 4.3');
                    $this->_checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
                    error_log($order_id.' ::Payu debug 4.4');
                    $this->_checkoutSession->setLastRealOrderId($order->getIncrementId());
                    error_log($order_id.' ::Payu debug 4.5');
                    $_coreRegistry= $objectManager->get('\Magento\Framework\Registry');
                    error_log($order_id.' ::Payu debug 4.6');
                    $_coreRegistry->register('current_order', $order);
                
                    error_log($order->getId().' ::Payu debug 4.7'.$order->getQuoteId());
                }
               


            } else {

                $this->messageManager->addErrorMessage(__('Payment failed. Please try again or choose a different payment method'));
                $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/failure');
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            error_log('Payu debug 4.8'.$e->getMessage());
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
             error_log('Payu debug 4.9'.$e->getMessage());
            $this->messageManager->addExceptionMessage($e, __('We can\'t place the order.'));
        }
error_log($returnUrl.' ::Payu debug 6');
        $this->getResponse()->setRedirect($returnUrl);
    }

}

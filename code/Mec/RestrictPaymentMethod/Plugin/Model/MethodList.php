<?php
namespace Mec\RestrictPaymentMethod\Plugin\Model;
 
class MethodList
{
	public function afterGetAvailableMethods(
        \Magento\Payment\Model\MethodList $subject,
    	$availableMethods,
    	\Magento\Quote\Api\Data\CartInterface $quote = null
	) {
    	$shippingCountry = $this->getShippingCountryFromQuote($quote);
      $billingCountry = $this->getBillingCountryFromQuote($quote);
    	foreach ($availableMethods as $key => $method) {
        // Here we will hide CashonDeliver method while customer select FlateRate Shipping Method
        if(($method->getCode() == 'payu') && ($shippingCountry != 'IN') && ($billingCountry != 'IN')) {
           unset($availableMethods[$key]);
        }
        
       
        if(($method->getCode() == 'stripe_payments') && ($billingCountry == 'IN')) {
           unset($availableMethods[$key]);
        }

    	}
        
    	return $availableMethods;
	}
 
	/**
 	* @param \Magento\Quote\Api\Data\CartInterface $quote
 	* @return string
 	*/
	private function getShippingCountryFromQuote($quote)
	{
          $writer = new \Zend\Log\Writer\Stream(BP.'/var/log/shipping_country.log');
          $logger = new \Zend\Log\Logger();
          $logger->addWriter($writer);

    	  if($quote) {
        	//return $quote->getShippingAddress()->getShippingMethod();
                $country_id = $quote->getShippingAddress()->getData('country_id');
                $logger->info('b:'.$country_id);
                return $country_id;
    	  }
 
    	  return '';
	}

        /**
        * @param \Magento\Quote\Api\Data\CartInterface $quote
        * @return string
        */
        private function getBillingCountryFromQuote($quote)
        {
          $writer = new \Zend\Log\Writer\Stream(BP.'/var/log/billing_country.log');
          $logger = new \Zend\Log\Logger();
          $logger->addWriter($writer);

          if($quote) {
          //return $quote->getBillingAddress()->getShippingMethod();
                $country_id = $quote->getBillingAddress()->getData('country_id');
                $logger->info($country_id);
                return $country_id;
          }
 
        return '';
      }
}

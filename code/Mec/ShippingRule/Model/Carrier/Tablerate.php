<?php
namespace Mec\ShippingRule\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;

class Tablerate extends \Magento\OfflineShipping\Model\Carrier\Tablerate
{
 
    public function collectRates(RateRequest $request)
    {

        error_log("tablerate shipping");
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        // exclude Virtual products price from Package value if pre-configured
        if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getProduct()->isVirtual()) {
                            $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                        }
                    }
                } elseif ($item->getProduct()->isVirtual()) {
                    $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
                }
            }
        }

        // Free shipping by qty
        $freeQty = 0;
        $freePackageValue = 0;
        $subTotal = 0;
        $is_home_category_available = 0;
        $home_category_id = 1393;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                $product = $item->getProduct(); 
                $cats = $product->getCategoryIds();
                if (in_array($home_category_id, $cats)) {
                    $is_home_category_available = 1;
                } 
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeShipping = is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0;
                            $freeQty += $item->getQty() * ($child->getQty() - $freeShipping);
                        }
                    }
                } elseif ($item->getFreeShipping() || $item->getAddress()->getFreeShipping()) {
                    $freeShipping = $item->getFreeShipping() ?
                        $item->getFreeShipping() : $item->getAddress()->getFreeShipping();
                    $freeShipping = is_numeric($freeShipping) ? $freeShipping : 0;
                    $freeQty += $item->getQty() - $freeShipping;
                    $freePackageValue += $item->getBaseRowTotal();
                }
                $subTotal += $item->getBaseRowTotal();
            }
            $oldValue = $request->getPackageValue();
            $newPackageValue = $oldValue - $freePackageValue;
            $request->setPackageValue($newPackageValue);
            $request->setPackageValueWithDiscount($newPackageValue);
        }

        if (!$request->getConditionName()) {
            $conditionName = $this->getConfigData('condition_name');
            $request->setConditionName($conditionName ? $conditionName : $this->_defaultConditionName);
        }

        // Package weight and qty free shipping
        $oldWeight = $request->getPackageWeight();
        $oldQty = $request->getPackageQty();

        $request->setPackageWeight($request->getFreeMethodWeight());
        $request->setPackageQty($oldQty - $freeQty);

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();
        $rate = $this->getRate($request);

        $request->setPackageWeight($oldWeight);
        $request->setPackageQty($oldQty);

        if (!empty($rate) && $rate['price'] >= 0) {
            if ($request->getPackageQty() == $freeQty) {
                $shippingPrice = 0;
            } else {
                $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
            }
            error_log($shippingPrice.'homwareproduct 1');
            error_log('homwareproduct');
           //New Shipping rate based on subtotal by Alex
           $baseSubTotal = $subTotal;
            if($is_home_category_available != 1){
                error_log('nohomewareproduct');
                if($baseSubTotal < 15000){
                    $shippingPrice = 1500;
                }else if( $baseSubTotal > 15000 && $baseSubTotal < 25000){
                    $shippingPrice = $shippingPrice - ($shippingPrice*25)/100;
                }else if( $baseSubTotal > 25000 && $baseSubTotal < 50000){
                    $shippingPrice = $shippingPrice - ($shippingPrice*50)/100;
                }else if( $baseSubTotal > 50000){
                    $shippingPrice = 0;
                }else{
                    $shippingPrice = $shippingPrice;
                }
            }
           
          
            $method = $this->createShippingMethod($shippingPrice, $rate['cost']);
            $result->append($method);
        } elseif ($request->getPackageQty() == $freeQty) {

            /**
             * Promotion rule was applied for the whole cart.
             *  In this case all other shipping methods could be omitted
             * Table rate shipping method with 0$ price must be shown if grand total is more than minimal value.
             * Free package weight has been already taken into account.
             */
            $request->setPackageValue($freePackageValue);
            $request->setPackageQty($freeQty);
            $rate = $this->getRate($request);
            if (!empty($rate) && $rate['price'] >= 0) {
                $method = $this->createShippingMethod(0, 0);
                $result->append($method);
            }
        } else {
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Error $error */
            $error = $this->_rateErrorFactory->create(
                [
                    'data' => [
                        'carrier' => $this->_code,
                        'carrier_title' => $this->getConfigData('title'),
                        'error_message' => $this->getConfigData('specificerrmsg'),
                    ],
                ]
            );
            $result->append($error);
        }

        return $result;
    }

    private function createShippingMethod($shippingPrice, $cost)
    {
        /** @var  \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_resultMethodFactory->create();
        error_log("create bestway shipping method");
        $method->setCarrier($this->getCarrierCode());
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('bestway');
        $method->setMethodTitle($this->getConfigData('name'));
        $method->setPrice($shippingPrice);
        $method->setCost($cost);
        return $method;
    }

    public function convertToBaseCurrency($price)
    {
     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
 
     $priceCurrencyFactory = $objectManager->get('Magento\Directory\Model\CurrencyFactory');
     $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
 
     $currencyCodeTo = $storeManager->getStore()->getCurrentCurrency()->getCode();
     $currencyCodeFrom = $storeManager->getStore()->getBaseCurrency()->getCode();
 
     $itemAmount = $price; // product price
     $rate = $priceCurrencyFactory->create()->load($currencyCodeTo)->getAnyRate($currencyCodeFrom);
     return $itemAmount = $itemAmount * $rate;
  }

}

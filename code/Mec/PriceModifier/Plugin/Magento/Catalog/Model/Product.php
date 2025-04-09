<?php


namespace Mec\PriceModifier\Plugin\Magento\Catalog\Model;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Amasty\Geoip\Model\Geolocation;

class Product
{
    private $remoteAddress;
    protected $dataHelper;
    protected $productData;
    private $geolocation;

    public function __construct(
        RemoteAddress $remoteAddress,
        \Mec\PriceModifier\Helper\Data $dataHelper,
        \Mec\SuggestedProducts\Block\SuggestedProduct $productData,
        Geolocation $geolocation
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->dataHelper = $dataHelper;
        $this->productData = $productData;
        $this->geolocation = $geolocation;
    }

    public function getCountryCode(){
        $currentIp = $this->remoteAddress->getRemoteAddress();
	
        $location = $this->geolocation->locate($currentIp);
        $country = $location->getCountry();
        if(isset($_GET['mtest']) && $_GET['mtest']=='ktest') {
        echo 'CURRENT IPs: '.$currentIp."<br/>";
        echo 'COUNTRY CODE:: '.$country."<br/>";
    }
        return $country;

    }

    public function afterGetPrice(\Magento\Catalog\Model\Product $subject, $result) {
        $country = $this->getCountryCode();
    	if(empty($country)) {
    		$country = 'IN';
    	}
        $additional_price = 0;
    	if(isset($_GET['mtest']) && $_GET['mtest']=='ktest') {
    		echo 'COUNTRY CODE:: '.$country."<br/>";
    		echo 'RESULT: '.$result."<br/>";
    	}
        if($country != 'IN'){
            $product_id = $subject->getId();
            if($country == 'US'){
                $us_price_rate = $this->dataHelper->getUsPriceRate($product_id);
                if(!empty($us_price_rate)){
                    //$us_price_rate = 0;
                     $additional_price = ($us_price_rate*$result);
                }				
                //$additional_price = ($us_price_rate*$result);
            }else{
                $world_price_rate = $this->dataHelper->getWorldPriceRate($product_id);
                if(!empty($world_price_rate)){
                    //$world_price_rate = 0;
                    $additional_price = ($world_price_rate*$result);
                }
                //$additional_price = ($world_price_rate*$result);
            }
        }
        $final_price = ($result+$additional_price);
	if(isset($_GET['mtest']) && $_GET['mtest']=='ktest') {
		echo 'ADDITIONAL PRICE: '.$additional_price."<br/>";
		echo 'FINAL PRICE: '.$final_price;
		die;
	}
		return $final_price;
    }

    /*public function afterGetName(\Magento\Catalog\Model\Product $subject, $result)
    {
        return $result;
    }*/

    public function afterGetSpecialPrice(\Magento\Catalog\Model\Product $subject, $result)
    {
        $new_special_price = $result;
        $country = $this->getCountryCode();
        if($country != 'IN'){
            $product_id = $subject->getId();
            if($country == 'US'){
                $us_special_price = $this->dataHelper->getUsSpecialPrice($product_id); //212
                if(!empty($us_special_price)){
                    $new_special_price = $us_special_price;
                }
            }else{
                $world_special_price = $this->dataHelper->getWorldSpecialPrice($product_id); //213
                if(!empty($world_special_price)){
                    $new_special_price = $world_special_price;
                }
            }
        }
        return $new_special_price;
    }

}

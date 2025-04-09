<?php
namespace Mec\PriceModifier\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Amasty\Geoip\Model\Geolocation;

class PricecalculationsAfterAddtoCart implements ObserverInterface
{
    protected $_storeManager;
    private $remoteAddress;
    protected $dataHelper;
    private $geolocation;
    protected $_priceHelper;

    public function __construct(
        RemoteAddress $remoteAddress,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mec\PriceModifier\Helper\Data $dataHelper,
        Geolocation $geolocation,
        \Magento\Framework\Pricing\Helper\Data $priceHelper

    ) {
        $this->remoteAddress = $remoteAddress;
        $this->_storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->geolocation = $geolocation;
        $this->_priceHelper = $priceHelper;
        //parent::__construct($data );
    }

    public function getCountryCode(){
        $currentIp = $this->remoteAddress->getRemoteAddress();
        $location = $this->geolocation->locate($currentIp);
        $country = $location->getCountry();
        return $country;
    }

    public function convertToBaseCurrency($price)
    {
        $formattedCurrencyValue = $this->_priceHelper->currency($price, false, false);
        return $formattedCurrencyValue;
    }
     
    public function getCurrentCurrencyRate()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyRate();
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $writer = new \Zend\Log\Writer\Stream(BP.'/var/log/custom_price.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        /* Code here */
        $quote_item = $observer->getEvent()->getQuoteItem();
        $quote_data = $quote_item->getData();
        $sku = $quote_data['sku'];
        //$child_product_id = $this->dataHelper->getChildProductId($quote_id,$item_id);
        $child_product_id = $this->dataHelper->getProductIdBySku($sku);
        if ($quote_item->getParentItem()) {$quote_item = $quote_item->getParentItem();}
	    //$item = $quote_item->getProduct();
            $country = $this->getCountryCode();

        if($country != 'IN'){
            $special_price = 0;
            $main_price = $this->dataHelper->getMainPrice($child_product_id);

            if($country == 'US'){
                $us_price_rate = $this->dataHelper->getUsPriceRate($child_product_id);
                if(empty($us_price_rate)){
                    $us_price_rate = 0;
                }		
                $additional_price = ($us_price_rate*$main_price);
                
                $us_special_price = $this->dataHelper->getUsSpecialPrice($child_product_id); //212
                if(!empty($us_special_price)){
                    $special_price = $us_special_price;
                }
            }else{
                $world_price_rate = $this->dataHelper->getWorldPriceRate($child_product_id);
                if(empty($world_price_rate)){
                    $world_price_rate = 0;
                }
                $additional_price = ($world_price_rate*$main_price);

                $world_special_price = $this->dataHelper->getWorldSpecialPrice($child_product_id); //213
                if(!empty($world_special_price)){
                    $special_price = $world_special_price;
                }
            }
            if($special_price>0){
                $custom_price = $special_price;
            }else{
                $custom_price = ($main_price+$additional_price);
            }

            $rate = $this->getCurrentCurrencyRate();
            $custom_price = ($custom_price*$rate);
            $quote_item->setPrice($custom_price);
            $quote_item->setOriginalCustomPrice($custom_price);
            $quote_item->setCustomPrice($custom_price);

            //$quote_item->setPrice($original);
            /*if($special_price > 0){
                $converted_special_price = $this->convertToBaseCurrency($special_price);
                $quote_item->specialPrice($converted_special_price);
                $quote_item->originalSpecialPrice($converted_special_price);
            }*/
             //$msg ='qi'.$quote_id.'ii'.$item_id.'cpi'.$child_product_id.'mp'.$main_price;
             //$logger->info($msg);
            $logger->info(json_encode($quote_item->getData()));
        }

    }
}

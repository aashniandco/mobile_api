<?php
namespace Mec\CustomLogs\Observer;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Amasty\Geoip\Model\Geolocation;
use Magento\Framework\App\ResourceConnection;

class Customlogs implements \Magento\Framework\Event\ObserverInterface
{
  protected $_storeManager;
  private $remoteAddress;
  private $geolocation;
  protected $resourceConnection;

  public function __construct(
      \Magento\Store\Model\StoreManagerInterface $storeManager,
      RemoteAddress $remoteAddress,
      Geolocation $geolocation,
      ResourceConnection $resourceConnection
  ){
      $this->_storeManager = $storeManager;
      $this->remoteAddress = $remoteAddress;
      $this->geolocation = $geolocation;
      $this->resourceConnection = $resourceConnection;
  }

  public function execute(\Magento\Framework\Event\Observer $observer)
  {
	  return $this;
    //$order= $observer->getData('order');
    //$order->doSomething();
    $product = $observer->getProduct();
    $sku = $product->getSku();
    $price = $product->getFinalPrice();
    $current_ip = $this->remoteAddress->getRemoteAddress();
    $location = $this->geolocation->locate($current_ip);
    $city = $location->getCity();
    $country_code = $location->getCountry();
    $currency_code = $this->_storeManager->getStore()->getCurrentCurrencyCode();

    $connection = $this->resourceConnection->getConnection();
    $tableName = $connection->getTableName('mec_custom_logs_product');
    $sql = "SELECT log_id,created_at FROM $tableName WHERE sku = '$sku' AND ip_address = '$current_ip' AND DATE(created_at) = DATE(NOW())";
    $result = $connection->fetchOne($sql);

    if($result){
     $sql = "UPDATE $tableName SET price = '$price' , country_code = '$country_code', city = '$city' , 
             currency_code = '$currency_code',updated_at= Now()
       WHERE log_id='$result'";
      $connection->query($sql);
    }else{      
      $sql = "INSERT INTO " . $tableName . " (log_id, ip_address, country_code, city, currency_code, sku, price) Values ('','$current_ip','$country_code','$city','$currency_code','$sku','$price')";
      $connection->query($sql);

    }
    
    //echo $msg = "catalog product view";
    //$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/custom_logs.log');
    //$logger = new \Zend\Log\Logger();
    //$logger->addWriter($writer);
    //$logger->info($msg);

    return $this;
  }
}

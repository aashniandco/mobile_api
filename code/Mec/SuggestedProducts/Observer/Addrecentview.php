<?php
namespace Mec\SuggestedProducts\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;

class Addrecentview implements ObserverInterface
{
    /**
     * Below is the method that will fire whenever the event runs!
     *
     * @param Observer $observer
     */

    protected $session;
    protected $_customer;
    protected $resourceConnection;
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Customer\Model\Session $customer,
         ResourceConnection $resourceConnection,
        \Magento\Store\Model\StoreManagerInterface $storeManager

    ) {
        $this->session = $session;
        $this->_customer = $customer;
        $this->resourceConnection = $resourceConnection;
        $this->_storeManager = $storeManager;
        //parent::__construct($data );
    }

    public function execute(Observer $observer)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('mec_suggested_products_recently_viewed');
        $product = $observer->getProduct();
        $productId = $product->getId();
        $store_id = $this->_storeManager->getStore()->getId();
        $customer = $this->_customer;
        $customer_id = $customer->getId();
        $visitor = $this->session->getVisitorData();
        //print_r($this->session->getVisitorData());
        $visitor_id = $visitor['visitor_id'];

        if(!empty($customer_id)){
            $sql = "SELECT * FROM $tableName WHERE customer_id = '$customer_id' AND product_id = '$productId' LIMIT 1";
            $result = $connection->fetchAll($sql);
            if (count($result) > 0) {
                $update_sql = "UPDATE " . $tableName . " SET created_at = NOW() WHERE product_id = '" . $result[0]['product_id'] . "' AND customer_id = '" . $result[0]['customer_id'] . "'";
                $connection->query($update_sql);
            } else {
                $sql = "INSERT INTO " . $tableName . " (recently_viewed_id, visitor_id, customer_id, product_id,store_id) Values ('','$visitor_id','$customer_id','$productId','$store_id')";
                $connection->query($sql);
            }

        }else {
            $customer_id = 0;
            $sql = "SELECT * FROM $tableName WHERE visitor_id = '$visitor_id' AND product_id = '$productId' LIMIT 1";
            $result = $connection->fetchAll($sql);
            if (count($result) > 0) {
                $update_sql = "UPDATE " . $tableName . " SET created_at = NOW() WHERE product_id = '" . $result[0]['product_id'] . "' AND visitor_id = '" . $result[0]['visitor_id'] . "'";
                $connection->query($update_sql);
            } else {
                $sql = "INSERT INTO " . $tableName . " (recently_viewed_id, visitor_id, customer_id, product_id,store_id) Values ('','$visitor_id','$customer_id','$productId','$store_id')";
                $connection->query($sql);
            }
        }
    }
}

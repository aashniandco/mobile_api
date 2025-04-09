<?php 
namespace Fermion\LoyaltyPoint\Model;
use Exception;
 
class AddLoyaltyManagement {

	protected $store_manager;    
    protected $connection;
    private $objectManager;
    private $LoyaltypointHelper;
    private $CustomerFactory;
    
   
   
    public function __construct(        
        \Magento\Store\Model\StoreManagerInterface $storeManager,        
        \Magento\Framework\App\ResourceConnection $resourceConn,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Fermion\LoyaltyPoint\Helper\LoyaltypointHelper $LoyaltypointHelper,
        \Magento\Customer\Model\CustomerFactory $CustomerFactory
        
    ) {        
        $this->store_manager = $storeManager;
        $this->connection = $resourceConn->getConnection();
        $this->objectManager = $objectmanager;
        $this->LoyaltypointHelper = $LoyaltypointHelper;
        $this->CustomerFactory = $CustomerFactory;
    }


	/**
	 * {@inheritdoc}
	 */
	public function addLoyalty($customerdata)
	{   
        $customer_email = isset($customerdata['customer_email']) ? $customerdata['customer_email'] : '';
        $customer_fname = isset($customerdata['customer_firstname']) ? $customerdata['customer_firstname'] : '';
        $customer_lastname = isset($customerdata['customer_lastname']) ? $customerdata['customer_lastname'] : '';
        $order_amount = isset($customerdata['order_amount']) ? $customerdata['order_amount'] : '';
        
        if (empty($customer_email)){
        	$resp = ['status' => "false", 'msg' => 'Customer Email is missing.'];
            return json_encode($resp);die;
        }elseif (empty($customer_fname)){
            $resp = ['status' => "false", 'msg' => 'Customer First Name is missing.'];
            return json_encode($resp);die;
        }elseif (empty($customer_lastname)){
            $resp = ['status' => "false", 'msg' => 'Customer Last Name is missing.'];
            return json_encode($resp);die;
        }elseif (empty($order_amount)){
            $resp = ['status' => "false", 'msg' => 'Order Amount is missing.'];
            return json_encode($resp);die;
        }
        $customer =  $this->CustomerFactory->create();
        $customer->setWebsiteId(1); 
        $customerId = $customer->loadByEmail($customer_email)->getId();
        if (!$customerId) {
            try {
            	$customer->setEmail($customer_email);
                $customer->setFirstname($customer_fname);
                $customer->setLastname($customer_lastname);
                $password = $this->generateRandomString(10);
                $customer->setPassword($password);
                $customer->setForceConfirmed(true);
                $customer->save();
                $customerId = $customer->getId();
            } catch (Exception $e) {
                $respArr = ['error' => 1,'msg' => $e->getMessage()];
            }
        }
        $customerdata['customer_id'] = $customerId; 
        $loyalty_points = ((float)$order_amount) / 1000;
        $customerdata['loyalty_points'] = $loyalty_points;
        $orderInsResp =  $this->LoyaltypointHelper->insertOrderLoyaltyPoint($customerdata);
        $totalPoints = isset($orderInsResp['total_points']) ? (int) $orderInsResp['total_points'] : 0;
        if(isset($orderInsResp['error']) && $orderInsResp['error'] == 0 && $totalPoints > 0){
            $customerdata['total_points'] = $totalPoints;
            $loyaltyResp =  $this->LoyaltypointHelper->addLoyaltyPointsToCustomer($customerdata);
            if($loyaltyResp){
                $respArr['status'] = "true";
            }else{
                $respArr['status'] = "false";
            }
        }else{
            $respArr['status'] = "false";
            $respArr['msg'] = isset($orderInsResp['msg']) ? $orderInsResp['msg'] : 'Something went wrong.';
        }
        return json_encode($respArr);
    }

    public function generateRandomString($length) {
        $characters = 
       '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@#$%&';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;

    }
}
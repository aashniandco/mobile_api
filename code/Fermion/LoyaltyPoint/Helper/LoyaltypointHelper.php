<?php 
namespace Fermion\LoyaltyPoint\Helper;
use Magento\Framework\App\Area;
use DateTime;

class LoyaltypointHelper extends \Magento\Framework\App\Helper\AbstractHelper {            
    protected $store_manager;
    protected $customerSession;   
    protected $connection;
    private $objectManager;
    private $RuleFactory;
    private $url;
    private $TransportBuilder;
    private $ScopeConfigInterface;
   
   
    public function __construct(        
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ResourceConnection $resourceConn,
        \Magento\SalesRule\Model\RuleFactory $RuleFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Customer\Model\Url $url,
        \Magento\Framework\Mail\Template\TransportBuilder $TransportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $ScopeConfigInterface
        
    ) {        
        $this->store_manager = $storeManager;
        $this->customerSession = $customerSession;
        $this->connection = $resourceConn->getConnection();
        $this->objectManager = $objectmanager;
        $this->RuleFactory = $RuleFactory;
        $this->url = $url;
        $this->transportBuilder = $TransportBuilder;
        $this->ScopeConfigInterface = $ScopeConfigInterface;

    }


    /* return available result */

    
    public function addLoyaltyPointsToCustomer($customerdata) {
        $respArr = array();
        try {
            $customer_id = isset($customerdata['customer_id']) ? $customerdata['customer_id'] :'';
            $customer_email = isset($customerdata['customer_email']) ? $customerdata['customer_email']:'';
            $loyalty_points = isset($customerdata['loyalty_points']) ? $customerdata['loyalty_points'] :'';
            $customer_firstname =isset($customerdata['customer_firstname']) ? $customerdata['customer_firstname'] :'';
            $customer_lastname = isset($customerdata['customer_lastname']) ? $customerdata['customer_lastname'] :'';
            $total_points = isset($customerdata['total_points']) ? $customerdata['total_points'] :'';
            
            // ----------Old Login----------
            // $oldLoayaltyPoint = $total_points - $loyalty_points;
            // error_log("copoun-----");
            // if($total_points >= 100 && $oldLoayaltyPoint < 100){
            //      error_log("coupon11=====");
            //     $coupon_code =  $this->getCouponCode($customer_email,$customer_firstname.' '.$customer_lastname,10);
            //     // echo $coupon_code;
            // }elseif($total_points >= 200 && $oldLoayaltyPoint < 200){
            //     $coupon_code =  $this->getCouponCode($customer_email,$customer_firstname.' '.$customer_lastname,20);
            // }else{
            //     $coupon_code = '';
            // }


            $level = 0;
            if($total_points <= 250 && $total_points > 100){
                $level = 1;
            }
            elseif($total_points > 250 && $total_points <= 500){
                $level = 2;
            }
            elseif($total_points > 500 && $total_points <= 750){
                $level = 3;
            }
            elseif($total_points > 750){
                $level = 4;
            }

            $respArr['total_points'] = $total_points;
            $respArr['level'] = $level;

            $selectSql = "SELECT * FROM customer_loyalty_points WHERE customer_email = '$customer_email'";
            $result =  $this->connection->fetchRow($selectSql,[$customer_email]);
            if ($result) {
                // $updateSql = "UPDATE customer_loyalty_points clp SET clp.total_loyaltypoints = ?, clp.level = ?, clp.coupon_code = if(clp.coupon_code = '','".$coupon_code."',concat(clp.coupon_code,',', '".$coupon_code."'))  WHERE clp.customer_email = ?";
                if($result['level'] == $level){
                    $updateSql = "UPDATE customer_loyalty_points clp SET clp.total_loyaltypoints = ?, clp.level = ? WHERE clp.customer_email = ?";
                    $this->connection->query($updateSql, [$total_points, $level, $customer_email]);
                }
                else{
                    $updateSql = "UPDATE customer_loyalty_points clp SET clp.total_loyaltypoints = ?, clp.level = ?, clp.has_generated_coupon = ?, clp.has_birthday_coupon = ? WHERE clp.customer_email = ?";
                    $this->connection->query($updateSql, [$total_points, $level, 0, 0, $customer_email]);
                }
                
                $respArr['error'] = 0;
                $respArr['msg'] = 'success';
            } else {
                // $insertSql = "INSERT INTO `customer_loyalty_points` (total_loyaltypoints,level,coupon_code,customer_firstname,customer_email,customer_lastname,customer_id) VALUES (".$loyalty_points.",".$level.",'". $coupon_code."','".$customer_firstname."','".$customer_email."','".$customer_lastname."',".$customer_id.")";
                $insertSql = "INSERT INTO `customer_loyalty_points` (total_loyaltypoints,level,customer_firstname,customer_email,customer_lastname,customer_id) VALUES (".$loyalty_points.",".$level.",'".$customer_firstname."','".$customer_email."','".$customer_lastname."',".$customer_id.")";
                $this->connection->query($insertSql);
                $respArr['error'] = 0;
                $respArr['msg'] = 'success';
               
            }
        } catch (Exception $e) {
            $respArr['error'] = 1;
            $respArr['msg'] = $e->getMessage();
        }
        return $respArr;
    }

    public function removeLoyaltyDataByCustomer($customerdata){
        $respArr = array();
        try{
           
            $customer_id=isset($customerdata['customer_id']) ? $customerdata['customer_id'] :'' ;
            $customer_email = isset($customerdata['customer_email']) ? $customerdata['customer_email'] :'';
            $customer_loyalty_points = $this->connection->getTableName('customer_loyalty_points');

            $where = ['customer_email = ?' => $customer_email];
            $this->connection->delete($customer_loyalty_points, $where);
            $respArr['error'] = 0;
            $respArr['msg'] = 'success';
        }catch(Exception $e){
            
            $respArr['error'] = 1;
            $respArr['msg'] = $e->getMessage();

        }
    }

    function make_seed() {
        list($usec, $sec) = explode(' ', microtime());
        return (float) $sec + ((float) $usec * 1000000);
    }

    public function getCouponCode($email, $username, $discount, $date, $oneDayFlag, $startDate=null)
    {
        if($oneDayFlag){
            $startDate = $endDate = $date;
            $period = 1 * 24 * 60 * 60;
        }
        else{
            $startDate = new DateTime(date('Y/m/d'));
            $endDate = new DateTime($date);
            $interval = $startDate->diff($endDate);
            $daysForCoupon = $interval->days;
            $period = $daysForCoupon * 24 * 60 * 60;
        }
        $coupon_expiration_date = time() + $period;
        $coupon_expiration = date('Y-m-d', $coupon_expiration_date);
        $chars = "ABCDEFGHIJKLMNPQRSTUVWXYZ123456789";
        srand($this->make_seed());
        $i = 0;
        $code = '';
        while ($i <= 7) {
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $code = $code . $tmp;
            $i++;
        }
        $code = 'AASHNI'.$code ;
        $percentage = 10;
        $coupon['name'] = 'welcome'; //Generate a rule name
        $coupon['start'] = $startDate; //Coupon use start date
        $coupon['end'] = $coupon_expiration ; //coupon use end date
        $coupon['max_redemptions'] = 1; //Uses per Customer
        $coupon['discount_type'] ='by_percent'; //for discount type
        $coupon['discount_amount'] = $discount; //discount amount/percentage
        $coupon['flag_is_free_shipping'] = 'no';
        $coupon['redemptions'] = 1;
        $coupon['code'] = $code; //generate a random coupon code
        $coupon['desc'] = 'Loyalty Points Coupon Code';
        $shoppingCartPriceRule = $this->RuleFactory->create();
        $shoppingCartPriceRule->setName($coupon['name'])
            ->setDescription($coupon['desc'])
            ->setFromDate($coupon['start'])
            ->setToDate($coupon['end'])
            ->setUsesPerCustomer($coupon['max_redemptions'])
            ->setCustomerGroupIds(array('0', '1', '2', '3')) //select customer group
            ->setIsActive(1)
            ->setSimpleAction($coupon['discount_type'])
            ->setDiscountAmount($coupon['discount_amount'])
            ->setDiscountQty(0)
            ->setApplyToShipping($coupon['flag_is_free_shipping'])
            ->setTimesUsed($coupon['redemptions'])
            ->setWebsiteIds(array('1','2','3','4'))
            ->setCouponType(2)
            ->setCouponCode($coupon['code'])
            ->setIsRss(1)
            ->setUsesPerCoupon(1);
        $objectManager   = \Magento\Framework\App\ObjectManager::getInstance();

        $item_found = $objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Found')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product\Found')
            ->setValue(1) // 1 == FOUND
            ->setAggregator('all'); // match ALL conditions
        $shoppingCartPriceRule->getConditions()->addCondition($item_found);

        $conditions = $objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute('special_price')
            ->setOperator('>')
            ->setValue(0);
        $item_found->addCondition($conditions);

        $item_coupon1 =$objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute("designer")
            ->setOperator('!=')
            ->setValue("5548")
            ->setIsValueProcessed("true");
        $shoppingCartPriceRule->getActions()->addCondition($item_coupon1);

        $item_coupon2 =$objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute("designer")
            ->setOperator('!=')
            ->setValue("5516")
            ->setIsValueProcessed("true");
        $shoppingCartPriceRule->getActions()->addCondition($item_coupon2);

        $item_coupon3 =$objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute("designer")
            ->setOperator('!=')
            ->setValue("5531")
            ->setIsValueProcessed("true");
        $shoppingCartPriceRule->getActions()->addCondition($item_coupon3);

        $item_coupon4 =$objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute("designer")
            ->setOperator('!=')
            ->setValue("5575")
            ->setIsValueProcessed("true");
        $shoppingCartPriceRule->getActions()->addCondition($item_coupon4);

        $item_coupon5 =$objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute("designer")
            ->setOperator('!=')
            ->setValue("6131")
            ->setIsValueProcessed("true");
        $shoppingCartPriceRule->getActions()->addCondition($item_coupon5);

        $item_coupon6 =$objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute("designer")
            ->setOperator('!=')
            ->setValue("6328")
            ->setIsValueProcessed("true");
        $shoppingCartPriceRule->getActions()->addCondition($item_coupon6);
            
        $item_coupon7 =$objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute("designer")
            ->setOperator('!=')
            ->setValue("5827")
            ->setIsValueProcessed("true");
        $shoppingCartPriceRule->getActions()->addCondition($item_coupon7);

        $item_coupon8 =$objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute("designer")
            ->setOperator('!=')
            ->setValue("5573")
            ->setIsValueProcessed("true");
        $shoppingCartPriceRule->getActions()->addCondition($item_coupon8);
            
        $item_coupon9 =$objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute("designer")
            ->setOperator('!=')
            ->setValue("5856")
            ->setIsValueProcessed("true");
        $shoppingCartPriceRule->getActions()->addCondition($item_coupon9);

        $item_coupon10 =$objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute("designer")
            ->setOperator('!=')
            ->setValue("5625")
            ->setIsValueProcessed("true");
        $shoppingCartPriceRule->getActions()->addCondition($item_coupon10);

        $item_coupon11 =$objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute("designer")
            ->setOperator('!=')
            ->setValue("5796")
            ->setIsValueProcessed("true");
        $shoppingCartPriceRule->getActions()->addCondition($item_coupon11);
           
        $item_coupon12 =$objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute("designer")
            ->setOperator('!=')
            ->setValue("5759")
            ->setIsValueProcessed("true");
        $shoppingCartPriceRule->getActions()->addCondition($item_coupon12);

        $item_coupon13 =$objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute("designer")
            ->setOperator('!=')
            ->setValue("5526")
            ->setIsValueProcessed("true");
        $shoppingCartPriceRule->getActions()->addCondition($item_coupon13);

        $item_coupon14 =$objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute("designer")
            ->setOperator('!=')
            ->setValue("6192")
            ->setIsValueProcessed("true");
        $shoppingCartPriceRule->getActions()->addCondition($item_coupon14);

        $item_coupon15 =$objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute("designer")
            ->setOperator('!=')
            ->setValue("5555")
            ->setIsValueProcessed("true");
        $shoppingCartPriceRule->getActions()->addCondition($item_coupon15);

        $item_coupon16 =$objectManager->create('Magento\SalesRule\Model\Rule\Condition\Product\Combine')
            ->setType('Magento\SalesRule\Model\Rule\Condition\Product')
            ->setAttribute("designer")
            ->setOperator('!=')
            ->setValue("6151")
            ->setIsValueProcessed("true");
        $shoppingCartPriceRule->getActions()->addCondition($item_coupon16);

        $shoppingCartPriceRule->getActions()->setValue(1);

        $shoppingCartPriceRule->getConditions()->setValue(0);

        $shoppingCartPriceRule->save();
    
        $this->sendCustomEmail($code, $coupon_expiration_date,$email,$username);
        return $code;
    }

    public function sendCustomEmail($code,$expiryDate,$email,$username)
    {
        if (strlen($code) > 0 ) {
            $templateId = 28;   
            $loginLink = $this->url->getLoginUrl();
               
            $vars = Array('couponCode' => $code,'username' =>$username);
            // error_log(print_r($vars, true));

            $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->store_manager->getStore()->getId());
               
            $store_name = $this->ScopeConfigInterface->getValue('trans_email/ident_sales/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $store_email = $this->ScopeConfigInterface->getValue('trans_email/ident_sales/email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            // error_log("store name==".$store_name);
            // error_log("store email==".$store_email);

            $sender = array('name' => $store_name, 'email' => $store_email);
         
            $transport_mail_to_admin =   $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($vars)
            ->setFrom($sender)
            ->addTo($email)
             //->addBcc($bccEmail)
            ->getTransport();
                
            try{
                error_log("mail2");
                // error_log("hiii---");
                $res = $transport_mail_to_admin->sendMessage();
                // error_log(print_r($res,true));
                // error_log("mail sent::");
                    
            }catch(\Exception $e){
                error_log("mail3");
                error_log('ERROR    '.$e->getMessage());
            }
        }
    }

    public function insertOrderLoyaltyPoint($customerdata){
        $respArr = array();
        try{
            $customer_id = isset($customerdata['customer_id']) ? $customerdata['customer_id']:'';
            $customer_email = isset($customerdata['customer_email']) ? $customerdata['customer_email'] :'';
            $customer_firstname = isset($customerdata['customer_firstname']) ? $customerdata['customer_firstname'] :'';
            $customer_lastname = isset($customerdata['customer_lastname']) ? $customerdata['customer_lastname'] :'';
            $order_id = isset($customerdata['order_id']) ? $customerdata['order_id'] :'';
            
            $loyalty_points = isset($customerdata['loyalty_points']) ? $customerdata['loyalty_points'] :'';
            
            $selectSql = "select sum(loyalty_points) as total_points from order_loyalty_points where customer_id = ? AND is_expire = 0 group by customer_id"; 

            $result =  $this->connection->fetchOne($selectSql, [$customer_id]);

            $total_points = isset($result) ? $result + $loyalty_points : $loyalty_points;
            $order_sql = "INSERT INTO `order_loyalty_points` (customer_id, order_id, loyalty_points, balance_loyaltypoints, customer_email) VALUES(".$customer_id.",'".$order_id. "',".$loyalty_points.",".$total_points.",'".$customer_email."')";

            $query_result = $this->connection->query($order_sql);
            if ($query_result) {
                $respArr['error'] = 0;
                $respArr['msg'] = "Success";
                $respArr['total_points'] = $total_points;
            }else{
                $respArr['error'] = 1;
                $respArr['msg'] = "Something went wrong.";
            } 
        }catch(Exception $e){
            $respArr['error'] = 1;
            $respArr['msg'] = $e->getMessage();
        }
        return $respArr;
    }

    public function updateOrderLoyaltyPoints($orders)
    {
        $respArr = array();
        try
        {
            $customer = $this->customerSession->getCustomer();
            $customer_id = $customer->getId();
            $customer_email = $customer->getEmail();
            $customer_firstname = $customer->getFirstName();
            $customer_lastname = $customer->getLastName();
            $respArr['customer_id'] = $customer_id;
            $respArr['customer_email'] = $customer_email;
            $respArr['customer_firstname'] = $customer_firstname;
            $respArr['customer_lastname'] = $customer_lastname;
            foreach ($orders as $orderId) {
                $sql = "SELECT increment_id, base_grand_total, store_id FROM sales_order WHERE entity_id = ? AND (status='complete' OR status='processing' or status='pending')";
                $orderData = $this->connection->fetchAll($sql, [$orderId]);
                $increment_id = $orderData[0]['increment_id'];
                $baseGrandTotal = $orderData[0]['base_grand_total'];
                $store_id = $orderData[0]['store_id'];
                $sql = "SELECT loyalty_points FROM order_loyalty_points WHERE order_id = ?";
                $loyalty_points = $this->connection->fetchOne($sql, [$increment_id]);
                if(!isset($loyalty_points) || $loyalty_points == '' || $loyalty_points == null){
                    $loyalty_points = ((float)$baseGrandTotal) / 500;
                    $selectSql = "select sum(loyalty_points) as total_points from order_loyalty_points where customer_id = ? AND is_expire = 0 group by customer_id"; 
                    $result =  $this->connection->fetchOne($selectSql, [$customer_id]);
                    $total_points = isset($result) ? $result + $loyalty_points : $loyalty_points;
                    $sql = "INSERT INTO order_loyalty_points (customer_id, order_id, loyalty_points, balance_loyaltypoints, customer_email) VALUES(?, ?, ?, ?, ?)";
                    $insert = $this->connection->query($sql, [$customer_id, $increment_id, $loyalty_points, $total_points, $customer_email]);
                    if ($insert) {
                        $respArr['error'] = 0;
                        $respArr['msg'] = "Success";
                    }
                    else{
                        $respArr['error'] = 1;
                        $respArr['msg'] = "Something went wrong.";
                    }
                }
                else{
                    $selectSql = "select sum(loyalty_points) as total_points from order_loyalty_points where customer_id = ? AND is_expire = 0 group by customer_id"; 
                    $result =  $this->connection->fetchOne($selectSql, [$customer_id]);
                    $total_points = isset($result) ? $result + $loyalty_points : $loyalty_points;
                    $insertSql = "INSERT INTO order_loyalty_points (customer_id, order_id, loyalty_points, balance_loyaltypoints, customer_email) VALUES(?, ?, ?, ?, ?)";
                    $query = $this->connection->query($insertSql, [$customer_id, $increment_id, $loyalty_points, $total_points, $customer_email]);
                    if ($query) {
                        $respArr['error'] = 0;
                        $respArr['msg'] = "Success";
                    }
                    else{
                        $respArr['error'] = 1;
                        $respArr['msg'] = "Something went wrong.";
                    }
                }
            }
            $selectSql = "select sum(loyalty_points) as total_points from order_loyalty_points where customer_id = ? AND is_expire = 0 group by customer_id"; 
            $total_points =  $this->connection->fetchOne($selectSql, [$customer_id]);
            $respArr['total_points'] = $total_points;
            $flagQuery = "UPDATE customer_loyalty_points SET double_points_flag = 1 WHERE customer_id = ?";
            $execFlagQuery = $this->connection->query($flagQuery, [$customer_id]);
        }
        catch(Exception $e)
        {
            $respArr['error'] = 1;
            $respArr['msg'] = $e->getMessage();
        }
        return $respArr;
    }

    
    public function getLoyaltyLevel()
    {
        $respArr = array();
        $respArr['guest'] = true;
        $respArr['hasLoyaltyPoints'] = false;
        if (!$this->customerSession->isLoggedIn()) {
            return $respArr;
        }
        $customerId = $this->customerSession->getCustomerId();
        // $customerId = 2439;
        $respArr['guest'] = false;
        $sql = "SELECT level, total_loyaltypoints, double_points_flag, has_generated_coupon, has_birthday_coupon FROM customer_loyalty_points WHERE customer_id = ?";
        $res = $this->connection->fetchAll($sql, [$customerId]);
        if($res){
            $level = $res[0]['level'];
            $respArr['double_points_flag'] = is_null($res[0]['double_points_flag']) || ($res[0]['double_points_flag'] == 0) ? false : true;
            $respArr['hasLoyaltyPoints'] = true;
            $respArr['level'] = $level;
            $respArr['loyalty_points'] = $res[0]['total_loyaltypoints'];
            $respArr['level_coupon_details'] = $this->getCouponLevelDetails($level);
            $respArr['level_has_coupon'] = false;
            $respArr['has_generated_coupon'] = $res[0]['has_generated_coupon'];
            $respArr['has_birthday_coupon'] = $res[0]['has_birthday_coupon'];

            $sql2 = "SELECT coupon_code, coupon_level, type, discount_percentage, coupon_date FROM loyalty_points_coupons WHERE customer_id = ? AND status = 1 AND coupon_level = ?";
            $res2 = $this->connection->fetchAll($sql2, [$customerId, $level]);
            if($res2){
                $currLevelCouponFlag = false;
                foreach ($res2 as $key => $value) {
                    if($value['type'] == 'birthday'){
                        $respArr['birthday_coupon']['coupon'] = $value['coupon_code'];
                        $respArr['birthday_coupon']['discount'] = $value['discount_percentage'];
                        continue;
                    }
                    $currLevelCouponFlag = true;
                    $respArr['discount_coupons'][$key]['coupons'][$key] = $value['coupon_code'];
                    $respArr['discount_coupons'][$key]['discount'][$key] = $value['discount_percentage'];
                }
                if($currLevelCouponFlag){
                    $respArr['level_has_coupon'] = true;
                }
            }
        }
        return $respArr;
    }

    // public function saveDiscountDates($dates)
    // {
    //     $respArr = array();
    //     $discountdates = array();
    //     $setStmt = '';
    //     $customer = $this->customerSession->getCustomer();
    //     $customer_email = $customer->getEmail();
    //     $username = $customer->getFirstName().' '.$customer->getLastName();
    //     $customer_id = $customer->getId();
    //     $date_one = $dates['date_one'];
    //     $discount = $dates['discount'];
    //     $level = $dates['level'];
    //     if($level == 1){
    //         if(empty($date_one)){
    //             $respArr['error'] = "dateNotFound";
    //             return $respArr;
    //         }
    //         $discountdates['date_one'] = $date_one;
    //         $setStmt = "SET clp.discount_date_one = ?";
    //         $queryArray = [$date_one, $customer_email];
    //     }
    //     else{
    //         $date_two = $dates['date_two'];
    //         if(empty($date_one) || empty($date_two)){
    //             $respArr['error'] = "dateNotFound";
    //             return $respArr;
    //         }
    //         $discountdates['date_one'] = $date_one;
    //         $discountdates['date_two'] = $date_two;
    //         $setStmt = "SET clp.discount_date_one = ?, clp.discount_date_two = ?";
    //         $queryArray = [$date_one, $date_two, $customer_email];
    //     }
        
    //     try{
    //         $updateSql = "UPDATE customer_loyalty_points clp ".$setStmt." WHERE clp.customer_email = ?";
    //         $this->connection->query($updateSql, $queryArray);
    //         $selectSql = "SELECT id from `customer_loyalty_points` WHERE customer_email = ?";
    //         $customer_loyalty_id = $this->connection->fetchOne($selectSql, [$customer_email]);
    //         foreach ($discountdates as $key => $value) {
    //             $oneDayFlag = true;
    //             $coupon_code = $this->getCouponCode($customer_email, $username, $discount, $value, $oneDayFlag);
    //             $insertSql = "INSERT INTO `loyalty_points_coupons` (coupon_code, coupon_date, status, customer_loyalty_id, customer_id, coupon_level) VALUES(?, ?, ?, ?, ?, ?)";
    //             $this->connection->query($insertSql, [$coupon_code, $value, 1, $customer_loyalty_id, $customer_id, $level]);
    //             $respArr['coupon_code'][$key] = $coupon_code;
    //         }
    //         $respArr['success'] = true;
    //     }
    //     catch(Exception $e){
    //         $respArr['error'] = $e->getMessage();
    //     }
    //     return $respArr;
    // }


    public function generateDiscountCoupons($leveldata){
        $respArr = array();
        $level = $leveldata['level'];
        $coupon_type = 'exclusive';
        try{
            $customer = $this->customerSession->getCustomer();
            $customer_email = $customer->getEmail();
            $username = $customer->getFirstName().' '.$customer->getLastName();
            $customer_id = $customer->getId();
            if(isset($leveldata['getBirthdayCoupon']) && $leveldata['getBirthdayCoupon'] == true){
                $coupon_type = 'birthday';
            }
            $couponsQuery = "SELECT MAX(coupon_level) AS 'coupon_level' FROM loyalty_points_coupons WHERE customer_id = ? AND status = 1 AND type = ?";
            $coupon_level = $this->connection->fetchOne($couponsQuery, [$customer_id, $coupon_type]);
            if($coupon_level == null || $coupon_level == ''){
                $coupon_level = 0;
            }
            if($coupon_level != $level){
                $selectSql = "SELECT id from `customer_loyalty_points` WHERE customer_email = ?";
                $customer_loyalty_id = $this->connection->fetchOne($selectSql, [$customer_email]);
                $coupon_generation_details = $this->getCouponLevelDetails($level);
                $index = 0;
                $total_discount_coupons = $coupon_generation_details['total_discount_coupons'];
                $discount_on_coupon = $coupon_generation_details['discount_in_percentage'];
                if($coupon_type == 'birthday'){
                    $total_discount_coupons = 1;
                    $discount_on_coupon = $coupon_generation_details['birthday_discount_in_percent'];
                }
                $expireDate = date('Y-m-d', strtotime('+1 year'));
                for($i = 0 ; $i < $total_discount_coupons ; $i++){
                    if($coupon_type == 'birthday') {
                        $expireDate = date('Y-m-t');
                    }
                    $oneDayFlag = false;
                    $coupon_code = $this->getCouponCode($customer_email, $username, $discount_on_coupon, $expireDate, $oneDayFlag);
                    $insertSql = "INSERT INTO `loyalty_points_coupons` (coupon_code, coupon_date, status, customer_loyalty_id, customer_id, coupon_level, discount_percentage, type) VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
                    $this->connection->query($insertSql, [$coupon_code, $expireDate, 1, $customer_loyalty_id, $customer_id, $level, $discount_on_coupon, $coupon_type]);
                    $respArr['coupon_code'][$index] = $coupon_code;
                    $index++;
                }
                if($coupon_type == 'birthday'){
                    $updateCouponFlagQuery = "UPDATE `customer_loyalty_points` SET has_birthday_coupon = 1 WHERE customer_id = ?";
                    $this->connection->query($updateCouponFlagQuery, [$customer_id]);
                }
                else{
                    $updateCouponFlagQuery = "UPDATE `customer_loyalty_points` SET has_generated_coupon = 1 WHERE customer_id = ?";
                    $this->connection->query($updateCouponFlagQuery, [$customer_id]);
                }
                $respArr['discount'] = $discount_on_coupon;
                $respArr['success'] = true;
            }
            else{
                $respArr['msg'] = "levelHasCoupon";
            }
            
        }
        catch(Exception $e){
            $respArr['error'] = $e->getMessage();
        }
        return $respArr;   
    }


    public function getCouponLevelDetails($level){
        $coupon_generation_detail_sql = "SELECT discount_in_percentage, total_discount_coupons, birthday_discount_in_percent FROM `loyalty_points_discounts` WHERE loyalty_level = ?";
        $result = $this->connection->fetchRow($coupon_generation_detail_sql, [$level]);
        return $result;
    }
}
?>


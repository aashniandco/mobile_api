<?php 
namespace Fermion\LoyaltyPoint\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CheckAndDeleteLoyaltyCoupon implements ObserverInterface
{
	protected $customerSession;   
    protected $connection;
    protected $couponModel;
    protected $ruleModel;

    public function __construct(
    	\Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ResourceConnection $resourceConn,
        \Magento\SalesRule\Model\Coupon $couponModel,
        \Magento\SalesRule\Model\Rule $ruleModel
    ){
    	$this->customerSession = $customerSession;
    	$this->connection = $resourceConn->getConnection();
    	$this->couponModel = $couponModel;
    	$this->ruleModel = $ruleModel;
    }

    public function execute(Observer $observer)
    {
    	try{
    		$order = $observer->getEvent()->getOrder();
	    	$appliedDiscounts = $order->getAllItems()[0]->getAppliedRuleIds();
	    	if (!empty($appliedDiscounts)) {
			    foreach (explode(',', $appliedDiscounts) as $ruleId) {
			        $rule = $this->ruleModel->load($ruleId);
			        if ($rule->getCouponCode()) {
			            $couponCode = $rule->getCouponCode();
			            if($couponCode){
							$sql = "UPDATE loyalty_points_coupons SET status = 0 WHERE coupon_code = ?";
							$this->connection->query($sql, [$couponCode]);
							// $rule->delete();
						}
			        }
			    }
			}
    	}
    	catch(Exception $e){
    		error_log("CheckAndDeleteLoyaltyCoupon Observer Error :: ".$e->getMessage());
    		return false;
    	}
    }
}
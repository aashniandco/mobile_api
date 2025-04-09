<?php

namespace Fermion\LoyaltyPoint\Controller\Account;

use \Magento\Framework\App\Action\Action;
use Fermion\LoyaltyPoint\Helper\LoyaltypointHelper;
use Fermion\LoyaltyPoint\Block\Account\AccountLoyalty;
use \Magento\Customer\Api\CustomerRepositoryInterface;

class OrderLoyalty extends Action
{
	/**
     * @var Fermion\LoyaltyPoint\Helper\LoyaltypointHelper
     */
	private $helper;
	private $block;
	private $customerRepo;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		LoyaltypointHelper $helper,
		AccountLoyalty $block,
		CustomerRepositoryInterface $customerRepo
	){
		parent::__construct($context);
		$this->helper = $helper;
		$this->block = $block;
		$this->customerRepo = $customerRepo;
	}

	public function execute(){
		$respArr = array();
		$respArr['success'] = true;
		$data = $this->getRequest()->getParams();
		if(isset($data['paginate']) && $data['paginate'] == true){
			$respArr = $this->block->getOrderData($data['page']);
			echo json_encode($respArr);
		}
		elseif(isset($data['setDob']) && $data['setDob'] == true){
			$returnArray = array();
			if(isset($data['customer_id']) && isset($data['dob'])){
				$customer = $this->customerRepo->getById($data['customer_id']);
				$customer->setDob($data['dob']);
				$this->customerRepo->save($customer);
				$returnArray['success'] = true;
			}
			else{
				$returnArray['success'] = false;
			}
			echo json_encode($returnArray);
		}
		elseif(isset($data['orderSelected']) && $data['orderSelected'] == true){
			$orderInsResp = $this->helper->updateOrderLoyaltyPoints($data['orders']);
			$totalPoints = isset($orderInsResp['total_points']) ? (float) $orderInsResp['total_points'] : 0;
			if(isset($orderInsResp['error']) && $orderInsResp['error'] == 0 && $totalPoints > 0){
               	$loyaltyResp =  $this->helper->addLoyaltyPointsToCustomer($orderInsResp);
               	if($loyaltyResp){
                    $respArr['status'] = "true";
                    $respArr['level'] = $loyaltyResp['level'];
                    $respArr['total_points'] = $loyaltyResp['total_points'];
               	}
               	else{
                	$respArr['status'] = "false";
                	$respArr['msg'] = isset($orderInsResp['msg']) ? $orderInsResp['msg'] : 'Something went wrong.';
               	}
	        }
	        else{
	            $respArr['status'] = "false";
	            $respArr['msg'] = isset($orderInsResp['msg']) ? $orderInsResp['msg'] : 'Something went wrong.';
	        }
			echo json_encode($respArr);
		}
		elseif(isset($data['getBirthdayCoupon']) && $data['getBirthdayCoupon'] == true){
			$resp = $this->helper->generateDiscountCoupons($data);
			if(isset($resp['success']) && $resp['success'] == true){
				$birthday_coupon_html = "<div class='birthday-coupon-table'>
	                <table class='coupon-table'>
	                    <tbody>
	                        <tr>
	                            <th>Coupon</th>
	                            <th>Discount</th>
	                        </tr>
	                        <tr>
	                            <td>".$resp['coupon_code'][0]."</td>
	                            <td>".$resp['discount']."%</td>
	                        </tr>
	                    </tbody>
	                </table>
	            </div>";
	            $resp['coupon_html'] = $birthday_coupon_html;
			}
			echo json_encode($resp);die;
		}
		else{
			$resp = $this->helper->generateDiscountCoupons($data);
			error_log("lpmod :: response -> ".json_encode($resp));
			if(isset($resp['error'])){
				$respArr['error'] = $resp['error'];
				$respArr['success'] = false;
			}
			echo json_encode($respArr);
		}
	}
}
<?php 

namespace Fermion\NativeApp\Model\Api;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Fermion\NativeApp\Helper\AppNativeHelper;

class FetchUserDetails implements \Fermion\NativeApp\Api\FetchUserDetailsInterface
{
	/**
     * @var Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Fermion\NativeApp\Helper\AppNativeHelper
     */
    private $helper;

    public function __construct(
    	CustomerRepositoryInterface $customerRepository,
    	AppNativeHelper $helper
    ){
    	$this->customerRepository = $customerRepository;
    	$this->helper = $helper;
    }

    public function fetchDetails($customerDetails)
    {
        $requestJson = json_encode($customerDetails);
        $this->helper->validateRequests('/rest/V1/app_fetch_customer_details', $requestJson);

    	$password = "";
    	if(isset($customerDetails['user_id']) && !empty($customerDetails['user_id']))
    	{
    		$customer_id = $customerDetails['user_id'];
    		$customer = $this->customerRepository->getById($customer_id);
    		$socialSigninData = $this->helper->getDataforSocailSignedInUsers(null, $customerDetails['user_id']);
    		$password = ($socialSigninData['password_fake_email'] != null || $socialSigninData['password_fake_email'] != "") ? $socialSigninData['password_fake_email'] : "";
    	}
    	else{
    		$this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", "please provide user_id");
    	}
    	$dataArray = [
    		"user_id" => $customer->getId(), 
    		"first_name" => $customer->getFirstname(), 
    		"last_name" => $customer->getLastname(),
    		"email" => $customer->getEmail(), 
    		"created_at" => date("Y/m/d h:i:sa"), 
    		"password" => $password
    	];

    	$this->helper->sendResponse(200, "Success", $dataArray);
    }
}
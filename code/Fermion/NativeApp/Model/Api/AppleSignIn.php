<?php

namespace Fermion\NativeApp\Model\Api;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Fermion\NativeApp\Helper\AppNativeHelper;
use Magento\Framework\Math\Random;
use Fermion\NativeApp\Model\NativeTokens;
use Fermion\NativeApp\Model\ResourceModel\NativeTokens as NativeTokensResource;
use Magento\Store\Model\StoreManagerInterface;

class AppleSignIn implements \Fermion\NativeApp\Api\AppleSigninInterface
{

	/**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Fermion\NativeApp\Helper\AppNativeHelper
     */
    private $helper;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var Fermion\NativeApp\Model\NativeTokens
     */
    private $nativeTokens;

    /**
     * @var Fermion\NativeApp\Model\ResourceModel\NativeTokens
     */
    private $nativeTokensResource;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
	private $storeManager;


	public function __construct(
		CustomerRepositoryInterface $customerRepository,
		AppNativeHelper $helper,
		Random $mathRandom,
		NativeTokens $nativeTokens,
		NativeTokensResource $nativeTokensResource,
		StoreManagerInterface $storeManager
	){
		$this->customerRepository = $customerRepository;
		$this->helper = $helper;
		$this->mathRandom = $mathRandom;
		$this->nativeTokens = $nativeTokens;
		$this->nativeTokensResource = $nativeTokensResource;
		$this->storeManager = $storeManager;
	}

	public function signInWithApple($customerDetails)
	{
		$requestJson = json_encode($customerDetails);
		$this->helper->validateRequests('/rest/V1/app_apple_signin', $requestJson);
		
		try{
			if(isset($customerDetails['email']) && $customerDetails['email'] != '' || $customerDetails['email'] != null){
				$this->helper->saveSocialCustomerDetails($customerDetails);				
				$customer = $this->customerRepository->get($customerDetails['email']);
				$customer_id = $customer->getId();
				$socialSigninData = $this->helper->getDataforSocailSignedInUsers(null, $customer_id);
				if(!$socialSigninData){
					$data = [
		                'type' => 'apple',
		                'token_id' => (string)$customerDetails['social_id'],
		                'customer_id' => $customer_id
		            ];
		            $this->helper->setSocialLoginCustomerData($data);
		            

					$socialSigninData = $this->helper->getDataforSocailSignedInUsers(null, $customer_id);
				}
			}
			else{
				if(!isset($customerDetails['social_id'])){
		    		$this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", "Please provide social id");
		    	}

				$socialSigninData = $this->helper->getDataforSocailSignedInUsers($customerDetails['social_id']);
				$customerData = $this->helper->getSocialCustomerDetails($customerDetails['social_id']);
				$customerDetails['email'] = $customerData['email'];
				$customerDetails['firstname'] = $customerData['first_name'];
				$customerDetails['lastname'] = $customerData['last_name'];
				$customer_id = $socialSigninData['customer_id'];
            	$customer = $this->customerRepository->getById($customer_id);
			}
            $token = $this->mathRandom->getUniqueHash();
	    	$data = ['token' => $token, 'customer_id' => $customer_id];
	    	$NativeTokensModel = $this->nativeTokens;
	    	$NativeTokensModel->setData($data);
	    	$this->nativeTokensResource->save($NativeTokensModel);

			$password = ($socialSigninData['password_fake_email'] != null || $socialSigninData['password_fake_email'] != "") ? $socialSigninData['password_fake_email'] : "";

	    	$customer_name = $customer->getFirstname()." ".$customer->getLastname();
	    	$baseUrl = $this->storeManager->getStore()->getBaseUrl();
	        $redirectUrl = $baseUrl."?token=".$token;
	        $dataArray = ["redirect_url" => $redirectUrl, "user_id" => $customer->getId(), "name" => $customer_name, "email" => $customer->getEmail(), "created_at" => date("Y/m/d h:i:sa"), "password" => $password];
	        $this->helper->sendResponse(200, "Success", $dataArray);
		}
		catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        	$pass = $this->getRandomPassword();
        	$customerDetails['password'] = $pass;
        	$customerDetails['password_confirmation'] = $pass;
        	if(!isset($customerDetails['email']) || (isset($customerDetails['email']) && empty($customerDetails['email']))) {
        		$customerDetails['email'] = $this->_getRandomEmail('apple');
        	}
        	$this->helper->signUpCustomer($customerDetails, 'apple');
		}
		catch(Exception $e){
			$this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", $e->getMessage());
		}
	}


	protected function getRandomPassword(){
    	$letters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	    $numbers = "0123456789";
	    $specialChars = "!@#$%^&*()_-=+;:,.?";

	    $password = $numbers[rand(0, strlen($numbers) - 1)] 
	              . $specialChars[rand(0, strlen($specialChars) - 1)];

	    $remainingChars = $letters . $numbers . $specialChars;
	    $remainingLength = 6;

	    for ($i = 0; $i < $remainingLength; $i++) {
	        $password .= $remainingChars[rand(0, strlen($remainingChars) - 1)];
	    }

	    $password = str_shuffle($password);
        return $password;
    }


    protected function _getRandomEmail($type)
    {
        $len = 12;
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $address =  \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Math\Random')->getRandomString($len, $chars) .'@'. $type.'-user.com';
        return $address;
    }

}
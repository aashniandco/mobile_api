<?php

namespace Fermion\NativeApp\Model\Api;

use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestFactory;
use Fermion\NativeApp\Helper\AppNativeHelper;
use Magento\Framework\Math\Random;
use Fermion\NativeApp\Model\NativeTokens;
use Fermion\NativeApp\Model\ResourceModel\NativeTokens as NativeTokensResource;
use Magento\Store\Model\StoreManagerInterface;


class CustomerSignup implements \Fermion\NativeApp\Api\CustomerSignupInterface
{
	/**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    private $accountManagement;

	/**
     * @var \Magento\Customer\Model\CustomerExtractor
     */
    private $customerExtractor;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $_eventManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

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
		AccountManagementInterface $accountManagement,
		CustomerExtractor $customerExtractor,
		Context $context,
		Session $customerSession,
		RequestFactory $requestFactory,
		AppNativeHelper $helper,
		Random $mathRandom,
		NativeTokens $nativeTokens,
		NativeTokensResource $nativeTokensResource,
		StoreManagerInterface $storeManager
	){
		$this->accountManagement = $accountManagement;
		$this->customerExtractor = $customerExtractor;
		$this->_eventManager = $context->getEventManager();
		$this->session = $customerSession;
		$this->request = $requestFactory->create($data = array());
		$this->helper = $helper;
		$this->mathRandom = $mathRandom;
		$this->nativeTokens = $nativeTokens;
		$this->nativeTokensResource = $nativeTokensResource;
		$this->storeManager = $storeManager;
	}

	public function registerCustomer($customerDetails)
	{

		$requestJson = json_encode($customerDetails);
        $this->helper->validateRequests('/rest/V1/app_customer_signup', $requestJson);

		$dataArray = array();

		try{
			$customer = $this->customerExtractor->extract('customer_account_create', $this->request);
			$customer->setAddresses([]);
			$firstname = $customerDetails['firstname'];
	        $lastname = $customerDetails['lastname'];

	        if(preg_match('/[^\x20-\x7e]/', $firstname)) {
	        	$this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", "Please enter valid first name");
	        }
	        if(preg_match('/[^\x20-\x7e]/', $lastname)) {
	        	$this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", "Please enter valid last name");
	        }

	        $password = $customerDetails['password'];
	        $confirmation = $customerDetails['password_confirmation'];
	        $this->checkPasswordConfirmation($password, $confirmation);
			$customer->setEmail($customerDetails['email']);
			$customer->setFirstName($firstname);
			$customer->setLastName($lastname);
	        $extensionAttributes = $customer->getExtensionAttributes();
	        $extensionAttributes->setIsSubscribed(false);
	        $customer->setExtensionAttributes($extensionAttributes);

	        $customer = $this->accountManagement
	                ->createAccount($customer, $password);

	        $token = $this->mathRandom->getUniqueHash();
        	$data = ['token' => $token, 'customer_id' => $customer->getId()];
        	$NativeTokensModel = $this->nativeTokens;
        	$NativeTokensModel->setData($data);
        	$this->nativeTokensResource->save($NativeTokensModel);

        	$baseUrl = $this->storeManager->getStore()->getBaseUrl();
            $redirectUrl = $baseUrl."?token=".$token;
	        
	        $customer_name = $customer->getFirstname()." ".$customer->getLastname();

	        $this->_eventManager->dispatch(
	            'customer_register_success',
	            ['account_controller' => $this, 'customer' => $customer]
	        );

	        $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
	        if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
	        	$dataArray = ["redirect_url" => $redirectUrl, "user_id" => $customer->getId(), "name" => $customer_name, "email" => $customer->getEmail(), "created_at" => date("Y/m/d h:i:sa")];
	        	$this->helper->sendResponse(200, "Success", $dataArray);
	        }

	        // else {
	        //     $this->session->setCustomerDataAsLoggedIn($customer);
	        // }
	    
	    } 
	    catch (StateException $e) {
	    	$this->helper->sendResponse(400, "Bad Request", "", "StateException", "Customer with specified email already exists");
        } 
        catch (InputException $e) {
        	$this->helper->sendResponse(400, "Bad Request", "", "InputException", $e->getMessage());
        } 
        catch (LocalizedException $e) {
        	$this->helper->sendResponse(400, "Bad Request", "", "LocalizedException", $e->getMessage());
        } 
        catch (\Exception $e) {
        	$this->helper->sendResponse(400, "Bad Request", "", "Exception", $e->getMessage());
        }

        $this->session->setCustomerFormData($customerDetails);
        $dataArray = ["redirect_url" => $redirectUrl, "user_id" => $customer->getId(), "name" => $customer_name, "email" => $customer->getEmail(), "created_at" => date("Y/m/d h:i:sa")];
        $this->helper->sendResponse(200, "Success", $dataArray);
	}

	protected function checkPasswordConfirmation($password, $confirmation)
    {
        if ($password != $confirmation) {
        	$this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", "Please make sure your passwords match.");
        }
    }
}
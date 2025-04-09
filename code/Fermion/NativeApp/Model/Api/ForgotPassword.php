<?php

namespace Fermion\NativeApp\Model\Api;

use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Fermion\NativeApp\Helper\AppNativeHelper;

class ForgotPassword implements \Fermion\NativeApp\Api\ForgotPasswordInterface
{
	/**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
	private $accountManagement;

	/**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Fermion\NativeApp\Helper\AppNativeHelper
     */
    private $helper;


	public function __construct(
		AccountManagementInterface $accountManagement,
		CustomerRepositoryInterface $customerRepository,
		StoreManagerInterface $storeManager,
		AppNativeHelper $helper
	){
		$this->accountManagement = $accountManagement;
		$this->customerRepository = $customerRepository;
		$this->storeManager = $storeManager;
		$this->helper = $helper;
	}
	public function getForgotPasswordLink($email)
	{
        $requestJson = json_encode($email);
        $this->helper->validateRequests('/rest/V1/app_forgot_password', $requestJson);

		$dataArray = array();

		if ($email) {
            
            if (!\Zend_Validate::is($email, \Magento\Framework\Validator\EmailAddress::class)) {
            	$this->helper->sendResponse(400, "Not Found", "", "INVALID_EMAIL", "Please enter a valid email.");
            }

            try {
                $this->accountManagement->initiatePasswordReset(
                    $email,
                    AccountManagement::EMAIL_RESET
                );
            } 
            catch (NoSuchEntityException $exception) {
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } 
            catch (SecurityViolationException $exception) {
            	$this->helper->sendResponse(400, "Bad Request", "", "SECURITY_VIOLATION", $exception->getMessage());

            } 
            catch (\Exception $exception) {
            	$this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", $exception->getMessage());
            }

            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $customer = $this->customerRepository->get($email, $websiteId);
            $customer_name = $customer->getFirstname()." ".$customer->getLastname();
            $dataArray = ["user_id" => $customer->getId(), "name" => $customer_name, "email" => $customer->getEmail(), "created_at" => date("Y/m/d h:i:sa")];
            $this->helper->sendResponse(200, "Success", $dataArray);
        } 
        else {
        	$this->helper->sendResponse(400, "Bad Request", "", "INVALID_INPUT", "Please enter your email.");
        }
	}
}
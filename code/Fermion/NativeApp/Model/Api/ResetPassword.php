<?php

namespace Fermion\NativeApp\Model\Api;

use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Fermion\NativeApp\Helper\AppNativeHelper;


class ResetPassword implements \Fermion\NativeApp\Api\ResetPasswordInterface
{
	/**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Fermion\NativeApp\Helper\AppNativeHelper
     */
    private $helper;


	public function __construct(
		AccountManagementInterface $accountManagement,
		Session $session,
		CustomerRepositoryInterface $customerRepository,
		AppNativeHelper $helper
	){
		$this->accountManagement = $accountManagement;
		$this->session = $session;
		$this->customerRepository = $customerRepository;
		$this->helper = $helper;
	}

	public function resetCustomerPassword($customerDetails)
	{
        $requestJson = json_encode($customerDetails);
        $this->helper->validateRequests('/rest/V1/app_customer_reset_password', $requestJson);

		$resetPasswordToken = $customerDetails['token'];
        $password = $customerDetails['password'];
        $passwordConfirmation = $customerDetails['password_confirmation'];

        if ($password !== $passwordConfirmation) {
        	$errorMsg = "New Password and Confirm New Password values didn't match.";
        	$this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", $errorMsg);
        }

        if (iconv_strlen($password) <= 0) {
        	$errorMsg = "Please enter a new password.";
        	$this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", $errorMsg);
        }

        try {

            $customer = $this->helper->loadCustomerByRpToken($resetPasswordToken);
            if($customer == null || !$customer){
                $this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", "The token has expired or no customer is linked to the token");
            }

            $this->accountManagement->resetPassword(
                null,
                $resetPasswordToken,
                $password
            );
            
            $this->session->unsRpToken();
            $this->helper->sendResponse(200, "Success", "");

        } catch (InputException $e) {
            $this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", $e->getMessage());
        } catch (\Exception $exception) {
            error_log("ResetPassword API Exception :: ".$e->getMessage());
            $this->helper->sendResponse(500, "Bad Request", "", "UNABLE_TO_PROCESS", "Something went wrong while saving the new password.");
        }
	}
}
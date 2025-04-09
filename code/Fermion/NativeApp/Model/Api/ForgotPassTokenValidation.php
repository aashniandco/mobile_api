<?php

namespace Fermion\NativeApp\Model\Api;

use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\ForgotPasswordToken\ConfirmCustomerByToken;
use Magento\Customer\Model\Session;
use Fermion\NativeApp\Helper\AppNativeHelper;
use Magento\Framework\App\ObjectManager;

class ForgotPassTokenValidation implements \Fermion\NativeApp\Api\ForgotPassTokenValidationInterface
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
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Customer\Model\ForgotPasswordToken\ConfirmCustomerByToken
     */
    private $confirmByToken;

    /**
     * @var Fermion\NativeApp\Helper\AppNativeHelper
     */
    private $helper;



    public function __construct(
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        ConfirmCustomerByToken $confirmByToken = null,
        AppNativeHelper $helper
    ){
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->session = $customerSession;
        $this->confirmByToken = $confirmByToken
            ?? ObjectManager::getInstance()->get(ConfirmCustomerByToken::class);
        $this->helper = $helper;
    }

    public function validateTokenForgotPassword($resetPasswordToken)
    {
        $requestJson = json_encode($resetPasswordToken);
        $this->helper->validateRequests('/rest/V1/app_forgot_pass_validate_token', $requestJson);

        $respArr = array();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customer = $this->helper->loadCustomerByRpToken($resetPasswordToken);
        if($customer == null || !$customer){
            $this->helper->sendResponse(400, "Failure", "", "UNABLE_TO_PROCESS", "The token has expired or no customer is linked to the token");
        }
        $this->session->setCustomerDataAsLoggedIn($customer);

        $isDirectLink = $resetPasswordToken != '';
        if (!$isDirectLink) {
            $resetPasswordToken = (string)$this->session->getRpToken();
        }
        
        try {
            $this->accountManagement->validateResetPasswordLinkToken(null, $resetPasswordToken);

            $this->confirmByToken->execute($resetPasswordToken);

            if ($isDirectLink) {
                $this->session->setRpToken($resetPasswordToken);
                $this->helper->sendResponse(200, "Success", ["created_at" => date("Y/m/d h:i:sa")]);
            } 
            else {
                $this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", "");
            }
        } 
        catch (\Exception $exception) {
            $this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", $exception->getMessage());
        }

    }


}
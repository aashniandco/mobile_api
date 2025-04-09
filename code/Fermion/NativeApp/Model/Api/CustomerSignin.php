<?php 

namespace Fermion\NativeApp\Model\Api;

use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Fermion\NativeApp\Helper\AppNativeHelper;
use Magento\Framework\Math\Random;
use Fermion\NativeApp\Model\NativeTokens;
use Fermion\NativeApp\Model\ResourceModel\NativeTokens as NativeTokensResource;
use Magento\Store\Model\StoreManagerInterface;

class CustomerSignin implements \Fermion\NativeApp\Api\CustomerSigninInterface
{
	/**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    private $customerAccountManagement;

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

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;


	public function __construct(
		Session $customerSession,
		AccountManagementInterface $customerAccountManagement,
		AppNativeHelper $helper,
		Random $mathRandom,
		NativeTokens $nativeTokens,
		NativeTokensResource $nativeTokensResource,
		StoreManagerInterface $storeManager
	){
		$this->session = $customerSession;
		$this->customerAccountManagement = $customerAccountManagement;
		$this->helper = $helper;
		$this->mathRandom = $mathRandom;
		$this->nativeTokens = $nativeTokens;
		$this->nativeTokensResource = $nativeTokensResource;
		$this->storeManager = $storeManager;
	}
	
	public function signinCustomer($login)
	{
        $requestJson = json_encode($login);
        $this->helper->validateRequests('/rest/V1/app_customer_signin', $requestJson);

		if (!empty($login['username']) && !empty($login['password'])) {
            try {
                $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                if($customer){
                	$token = $this->mathRandom->getUniqueHash();
                	$data = ['token' => $token, 'customer_id' => $customer->getId()];
                	$NativeTokensModel = $this->nativeTokens;
                	$NativeTokensModel->setData($data);
                	$this->nativeTokensResource->save($NativeTokensModel);
                }
                // $this->session->setCustomerDataAsLoggedIn($customer);
                $customer_name = $customer->getFirstname()." ".$customer->getLastname();
                // if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                //     $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                //     $metadata->setPath('/');
                //     $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                // }
                
            } 
            // catch (EmailNotConfirmedException $e) {
            //     $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
            //     $message = __(
            //         'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
            //         $value
            //     );
            // } 
            catch (AuthenticationException $e) {
            	$errCode = "AuthenticationException";
            	$message = "The account sign-in was incorrect or your account is disabled temporarily. Please wait and try again later.";
            } catch (LocalizedException $e) {
            	$errCode = "LocalizedException";
            	$message = $e->getMessage();
            } catch (\Exception $e) {
                // PA DSS violation: throwing or logging an exception here can disclose customer password
                $errCode = "UNABLE_TO_PROCESS";
                $message = $e->getMessage();
            } finally {
                if (isset($message) && isset($errCode)) {
                    $this->helper->sendResponse(400, "Bad Request", "", $errCode, $message);
                }
            }

            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            $redirectUrl = $baseUrl."?token=".$token;
	        $dataArray = ["redirect_url" => $redirectUrl, "user_id" => $customer->getId(), "name" => $customer_name, "email" => $customer->getEmail(), "created_at" => date("Y/m/d h:i:sa")];
	        $this->helper->sendResponse(200, "Success", $dataArray);
        
        } else {
        	$this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", "A login and a password are required.");
        }
	}


	/**
     * Retrieve cookie manager
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
	private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }


    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }
}
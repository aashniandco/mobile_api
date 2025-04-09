<?php
namespace Fermion\Pagelayout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Encryption\EncryptorInterface;
// use Magento\Framework\App\Request\Http;
use Magento\Customer\Model\Session as CustomerSession;

class CustomerCookie implements ObserverInterface{
	
	const userId = 'userId';

	const isLogin = 'isLogin'; 

	/**
     * @var Magento\Framework\Stdlib\CookieManagerInterface;
     */
	private $cookieManager;

	/**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface;
     */
    private $encryptor;

    /**
     * @var \Magento\Framework\App\Request\Http;
     */
    // private $request;

    /**
     * @var Magento\Customer\Model\Session
     */
    private $customerSession;


	public function __construct(
		CookieManagerInterface $cookieManager,
		CookieMetadataFactory $cookieMetadataFactory,
		EncryptorInterface $encryptor,
		// Http $request,
		CustomerSession $customerSession
	){
		$this->cookieManager = $cookieManager;
		$this->cookieMetadataFactory = $cookieMetadataFactory;
		$this->encryptor = $encryptor;
		// $this->request = $request;
		$this->customerSession = $customerSession;
	}

	public function execute(Observer $observer){
		$fullActionName = $observer->getEvent()->getRequest()->getFullActionName();
		$loggedIn = 'false';
		$savedUserIdCookie = $this->cookieManager->getCookie(self::userId);
		$savedIsLoginCookie = $this->cookieManager->getCookie(self::isLogin);
		$customerId = 0;
		if($this->customerSession->isLoggedIn()){
			$loggedIn = 'true';
			$customerId = $this->customerSession->getCustomerId();
		}
		
		if ($savedIsLoginCookie == '' || $savedIsLoginCookie == null || $fullActionName == 'customer_account_logout' || $fullActionName == 'customer_account_createpost'  || $fullActionName == 'customer_account_loginPost'){
			//error_log(self::isLogin."---setcustomcookie---islogin-------".$loggedIn);
        	//$this->setCustomCookie(self::isLogin, $loggedIn);
        	setcookie(self::isLogin, $loggedIn, 0, "/");
        }

        if(($fullActionName == 'customer_account_logout' || ($savedUserIdCookie != '' && $savedUserIdCookie != null)) && $customerId == 0){
        	$this->deleteCookie(self::userId);    
        }elseif(($fullActionName == 'customer_account_loginPost' || $fullActionName == 'customer_account_createpost' || $savedUserIdCookie == '' || $savedUserIdCookie == null) && $customerId != 0){
			$customerIdEnc = hash_hmac('sha256', $customerId, mt_rand() . microtime());
        	//$this->setCustomCookie(self::userId, $customerIdEnc);
        	setcookie(self::userId, $customerIdEnc, 0, "/");
        }
    }

	public function setCustomCookie($name,$value)
    {
    	$publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $publicCookieMetadata->setDurationOneYear();
        $publicCookieMetadata->setPath('/');
        $publicCookieMetadata->setHttpOnly(false);

        $this->cookieManager->setPublicCookie(
            $name,
            $value,
            $publicCookieMetadata
        );
        
    }


    public function deleteCookie($name)
    {

        if ($this->cookieManager->getCookie($name)) {
        	//error_log("--------deleted cookie name --------".$name);
            $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $metadata->setPath('/');

            return $this->cookieManager->deleteCookie(
                $name,$metadata);
        }
    }
}
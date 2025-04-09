<?php

namespace Mageprince\BuyNow\Controller\Login;

use Magento\Customer\Controller\Account\LoginPost;
use Magento\Quote\Model\QuoteRepository;
use Magento\Customer\Api\CustomerRepositoryInterface ;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;

class LoginReloadQuote extends LoginPost
{
	/**
     * @var Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;
    

	public function __construct(
        Context $context,
        AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect,
		QuoteRepository $quoteRepository,
		CustomerRepositoryInterface $customerRepository,
		CustomerSession $customerSession,
		CheckoutSession $checkoutSession,
		CustomerCart $cart
	){
        parent::__construct(
        	$context,
        	$customerSession,
        	$customerAccountManagement,
        	$customerHelperData,
        	$formKeyValidator,
        	$accountRedirect
        );
		$this->quoteRepository = $quoteRepository;
		$this->customerRepository = $customerRepository;
		$this->checkoutSession = $checkoutSession;
		$this->cart = $cart;
	}

	/**
     * Get scope config
     *
     * @return ScopeConfigInterface
     * @deprecated 100.0.10
     */
    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
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

    /**
     * Login Controller Override
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $isAjax = $this->getRequest()->getPost('isAjax') ? $this->getRequest()->getPost('isAjax') : 0;
        $returnArr = ['success' => false];
        if(!$isAjax){
            if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
                /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            }
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');

            if($isAjax){
                $token = $this->getRequest()->getPost('token');

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $nativeTokenFactory = $objectManager->create('Fermion\NativeApp\Model\ResourceModel\NativeTokens\Collection\NativeTokenFactory');

                $nativeTokensCollection = $nativeTokenFactory->create();
                $nativeTokensCollection->addTokenFilter($token);

                $customer_id = null;
                foreach ($nativeTokensCollection as $item) {
                    $customer_id = $item->getData("customer_id");
                    break;
                }

                $customer = $this->customerRepository->getById($customer_id);

                $login['username'] = $customer->getEmail();
                $login['password'] = $token;
            }

            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    
                    $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                    
                    $this->session->setCustomerDataAsLoggedIn($customer);
                    $returnArr['success'] = true;
                    
                    if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                        $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                        $metadata->setPath('/');
                        $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                    }
                    
                    $redirectUrl = $this->accountRedirect->getRedirectCookie();
                    
                    $current_quote = $this->checkoutSession->getQuote();
                    if($current_quote->getId())
                    {  
                        $original_quote_id = $current_quote->getData('quote_identity');
                        if($original_quote_id != null && $original_quote_id != '' && $original_quote_id != 0){
                            $current_quote->setIsActive(false);
                            $this->quoteRepository->save($current_quote);
                            $quote = $this->quoteRepository->get($original_quote_id);
                            $quote->setIsActive(true);
                            $quote->collectTotals();
                            $quote->save();
                            $this->quoteRepository->save($quote);
                            $this->checkoutSession->setQuoteId($quote->getId());
                            $this->cart->setQuote($quote);
                        }        
                    }

                    if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                        $this->accountRedirect->clearRedirectCookie();
                        $resultRedirect = $this->resultRedirectFactory->create();
                        // URL is checked to be internal in $this->_redirect->success()
                        $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
                        if($isAjax){
                            echo json_encode($returnArr);
                        }
                        else{
                            return $resultRedirect;
                        }
                    }
                    
                    
                    

                } catch (EmailNotConfirmedException $e) {
                    $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $message = __(
                        'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                } catch (AuthenticationException $e) {
                    $message = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    );
                } catch (LocalizedException $e) {
                    $message = $e->getMessage();
                } catch (\Exception $e) {
                    // PA DSS violation: throwing or logging an exception here can disclose customer password
                    $message = $e->getMessage();
                    $this->messageManager->addErrorMessage(
                        __($message)
                    );
                } finally {
                    if (isset($message)) {
                        $this->messageManager->addErrorMessage($message);
                        $this->session->setUsername($login['username']);
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('A login and a password are required.'));
            }
        }

        if($isAjax){
            echo json_encode($returnArr);
        }
        else{
            return $this->accountRedirect->getRedirect();
        }
    }
}
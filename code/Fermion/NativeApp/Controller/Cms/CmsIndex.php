<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fermion\NativeApp\Controller\Cms;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\Page as ResultPage;
use Magento\Cms\Helper\Page;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Action\Action;
use Fermion\NativeApp\Model\ResourceModel\NativeTokens\Collection\NativeTokenFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Cms\Controller\Index\Index;


/**
 * Home page. Needs to be accessible by POST because of the store switching.
 */
class CmsIndex extends Index
{
    /**
     * @var Fermion\NativeApp\Model\ResourceModel\NativeTokens\Collection\NativeTokenFactory
     */
    private $nativeTokenFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

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
        ForwardFactory $resultForwardFactory,
        ScopeConfigInterface $scopeConfig = null,
        Page $page = null,
        NativeTokenFactory $nativeTokenFactory,
        CustomerRepositoryInterface $customerRepository,
        Session $session,
        AccountRedirect $accountRedirect
    ) {
        $this->nativeTokenFactory = $nativeTokenFactory;
        $this->customerRepository = $customerRepository;
        $this->session = $session;
        $this->accountRedirect = $accountRedirect;
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->page = $page ?: ObjectManager::getInstance()->get(Page::class);
        parent::__construct(
            $context,
            $resultForwardFactory,
            $this->scopeConfig,
            $this->page
        );
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

    public function setCookie($cookieName, $cookieValue, $duration)
    {
        $cookieMetadata = $this->getCookieMetadataFactory()->createPublicCookieMetadata();
        $cookieMetadata->setDuration($duration);
        $cookieMetadata->setPath('/');
        $cookieMetadata->setHttpOnly(false);

        $this->getCookieManager()->setPublicCookie(
            $cookieName,
            $cookieValue,
            $cookieMetadata
        );
    }

    /**
     * Renders CMS Home page
     *
     * @param string|null $coreRoute
     *
     * @return bool|ResponseInterface|Forward|ResultInterface|ResultPage
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($coreRoute = null)
    {
        // if(isset($_GET['token']))
        // {
        //     $token = $_GET['token'];

        //     try{
        //         $nativeTokensCollection = $this->nativeTokenFactory->create();
        //         $nativeTokensCollection->addTokenFilter($token);

        //         $customer_id = null;
        //         foreach ($nativeTokensCollection as $item) {
        //             $customer_id = $item->getData("customer_id");
        //             break;
        //         }
                
        //         if(isset($customer_id)){
        //             if(!$this->session->isLoggedIn()){
        //                 if ($this->getCookieManager()->getCookie('PHPSESSID')) {
        //                     $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
        //                     $metadata->setPath('/');
        //                     $this->getCookieManager()->deleteCookie('PHPSESSID', $metadata);
        //                 }
        //             }  
        //             $customer = $this->customerRepository->getById($customer_id);
        //             $this->session->setCustomerDataAsLoggedIn($customer);
        //             $this->session->regenerateId();

        //             if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
        //                 $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
        //                 $metadata->setPath('/');
        //                 $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
        //             }
        //             $redirectUrl = $this->accountRedirect->getRedirectCookie();
        //             if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
        //                 $this->accountRedirect->clearRedirectCookie();
        //             }
        //         }
        //     }
        //     catch(\Exception $e){
        //         error_log("NativeAppSession observerHeader :: ".$e->getMessage());
        //     }
        // }
        // if(isset($_GET['agent'])){
        //     $agent = $_GET['agent'];
        //     $this->setCookie('aashni_app', $agent, 86400);
        // }
        $pageId = $this->scopeConfig->getValue(Page::XML_PATH_HOME_PAGE, ScopeInterface::SCOPE_STORE);
        $resultPage = $this->page->prepareResultPage($this, $pageId);
        if (!$resultPage) {
            /** @var Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('defaultIndex');
            return $resultForward;
        }
        return $resultPage;
    }
}

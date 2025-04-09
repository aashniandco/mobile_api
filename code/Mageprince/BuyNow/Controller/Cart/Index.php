<?php 

namespace Mageprince\BuyNow\Controller\Cart;

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Customer\Api\CustomerRepositoryInterface ;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Mageprince\BuyNow\Helper\Data as BuyNowHelper;
use Mageprince\BuyNow\Model\BuyNowData;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;


class Index extends Action
{
	/**
     * @var Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    
    /**
     * @var Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;
    
    /**
     * @var Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
	protected $storeManager;

	/**
     * @var Magento\Catalog\Api\ProductRepositoryInterface
     */
	protected $productRepository;

	/**
     * @var Mageprince\BuyNow\Helper\Data
     */
	protected $buyNowHelper;

    /**
     * @var Mageprince\BuyNow\Model\BuyNowData
     */
    protected $buyNowData;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory CookieMetadataFactory
     */
    private $cookieMetadataFactory;

	public function __construct(
        Context $context,
		QuoteFactory $quoteFactory,
		QuoteRepository $quoteRepository,
		CustomerRepositoryInterface $customerRepository,
		CustomerSession $customerSession,
		CheckoutSession $checkoutSession,
		CustomerCart $cart,
		StoreManagerInterface $storeManager,
		ProductRepositoryInterface $productRepository,
		BuyNowHelper $buyNowHelper,
        BuyNowData $buyNowData,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
	){
        parent::__construct($context);
		$this->quoteFactory = $quoteFactory;
		$this->quoteRepository = $quoteRepository;
		$this->customerRepository = $customerRepository;
		$this->customerSession = $customerSession;
		$this->checkoutSession = $checkoutSession;
		$this->cart = $cart;
		$this->storeManager = $storeManager;
		$this->productRepository = $productRepository;
		$this->buyNowHelper = $buyNowHelper;
        $this->buyNowData = $buyNowData;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
	}

    /**
     * Checkout page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
    	$params = $this->getRequest()->getParams();

        $this->buyNowData->setBuyNowFlag(true);
    	$currentStoreId = $this->storeManager->getStore()->getStoreId();

        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
        }
        else{
            $customerId = null;
        }
        
        $product = $this->_initProduct();
        $cartProducts = $this->buyNowHelper->keepCartProducts();
        $oldQuoteId = '';
        if(!$cartProducts){
            try{
                $current_quote = $this->checkoutSession->getQuote();

                if($current_quote->getId()){
                    $this->buyNowData->setOriginalQuoteId($current_quote->getId());
                    //$this->setCustomCookie('buynow',$current_quote->getId());
                    $oldQuoteId = $current_quote->getId();
                    $current_quote->setIsActive(false);
                    $this->quoteRepository->save($current_quote);   
                }

                $quote = $this->quoteFactory->create();
                $quote->setStoreId($currentStoreId);

                if ($customerId != null) {
                    $customer = $this->customerRepository->getById($customerId);
                    $quote->setCustomer($customer);
                    $quote->setData('quote_identity', $current_quote->getId());
                }

                $quote->save();
                $this->checkoutSession->setQuoteId($quote->getId());
                $this->cart->setQuote($quote);
                $this->cart->addProduct($product, $params);
            }
            catch(\Exception $e) {
                // Handle any exceptions that may occur during the process
                error_log("\n\n buynow :: Inside Catch Block - ".$e);
                return null;
            }
        }
        else{
            $this->cart->addProduct($product, $params);
        }

        $this->cart->save();
        $quote->collectTotals();
        $quote->save();

        $this->_eventManager->dispatch(
            'checkout_cart_add_product_complete',
            ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
        );
        
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $redirect = $baseUrl.'checkout/';
        echo json_encode(array('backUrl' => $redirect,'quote_id'=> $oldQuoteId));die;
    }

    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->storeManager->getStore()->getStoreId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }


    /** Set Custom Cookie using Magento 2 */
    public function setCustomCookie($name,$value)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $sessionManager = $objectManager->create('Magento\Framework\Session\SessionManagerInterface');
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        //$publicCookieMetadata->setDurationOneYear();
        // $publicCookieMetadata->setPath('/');
        
        $publicCookieMetadata->setPath($sessionManager->getCookiePath());
        $publicCookieMetadata->setDomain($sessionManager->getCookieDomain());
        $publicCookieMetadata->setDuration(86400);
        $publicCookieMetadata->setHttpOnly(true);
        $publicCookieMetadata->setSecure(true);
        error_log("mhtest::domain:".$sessionManager->getCookieDomain()."|".$sessionManager->getCookiePath());
        $this->cookieManager->setPublicCookie(
            $name,
            $value,
            $publicCookieMetadata
        );
        
    }
}
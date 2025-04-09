<?php

namespace Mageprince\BuyNow\Controller\Onepage;

use Magento\Checkout\Controller\Onepage\Success as SuccessController;
use Mageprince\BuyNow\Model\BuyNowData;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Success extends SuccessController implements HttpGetActionInterface
{

	/**
     * @var Mageprince\BuyNow\Model\BuyNowData
     */
    protected $buyNowData;

	/**
     * @var Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var Magento\Checkout\Model\Cart
     */
    protected $cart;

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
		BuyNowData $buyNowData,
		CheckoutSession $checkoutSession,
		CustomerCart $cart,
		\Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
		\Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
		\Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
	){
		parent::__construct(
			$context,
			$customerSession,
            $customerRepository,
            $accountManagement,
			$coreRegistry,
			$translateInline,
			$formKeyValidator,
			$scopeConfig,
			$layoutFactory,
			$quoteRepository,
			$resultPageFactory,
			$resultLayoutFactory,
			$resultRawFactory,
			$resultJsonFactory
		);
		$this->buyNowData = $buyNowData;
		$this->checkoutSession = $checkoutSession;
		$this->cart = $cart;
		$this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
	}

	public function execute()
	{
		error_log("buynow :: Success Controller override");
		$session = $this->getOnepage()->getCheckout();
        if (!$this->_objectManager->get(\Magento\Checkout\Model\Session\SuccessValidator::class)->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }
        $session->clearQuote();

        // Custom code to restore original quote
		if($this->cookieManager->getCookie('buynow')){
			$quoteId = $this->cookieManager->getCookie('buynow');
			 if($quoteId != null || $quoteId != '') {
			 	try{
			 		$quote = $this->quoteRepository->get($quoteId);
                    $quote->setIsActive(true);
                    $this->quoteRepository->save($quote);
                    $this->checkoutSession->setQuoteId($quoteId);
                    $this->cart->setQuote($quote);
                    $this->cart->save();
                    $quote->collectTotals();
                    $quote->save();
                    $this->buyNowData->setBuyNowFlag(false);
                    $this->deleteCookie('buynow');
			 	}
                catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                	error_log("buynow :: Success Page Controller error -> ".$e);
                    $quote = null; // Quote with the provided ID was not found
                }
			 }
		}

		$resultPage = $this->resultPageFactory->create();
        $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            [
                'order_ids' => [$session->getLastOrderId()],
                'order' => $session->getLastRealOrder()
            ]
        );
        return $resultPage;
	}

	/** Delete custom Cookie */
    public function deleteCookie($name)
    {
        if ($this->cookieManager->getCookie($name)) {
            $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $metadata->setPath('/');
            error_log("-----buynow delete cookie on checkout---------");
            return $this->cookieManager->deleteCookie(
                $name,$metadata);
        }
    }
}
<?php 

namespace Fermion\Pagelayout\Controller\Account;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\QuoteFactory;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Store\Model\StoreManagerInterface;

class DeleteAction extends Action
{
	/**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
	protected $storeManager;

	public function __construct(
		Context $context,
		CustomerRepositoryInterface $customerRepository,
		Registry $registry,
		CheckoutSession $checkoutSession,
		QuoteRepository $quoteRepository,
		QuoteFactory $quoteFactory,
		CustomerCart $cart,
		StoreManagerInterface $storeManager
	){
		parent::__construct($context);
		$this->_customerRepository = $customerRepository;
		$this->registry = $registry;
		$this->checkoutSession = $checkoutSession;
		$this->quoteRepository = $quoteRepository;
		$this->quoteFactory = $quoteFactory;
		$this->cart = $cart;
		$this->storeManager = $storeManager;
	}
	public function execute()
	{
		$this->registry->register('isSecureArea', true);
		$is_ajax = $this->getRequest()->getParam('is_ajax');
		$customer_id = $this->getRequest()->getParam('customer_id');
		if (!empty($customer_id)) {
            try {
            	$currentStoreId = $this->storeManager->getStore()->getStoreId();

            	$current_quote = $this->checkoutSession->getQuote();
            	$current_quote->setIsActive(false);
            	$this->quoteRepository->save($current_quote);
            	$quote = $this->quoteFactory->create();
            	$quote->setStoreId($currentStoreId);
                $quote->save();
                $this->checkoutSession->setQuoteId($quote->getId());
                $this->cart->setQuote($quote);

                $this->_customerRepository->deleteById($customer_id);
                $this->messageManager->addSuccessMessage(__('You deleted the account successfully! Redirecting...'));
                
                echo json_encode([
                	'status' => true,
                	'msg' => 'success'
            	]);
                die;
            } catch (\Exception $exception) {
            	$this->messageManager->addSuccessMessage(__("Could not delete your account!"));
                
                echo json_encode([
                	'status' => false,
                	'msg' => $exception->getMessage()
            	]);
                die;
            }
        }
        else{
        	echo json_encode([
            	'status' => false,
            	'msg' => 'id not found'
        	]);
            die;
        }
	}
}
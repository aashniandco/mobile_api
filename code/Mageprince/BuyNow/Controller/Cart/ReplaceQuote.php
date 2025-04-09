<?php 

namespace Mageprince\BuyNow\Controller\Cart;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;


class ReplaceQuote extends Action
{
    
   /**
     * @var Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $configurableProduct;

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
        CheckoutSession $checkoutSession,
        CustomerCart $cart,
        QuoteRepository $quoteRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository,
        ProductRepository $productRepository,
        StoreManagerInterface $storeManager,
        Configurable $configurableProduct,
         \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
    ){
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->quoteRepository = $quoteRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->configurableProduct = $configurableProduct;
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
        $responseArr = array();
        $responseArr['error'] = 0;
        $params = $this->getRequest()->getParams();
        $quoteId = isset($params['quote-id']) ? $params['quote-id'] : '';
        $childsku = '';
            if($quoteId != null || $quoteId != ''){
                try 
                {
                    $new_quote = $this->checkoutSession->getQuote();
                    if($new_quote !== null){
                        $items  = $this->cart->getQuote()->getAllVisibleItems();
                        foreach ($items as $item){
                            $childsku = $item->getSku();
                            $parentProductId =$item->getProductId();
                            continue;
                        }
                        if($childsku != ''){
                            $product = $this->productRepository->get($childsku);
                            $productId = $product->getId();
                            // $parentIds = $this->configurableProduct->getParentIdsByChild($productId);
                            // $parentProductId = $parentIds[0];
                            $storeId = $this->storeManager->getStore()->getStoreId();
                            $parentProduct = $this->productRepository->getById($parentProductId, false, $storeId);
                            $new_quote->setIsActive(false);
                            $this->quoteRepository->save($new_quote);
                        }
                        
                    }
                    
                    $quote = $this->_objectManager->create('Magento\Quote\Model\QuoteFactory')->create()->load($quoteId);
                    $quote->setIsActive(1);
                    $this->cart->setQuote($quote);
                    $this->checkoutSession->setQuoteId($quoteId);
                    $p_type = $parentProduct->getTypeId();
                    if($new_quote !== null && $childsku != '' && $p_type != 'mageworx_giftcards'){
                        $buyNowItemParam = $this->setAddParams($quote, $product, $productId, $parentProduct, $parentProductId);
                        if(!empty($buyNowItemParam)){

                            $this->cart->addProduct($parentProduct, $buyNowItemParam);
                        }else{
                            $quote->save();
                        }
                    }
                    $this->cart->save();
                    //$this->deleteCookie('buynow');  
                    //error_log("deletecookie--");                                
                }
                catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $quote = null; // Quote with the provided ID was not found
                    $responseArr['error'] = 1;
                }
            }
        
            echo json_encode($responseArr);die;

    }


    /** Delete custom Cookie */
    public function deleteCookie($name)
    {
        if ($this->cookieManager->getCookie($name)) {
            $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $metadata->setPath('/');

            return $this->cookieManager->deleteCookie(
                $name,$metadata);
        }
    }
    
    public function setAddParams($quote, $product, $productId, $parentProduct, $parentProductId){
        error_log("buynow :: addProductInOldQuote step 1");
        $items = $quote->getAllItems();
        $quantityIsAvailable = false;
        $productAlreadyInCart = false;
        $params = array();
        foreach ($items as $item) {
            if ($item->getProductId() == $productId) {
                $productAlreadyInCart = true;
                return $params;
                //break;
            }
        }
        $stockItem = $product->getExtensionAttributes()->getStockItem();
        if($stockItem->getQty() >= 1) {
            error_log("buynow :: addProductInOldQuote step 2 ".$stockItem->getQty());
            $quantityIsAvailable = true; 
        }
        if(!$productAlreadyInCart && $quantityIsAvailable){
            error_log("buynow :: addProductInOldQuote step 3");
           
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $formKey = $this->_objectManager->create('\Magento\Framework\Data\Form\FormKey')->getFormKey();
            $params['form_key'] = $formKey;
            $params['product'] = $parentProductId;
            $options = [];
            $productAttributeOptions = $parentProduct->getTypeInstance(true)->getConfigurableAttributesAsArray($parentProduct);
            foreach($productAttributeOptions as $option){
                $options[$option['attribute_id']] = (int) $product->getData($option['attribute_code']);
            }
            $params['super_attribute'] = $options; 
            $params['qty'] = 1;
           // return $params;
        }
        return $params;
    }

   
}
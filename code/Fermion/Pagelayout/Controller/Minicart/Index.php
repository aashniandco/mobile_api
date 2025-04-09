<?php
namespace Fermion\Pagelayout\Controller\Minicart;
class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_wishlistRepository;
    protected $_productRepository;
    protected $_request;
    protected $_storeManager;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository)
    {
        $this->_pageFactory = $pageFactory;
        $this->_wishlistRepository= $wishlistRepository;
        $this->_productRepository = $productRepository;
        $this->_request=$request;
        $this->_storeManager = $storeManager;
        return parent::__construct($context);
    }
    public function execute(){
        $data =$_POST;
        $response_arr['status']='error';
        $ajax=isset($data['isAjax']) ? $data['isAjax'] : '';
        $isLoggedIn = 0;
        if($ajax){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerSession = $objectManager->create('Magento\Customer\Model\Session');
            $cart = $objectManager->create('\Magento\Checkout\Model\Session');
            $_productRepository = $objectManager->create('\Magento\Catalog\Model\ProductRepository');
            $imageHelper = $objectManager->create('\Magento\Catalog\Helper\Image');
            $customerId = $customerSession->getId();
            $quote = $cart->getQuote();
            $subtotal = $quote->getSubtotal();
            $totalItems = $cart->getQuote()->getItemsCount();
            $items = $cart->getQuote()->getAllItems();
            $visibleItem = $quote->getAllVisibleItems();
            $isLoggedIn = $customerSession->isLoggedIn();  
            $formKey = $objectManager->create('\Magento\Framework\Data\Form\FormKey');
            $carthtml = ''; 
            $subtotalhtml = '';
            $currencysymbol = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
            $currency = $currencysymbol->getStore()->getCurrentCurrency();
            $getCurrencyCode = $currency->getCurrencyCode();
            $getCurrencySymbol = $currency->getCurrencySymbol();
            $response_arr['data'] = array();
            foreach($visibleItem as $key=>$item){
                $product_id = $item->getProductId();
                $configurableProductObject = $_productRepository->get($item->getData('sku'));
                $configurableProductSizeObject = $configurableProductObject->getResource()->getAttribute('size');
                $configurableSize = $configurableProductSizeObject->getFrontend()->getValue($configurableProductObject);
                $product = $_productRepository->getById($item->getProductId());
                $designer = $product->getResource()->getAttribute('designer');
                $designerName = $designer->getFrontend()->getValue($product);
                $productName = $product->getName();
                $productUrl = $product->getProductUrl();
                $itemQuantity = $item->getQty(); 
                $itemId = $item->getId(); 
                $actualPrice = $item->getProduct()->getPrice();
                $specialPrice = $item->getPrice();
                $productimages = $product->getMediaGalleryImages();
                foreach($productimages as $_image){
                    $productImageUrl = $imageHelper->init($product, 'image')
                                    ->setImageFile($_image->getFile())
                                    ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                                    ->getUrl();
                                }
                                $response_arr['data'][$key]['productImageUrl']= $productImageUrl;
                                $response_arr['data'][$key]['productUrl']= $productUrl;
                                $response_arr['data'][$key]['productName']=  $productName;
                                $response_arr['data'][$key]['designername']=  $designerName;
                                $response_arr['data'][$key]['size']=  $configurableSize;
                                $response_arr['data'][$key]['qty']=  $itemQuantity;
                                //$response_arr['data'][$key]['item_id']=  $item_id;
                                $response_arr['data'][$key]['item_id']=  $itemId;
                                $response_arr['data'][$key]['special_price']=  $specialPrice;
                                $response_arr['data'][$key]['actual_price']=  $actualPrice;
                                $response_arr['data'][$key]['total_count']=  $totalItems;
                                $response_arr['data'][$key]['product_id']= $item->getProductId();
                                $response_arr['data'][$key]['formKey']=  $formKey->getFormKey();
                                $response_arr['status']='success';
                            }
                            $response_arr['isLoggedIn'] = $isLoggedIn;
                            $response_arr['subtotal']= $subtotal;
                            echo json_encode($response_arr);die;
                        }
                    }
                }

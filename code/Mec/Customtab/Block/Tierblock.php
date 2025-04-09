<?php
namespace Mec\Customtab\Block;
use Magento\Framework\View\Element\Template;
#use Magento\Catalog\Product\View;
/**
* Tierblock block
*/
class Tierblock extends \Magento\Framework\View\Element\Template
{
	protected $_registry;
	protected $_filterProvider;
    	protected $_storeManager;
    	protected $_blockFactory;
	protected $_filterTemplate;
	protected $_productloader;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
	\Magento\Framework\Registry $registry,
	\Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\BlockFactory $blockFactory,
	\Magento\Framework\Filter\Template $filterTemplate,
	\Magento\Catalog\Model\ProductFactory $_productloader,
        array $data = []
    )
    {
	$this->_registry = $registry;
    	$this->_filterProvider = $filterProvider;
    	$this->_storeManager = $storeManager;
    	$this->_blockFactory = $blockFactory;
	$this->_filterTemplate = $filterTemplate;
	$this->_productloader = $_productloader;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }
		
    public function getUpsellBlock()
    {

	$upSellProducts = $this->getCurrentProduct()->getUpSellProducts();
	if (!empty($upSellProducts)) {
		foreach ($upSellProducts as $upSellProduct) {
            		$upsellFound = $upSellProduct->getId();
			break;	
        	}
		$upsellLoaded = $this->_productloader->create()->load($upsellFound);
		$priceDiffer = $this->_getPriceDiffer($upsellLoaded);
		if ($priceDiffer <= 0) {
			//return;
			
		}
        $variables = array(
            'price_differ'  => number_format($priceDiffer, 2),
            'item_sku'      => $upsellLoaded->getSku(),
            'product_link' => $upsellLoaded->getProductUrl()
        );
		$callBlock = $this->getCurrentProduct()->getAttributeText('select_upsell_banner');
		$storeId = $this->_storeManager->getStore()->getId();
		/** @var \Magento\Cms\Model\Block $block */
            	$block = $this->_blockFactory->create();
		$block->setStoreId($storeId)->load($callBlock);
		$this->_filterProvider->getPageFilter()->setVariables($variables);
		$html = $this->_filterProvider->getPageFilter()->filter($block->getContent());
        	return $html;
	}
	else{
		return ;
	}
    }

    public function _getPriceDiffer($upSellProduct)
    {
	#for getting lowest price of upsell product
	if ($upSellProduct->getTypeId() == 'grouped') {
            $_upsellItemPrice = $this->_getLowestPrice($upSellProduct);
        } else {
            $_upsellItemPrice = $upSellProduct->getFinalPrice();
	}
	#for getting lowest price of current product
	$currentProduct = $this->getCurrentProduct();
	if ($currentProduct->getTypeId() == 'grouped' ){
            $_itemPrice = $this->_getLowestPrice($currentProduct);
        } else {
            $_itemPrice = $currentProduct->getFinalPrice();
        }
	return $_itemPrice - $_upsellItemPrice;

    }

    protected function _getLowestPrice($_item)
    {
        $_associatedProducts = $_item->getTypeInstance(true)->getAssociatedProducts($_item);
        $_lowestPrice = 0;
        foreach ($_associatedProducts as $_item) {
            if ($_lowestPrice == 0) {
                if ($_item->getQty() > 1) {
                    $_lowestPrice = $_item->getFinalPrice() / $_item->getQty();
                } else {
                    $_lowestPrice = $_item->getFinalPrice();
                }
            } elseif ($_item->getQty() > 1) {
                $_itemPrice = $_item->getFinalPrice() / $_item->getQty();
                $_lowestPrice = ($_itemPrice < $_lowestPrice ? $_itemPrice : $_lowestPrice);
            } else {
                if ($_item->getTypeId() == 'bundle') {
                    $bundleOptionsCollection = $_item->getTypeInstance(true)->getSelectionsCollection(
                        $_item->getTypeInstance(true)->getOptionsIds($_item), $_item
                    );
                    $bundleOptionsCount = 0;
                    foreach ($bundleOptionsCollection as $option) {
                        $bundleOptionsCount += $option->getSelectionQty();
                    }
                    $_itemPrice = $_item->getFinalPrice() / $bundleOptionsCount;
                    $_lowestPrice = ($_itemPrice < $_lowestPrice ? $_itemPrice : $_lowestPrice);
                } else {
                    $_lowestPrice = ($_item->getFinalPrice() < $_lowestPrice ? $_item->getFinalPrice() : $_lowestPrice);
                }
            }
        }

        return $_lowestPrice;
    }


}

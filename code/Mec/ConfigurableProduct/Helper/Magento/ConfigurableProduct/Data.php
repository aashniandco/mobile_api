<?php

namespace Mec\ConfigurableProduct\Helper\Magento\ConfigurableProduct;
use Magento\Catalog\Model\Product;

class Data extends \Magento\ConfigurableProduct\Helper\Data
{
    /**
     * Catalog Image Helper
     *
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    protected $_productloader;
    protected $stockRegistry;
    protected $_storeManager;

    /**
     * @param \Magento\Catalog\Helper\Image $imageHelper
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->imageHelper = $imageHelper;
        $this->stockRegistry = $stockRegistry;
        $this->_productloader = $_productloader;
        $this->_storeManager = $storeManager;
        parent::__construct($imageHelper);
    }

    /**
     * Get Options for Configurable Product Options
     *
     * @param \Magento\Catalog\Model\Product $currentProduct
     * @param array $allowedProducts
     * @return array
     */
    public function getOptions($currentProduct, $allowedProducts)
    {

        $options = [];
        foreach ($allowedProducts as $product) {
            $productId = $product->getId();

            $websiteId = $this->getCurrentWebsiteId();
#            if ($websiteId == 1) {
                $product = $this->getLoadProduct($productId);
                $stockitem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
                if($stockitem->getQty() <= 0 || $stockitem->getIsInStock() != 1) continue;
#            }

            $images = $this->getGalleryImages($product);
            if ($images) {
                foreach ($images as $image) {
                    $options['images'][$productId][] =
                        [
                            'thumb' => $image->getData('small_image_url'),
                            'img' => $image->getData('medium_image_url'),
                            'full' => $image->getData('large_image_url'),
                            'caption' => $image->getLabel(),
                            'position' => $image->getPosition(),
                            'isMain' => $image->getFile() == $product->getImage(),
                        ];
                }
            }
            foreach ($this->getAllowAttributes($currentProduct) as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());

                $options[$productAttributeId][$attributeValue][] = $productId;
                $options['index'][$productId][$productAttributeId] = $attributeValue;
            }
        }
        return $options;
    }

    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }

    public function getCurrentWebsiteId(){
        return $this->_storeManager->getStore()->getWebsiteId();
    }

}

	

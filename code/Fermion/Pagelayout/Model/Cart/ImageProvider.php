<?php

namespace Fermion\Pagelayout\Model\Cart;

use Magento\Checkout\Model\Cart\ImageProvider as VendorImageProvider;
use Magento\Checkout\CustomerData\DefaultItem;
use Magento\Framework\App\ObjectManager;

class ImageProvider extends VendorImageProvider{

	public function __construct(
        \Magento\Quote\Api\CartItemRepositoryInterface $itemRepository,
        \Magento\Checkout\CustomerData\ItemPoolInterface $itemPool,
        \Magento\Checkout\CustomerData\DefaultItem $customerDataItem = null
    ) {
        $this->itemRepository = $itemRepository;
        $this->itemPool = $itemPool;
        $this->customerDataItem = $customerDataItem ?: ObjectManager::getInstance()->get(DefaultItem::class);
    }

    public function getImages($cartId)
    {
        $itemData = [];

        /** @see code/Magento/Catalog/Helper/Product.php */
        $items = $this->itemRepository->getList($cartId);
        /** @var \Magento\Quote\Model\Quote\Item $cartItem */
       
        foreach ($items as $cartItem) {
            $allData = $this->customerDataItem->getItemData($cartItem);
            if($cartItem->getProductType() == 'mageworx_giftcards'){

                $orderOptions = $cartItem->getProduct()->getTypeInstance(true)->getOrderOptions($cartItem->getProduct());
                error_log("image check :: ".json_encode($orderOptions));
                $storeManager = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Store\Model\StoreManagerInterface::class);
                $mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $imageBaseUrl = $mediaUrl . 'catalog/product';
                $images= isset($orderOptions['info_buyRequest']['image_url']) ? $orderOptions['info_buyRequest']['image_url'] :'';
                $allData['product_image']['src'] = $imageBaseUrl.$images;
            }
            
            $itemData[$cartItem->getItemId()] = $allData['product_image'];
        }
        return $itemData;
    
    }
}
<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Mpanel\Controller\Index;

class Loadmore extends \Magento\Framework\App\Action\Action
{
	/**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

	public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Element\Context $urlContext)     
	{
		$this->_urlBuilder = $urlContext->getUrlBuilder();

		parent::__construct($context);
	}
	
    public function execute()
    {
		$this->_view->loadLayout();
		$type = $this->getRequest()->getParam('type');
		$p = $this->getRequest()->getParam('p');
		$nextPage = $p+1;
		$limit = $this->getRequest()->getParam('limit');
		$perrow = $this->getRequest()->getParam('perrow');
		$ratio = $this->getRequest()->getParam('ratio');
		$col = $this->getRequest()->getParam('col');
		$blockId = $this->getRequest()->getParam('block_id');

		
		switch ($type) {
			// New Product 
			case 'new-products-category':
				$tabs = $this->getRequest()->getParam('tabs');
				$category = $categoryId = $this->getRequest()->getParam('category');
				$result['element_id'] = 'new-products'.$blockId;
				if($tabs){
					$category = $this->getModel('Magento\Catalog\Model\Category')->load($category);
					$categoryId = $category->getId();
					$result['element_id'] = 'new-products'.$categoryId.$blockId;
				}
				
				$htmlPrev = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\NewProducts')
					->setTabs($tabs)
					->setAdditionalData($category)
					->setLimit($limit)
					->setPerRow($perrow)
					->setPrevPage(true)
					->setRatio($ratio)
					->setCol($col)
					->setBlockId($blockId)
					->setCurPage($p-1)
					->setTemplate('products/new/grid/loadmore_content.phtml')
					->toHtml();
					
				$html = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\NewProducts')
					->setTabs($tabs)
					->setAdditionalData($category)
					->setLimit($limit)
					->setPerRow($perrow)
					->setRatio($ratio)
					->setCol($col)
					->setBlockId($blockId)
					->setCurPage($p)
					->setTemplate('products/new/grid/loadmore_content.phtml')
					->toHtml();
					
				$htmlNext = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\NewProducts')
					->setTabs($tabs)
					->setAdditionalData($category)
					->setLimit($limit)
					->setRatio($ratio)
					->setPerRow($perrow)
					->setNextPage(true)
					->setCol($col)
					->setBlockId($blockId)
					->setCurPage($nextPage)
					->setTemplate('products/new/grid/loadmore_content.phtml')
					->toHtml();
				
				if($html != $htmlNext){
					$result['url'] = $this->_urlBuilder->getUrl('mpanel/index/loadmore', ['type'=>$type, 'perrow'=>$perrow, 'category'=>$categoryId, 'limit'=>$limit, 'p'=>$nextPage, 'ratio'=>$ratio, 'col'=>$col, 'block_id'=>$blockId, 'tabs'=>$tabs]);
					$result['next'] = $htmlNext;
				}else{
					if($htmlPrev == $html){
						$html = '';
					}
				}
				
				break;
			
			// Attribute Products
			case 'attribute-products-category':
				$tabs = $this->getRequest()->getParam('tabs');
				$category = $categoryId = $this->getRequest()->getParam('category');
				$attribute = $this->getRequest()->getParam('attribute');
				$result['element_id'] = 'attribute-products'.$blockId;
				if($tabs){
					$category = $this->getModel('Magento\Catalog\Model\Category')->load($category);
					$categoryId = $category->getId();
					$result['element_id'] = 'attribute-products'.$categoryId.$blockId;
				}
				
				$htmlPrev = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\Attributes')
					->setTabs($tabs)
					->setAdditionalData($category)
					->setAttributeCode($attribute)
					->setLimit($limit)
					->setRatio($ratio)
					->setPrevPage(true)
					->setPerRow($perrow)
					->setCol($col)
					->setBlockId($blockId)
					->setCurPage($p-1)
					->setTemplate('products/attribute/grid/loadmore_content.phtml')
					->toHtml();
					
				$html = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\Attributes')
					->setTabs($tabs)
					->setAdditionalData($category)
					->setAttributeCode($attribute)
					->setLimit($limit)
					->setPerRow($perrow)
					->setRatio($ratio)
					->setCol($col)
					->setBlockId($blockId)
					->setCurPage($p)
					->setTemplate('products/attribute/grid/loadmore_content.phtml')
					->toHtml();
					
				$htmlNext = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\Attributes')
					->setTabs($tabs)
					->setAdditionalData($category)
					->setAttributeCode($attribute)
					->setLimit($limit)
					->setPerRow($perrow)
					->setRatio($ratio)
					->setNextPage(true)
					->setCol($col)
					->setBlockId($blockId)
					->setCurPage($nextPage)
					->setTemplate('products/attribute/grid/loadmore_content.phtml')
					->toHtml();
				
				if($html != $htmlNext){
					$result['url'] = $this->_urlBuilder->getUrl('mpanel/index/loadmore', ['type'=>$type, 'perrow'=>$perrow, 'category'=>$categoryId, 'limit'=>$limit, 'p'=>$nextPage, 'ratio'=>$ratio, 'col'=>$col, 'block_id'=>$blockId, 'attribute'=>$attribute, 'tabs'=>$tabs]);
					$result['next'] = $htmlNext;
				}else{
					if($htmlPrev == $html){
						$html = '';
					}
				}
				
				break;
			
			// Sale Products
			case 'sale-products-category':
				$tabs = $this->getRequest()->getParam('tabs');
				$category = $categoryId = $this->getRequest()->getParam('category');
				$result['element_id'] = 'sale-products'.$blockId;
				if($tabs){
					$category = $this->getModel('Magento\Catalog\Model\Category')->load($category);
					$categoryId = $category->getId();
					$result['element_id'] = 'sale-products'.$categoryId.$blockId;
				}
				
				$htmlPrev = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\Sale')
					->setTabs($tabs)
					->setAdditionalData($category)
					->setLimit($limit)
					->setRatio($ratio)
					->setPerRow($perrow)
					->setPrevPage(true)
					->setCol($col)
					->setBlockId($blockId)
					->setCurPage($p-1)
					->setTemplate('products/sale/grid/loadmore_content.phtml')
					->toHtml();
					
				$html = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\Sale')
					->setTabs($tabs)
					->setAdditionalData($category)
					->setLimit($limit)
					->setRatio($ratio)
					->setPerRow($perrow)
					->setCol($col)
					->setBlockId($blockId)
					->setCurPage($p)
					->setTemplate('products/sale/grid/loadmore_content.phtml')
					->toHtml();
					
				$htmlNext = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\Sale')
					->setTabs($tabs)
					->setAdditionalData($category)
					->setLimit($limit)
					->setRatio($ratio)
					->setPerRow($perrow)
					->setCol($col)
					->setBlockId($blockId)
					->setNextPage(true)
					->setCurPage($nextPage)
					->setTemplate('products/sale/grid/loadmore_content.phtml')
					->toHtml();
				
				if($html != $htmlNext){
					$result['url'] = $this->_urlBuilder->getUrl('mpanel/index/loadmore', ['type'=>$type, 'perrow'=>$perrow, 'category'=>$categoryId, 'limit'=>$limit, 'p'=>$nextPage, 'ratio'=>$ratio, 'col'=>$col, 'block_id'=>$blockId, 'tabs'=>$tabs]);
					$result['next'] = $htmlNext;
				}else{
					if($htmlPrev == $html){
						$html = '';
					}
				}
				
				break;
			
			// Top Rate Products
			case 'rate-products-category':
				$tabs = $this->getRequest()->getParam('tabs');
				$category = $categoryId = $this->getRequest()->getParam('category');
				$result['element_id'] = 'rate-products'.$blockId;
				if($tabs){
					$category = $this->getModel('Magento\Catalog\Model\Category')->load($category);
					$categoryId = $category->getId();
					$result['element_id'] = 'rate-products'.$categoryId.$blockId;
				}
				
				$htmlPrev = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\Rate')
					->setTabs($tabs)
					->setAdditionalData($category)
					->setLimit($limit)
					->setRatio($ratio)
					->setPrevPage(true)
					->setPerRow($perrow)
					->setCol($col)
					->setBlockId($blockId)
					->setCurPage($p-1)
					->setTemplate('products/rate/grid/loadmore_content.phtml')
					->toHtml();
					
				$html = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\Rate')
					->setTabs($tabs)
					->setAdditionalData($category)
					->setLimit($limit)
					->setRatio($ratio)
					->setPerRow($perrow)
					->setCol($col)
					->setBlockId($blockId)
					->setCurPage($p)
					->setTemplate('products/rate/grid/loadmore_content.phtml')
					->toHtml();
					
				$htmlNext = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\Rate')
					->setTabs($tabs)
					->setAdditionalData($category)
					->setLimit($limit)
					->setRatio($ratio)
					->setPerRow($perrow)
					->setCol($col)
					->setNextPage(true)
					->setBlockId($blockId)
					->setCurPage($nextPage)
					->setTemplate('products/rate/grid/loadmore_content.phtml')
					->toHtml();
				
				if($html != $htmlNext){
					$result['url'] = $this->_urlBuilder->getUrl('mpanel/index/loadmore', ['type'=>$type, 'perrow'=>$perrow, 'category'=>$categoryId, 'limit'=>$limit, 'p'=>$nextPage, 'ratio'=>$ratio, 'col'=>$col, 'block_id'=>$blockId, 'tabs'=>$tabs]);
					$result['next'] = $htmlNext;
				}else{
					if($htmlPrev == $html){
						$html = '';
					}
				}
				
				break;
			
			// Category Products
			case 'category-products-category':
				$tabs = $this->getRequest()->getParam('tabs');
				$category = $categoryId = $this->getRequest()->getParam('category');
				$result['element_id'] = 'category-products'.$blockId;
				if($tabs){
					$category = $this->getModel('Magento\Catalog\Model\Category')->load($category);
					$categoryId = $category->getId();
					$result['element_id'] = 'category-products'.$categoryId.$blockId;
				}
				
				$htmlPrev = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\Category')
					->setTabs($tabs)
					->setAdditionalData($category)
					->setLimit($limit)
					->setRatio($ratio)
					->setPerRow($perrow)
					->setPrevPage(true)
					->setCol($col)
					->setBlockId($blockId)
					->setCurPage($p-1)
					->setTemplate('products/category_products/grid/loadmore_content.phtml')
					->toHtml();
					
				$html = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\Category')
					->setTabs($tabs)
					->setAdditionalData($category)
					->setLimit($limit)
					->setRatio($ratio)
					->setPerRow($perrow)
					->setCol($col)
					->setBlockId($blockId)
					->setCurPage($p)
					->setTemplate('products/category_products/grid/loadmore_content.phtml')
					->toHtml();
					
				$htmlNext = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\Category')
					->setTabs($tabs)
					->setAdditionalData($category)
					->setLimit($limit)
					->setRatio($ratio)
					->setPerRow($perrow)
					->setCol($col)
					->setBlockId($blockId)
					->setNextPage(true)
					->setCurPage($nextPage)
					->setTemplate('products/category_products/grid/loadmore_content.phtml')
					->toHtml();
				
				if($html != $htmlNext){
					$result['url'] = $this->_urlBuilder->getUrl('mpanel/index/loadmore', ['type'=>$type, 'perrow'=>$perrow, 'category'=>$categoryId, 'limit'=>$limit, 'p'=>$nextPage, 'ratio'=>$ratio, 'col'=>$col, 'block_id'=>$blockId, 'tabs'=>$tabs]);
					$result['next'] = $htmlNext;
				}else{
					if($htmlPrev == $html){
						$html = '';
					}
				}
				
				break;
				
			case 'category-tabs':
				$categoryId = $this->getRequest()->getParam('category');
				$category = $this->getModel('Magento\Catalog\Model\Category')->load($categoryId);
				$html = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\Category\Tabs')
					->setAdditionalData($category)
					->setProductsCount($count)
					->setPerRow($perrow)
					->setCurPage($p)
					->setTemplate('products/items.phtml')
					->toHtml();
					
				$htmlNext = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\Category\Tabs')
					->setAdditionalData($category)
					->setProductsCount($count)
					->setPerRow($perrow)
					->setCurPage($nextPage)
					->setTemplate('products/items.phtml')
					->toHtml();
				
				if($html != $htmlNext){
					$result['url'] = $this->_urlBuilder->getUrl('mpanel/index/loadmore', ['type'=>$type, 'perrow'=>$perrow, 'category'=>$categoryId, 'products_count'=>$count, 'perrow'=>$perrow, 'p'=>$nextPage]);
				}else{
					$html = '';
				}
				break;
				
			case 'attribute-tabs':
				$attributeCode = $this->getRequest()->getParam('attribute');
				$html = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\Tabs')
					->setAdditionalData($attributeCode)
					->setProductsCount($count)
					->setPerRow($perrow)
					->setCurPage($p)
					->setTemplate('products/items.phtml')
					->toHtml();
					
				$htmlNext = $this->_view->getLayout()
					->createBlock('MGS\Mpanel\Block\Products\Tabs')
					->setAdditionalData($attributeCode)
					->setProductsCount($count)
					->setPerRow($perrow)
					->setCurPage($nextPage)
					->setTemplate('products/items.phtml')
					->toHtml();
				
				if($html != $htmlNext){
					$result['url'] = $this->_urlBuilder->getUrl('mpanel/index/loadmore', ['type'=>$type, 'attribute'=>$attributeCode, 'products_count'=>$count, 'perrow'=>$perrow, 'p'=>$nextPage]);
				}else{
					$html = '';
				}
				break;
		}
		
		$result['content'] = $html;
		
		//echo json_encode($result);
		
		$this->getResponse()->setHeader('Content-type', 'text/plain', true);
		$this->getResponse()->setBody(json_encode($result));
    }
	
	public function getModel($model){
		return $this->_objectManager->create($model);
	}
}

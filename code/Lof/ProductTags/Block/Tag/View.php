<?php
/**
 * Copyright (c) 2019  Landofcoder
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Lof\ProductTags\Block\Tag;

class View extends \Magento\Framework\View\Element\Template
{
    protected $resultPageFactory;

    protected $_tagFactory;

    protected $_tagcollection;

    protected $_tagHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Lof\ProductTags\Model\TagFactory $tagFactory,
        \Lof\ProductTags\Helper\Data $tagdata,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_tagFactory = $tagFactory;
        $this->_tagHelper = $tagdata;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }
    public function _toHtml(){
        if(!$this->_tagHelper->getGeneralConfig('enabled')) return;
        if(!$this->_tagHelper->getGeneralConfig('enable_tag_on_product')) return;
        return parent::_toHtml();
    }
    function getTagHelper(){
        return $this->_tagHelper;
    }
    public function getCurrentTag()
    {
        $tag = $this->_coreRegistry->registry('current_tag');
        if ($tag) {
            $this->setData('current_tag', $tag);
        }
        return $tag;
    }

     /**
     * Prepare breadcrumbs
     *
     * @param \Magento\Cms\Model\Page $brand
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _addBreadcrumbs()
    {
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $tag = $this->getCurrentTag();
       
        if($breadcrumbsBlock)
        {
        $breadcrumbsBlock->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $baseUrl
            ]
            );
        
        $breadcrumbsBlock->addCrumb(
            'tag',
            [
                'label' => $tag->getTagTitle(),
                'title' => $tag->getTagTitle(),
                'link' => ''
            ]
            );
        }
    }
    /**
     * @return string
     */
    public function getProductListHtml()
    {
    	return $this->getChildHtml('product_list');
    }
    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $tag = $this->getCurrentTag();
        $page_title = $tag->getTagTitle();
        $meta_description = $tag->getTagDescription();
        $this->_addBreadcrumbs();
        if($page_title){
            $this->pageConfig->getTitle()->set($page_title);   
        }
        if($meta_description){
            $this->pageConfig->setDescription($meta_description);   
        }
        return parent::_prepareLayout();
    }
}

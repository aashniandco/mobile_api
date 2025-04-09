<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Block\Framework;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
/**
 * Main contact form block
 */
class Page extends \Magento\Framework\View\Result\Page
{
	/**
     * Add default body classes for current page layout
     *
     * @return $this
     */
    protected function addDefaultBodyClasses()
    {
        $this->pageConfig->addBodyClass($this->request->getFullActionName('-'));
        $pageLayout = $this->getPageLayout();
        if ($pageLayout) {
            $this->pageConfig->addBodyClass('page-layout-' . $pageLayout);
        }
		$width = $this->getStoreConfig('mgstheme/general/width');
		if($width != 'width1200'){
			$this->pageConfig->addBodyClass($width);
		}
		$layout = $this->getStoreConfig('mgstheme/general/layout');
		$this->pageConfig->addBodyClass($layout);
		
        return $this;
    }
	
	public function getStoreConfig($node){
		$helper =  \Magento\Framework\App\ObjectManager::getInstance()->get('MGS\Mpanel\Helper\Data');
		
		return $helper->getStoreConfig($node);
	}
	
	protected function render(HttpResponseInterface $response)
    {
        $this->pageConfig->publicBuild();
        if ($this->getPageLayout()) {
            $config = $this->getConfig();
            $this->addDefaultBodyClasses();
            $addBlock = $this->getLayout()->getBlock('head.additional'); // todo
            $requireJs = $this->getLayout()->getBlock('require.js');
			
			
            $this->assign([
                'requireJs' => $requireJs ? $requireJs->toHtml() : null,
                'headContent' => $this->pageConfigRenderer->renderHeadContent(),
                'headAdditional' => $addBlock ? $addBlock->toHtml() : null,
                'htmlAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_HTML),
                'headAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_HEAD),
                'bodyAttributes' => $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_BODY),
                'loaderIcon' => $this->getViewFileUrl('images/loader-2.gif'),
				'topPanel' => $this->getLayout()->createBlock('MGS\Mpanel\Block\Panel\Toppanel')->setTemplate('panel/toppanel.phtml')->setCacheable(false)->toHtml(),
            ]);
			
			
			
			$output = $mainContent = $this->getLayout()->getOutput();
			
			
			
			
			if($this->getLayout()->getBlock('header')){
				$headerContent = $this->getLayout()->getBlock('header')->toHtml();
				$mainContent = str_replace($headerContent,'',$mainContent);
				
				$this->assign([
					'headerContent' => $headerContent
				]);
			}
			
			if($this->getLayout()->getBlock('footer')){
				$footerContent = $this->getLayout()->getBlock('footer')->toHtml();
				$mainContent = str_replace($footerContent,'',$mainContent);
				
				$this->assign([
					'footerContent' => $footerContent
				]);
			}
			
			$scriptContent = '';
			if($this->getLayout()->getBlock('home.script')){
				$scriptContent = $this->getLayout()->getBlock('home.script')->toHtml();
				$mainContent = str_replace($scriptContent,'',$mainContent);
			}
			
			if($this->getLayout()->getBlock('mgs.script')){
				$globalScript = $this->getLayout()->getBlock('mgs.script')->toHtml();
				$mainContent = str_replace($globalScript,'',$mainContent);
				$scriptContent .= $globalScript;
			}
			
			$this->assign([
				'scriptContent' => $scriptContent
			]);
			
			$mainContent = str_replace('<header class="header"></header>','',$mainContent);
			$mainContent = str_replace('<footer class="footer"></footer>','',$mainContent);
			
			$condition = '#<\!--\[if[^\>]*>\s*<script.*</script>\s*<\!\[endif\]-->#isU';
			preg_match_all($condition, $mainContent, $matches);
			$ifJs = implode('', $matches[0]);

			$temp = preg_replace($condition, '' , $mainContent);


			$condition = '@(?:<script|<script)(.*)</script>@msU';
			preg_match_all($condition,$temp,$matches);
			$js = implode('',$matches[0]);

			$script = $js . $ifJs;
			
			$builderContent = $this->getLayout()->createBlock('MGS\Mpanel\Block\Panel\HomeContent')->setTemplate('panel/homecontent.phtml')->setCacheable(false)->toHtml();
			$builderContent .= $script;

			$this->assign([
				'mainContent' => $mainContent,
				'builderContent' => $builderContent
			]);

            $this->assign('layoutContent', $output);
            $output = $this->renderPage();
            $this->translateInline->processResponseBody($output);
            $response->appendBody($output);
        } else {
            parent::render($response);
        }
        return $this;
    }
}


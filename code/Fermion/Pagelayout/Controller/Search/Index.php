<?php
namespace Fermion\Pagelayout\Controller\Search;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	protected $block;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Fermion\Pagelayout\Block\Search $block
	)
	{
		$this->_pageFactory = $pageFactory;
		$this->block = $block;
		return parent::__construct($context);
	}

	public function execute()
	{

		//die('-------------');
		if(isset($_GET['dev']) && $_GET['dev'] == 'sid'){
			die('siddhesh..');
		}

		$requestParams = $this->getRequest()->getParams();
		$searchString = $this->getRequest()->getParam('q');

		$respData = array();
		$respData = $this->block->getSearchFilterdata($searchString,$requestParams);
		$htmlData = isset($respData['prod_grid_html']) ? $respData['prod_grid_html'] : '';

		if(isset($htmlData['prod_html']) && $htmlData['prod_html'] == '') {
			$resultPage = $this->_pageFactory->create();
            $resultPage->addHandle('pagelayout_no_search_result');
            return $resultPage;
		}
		else{
			$resultPage = $this->_pageFactory->create();
    		return $resultPage;
		}
	}
}
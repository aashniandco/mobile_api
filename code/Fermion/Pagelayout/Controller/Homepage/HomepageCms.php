<?php

namespace Fermion\Pagelayout\Controller\Homepage;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\View\Result\PageFactory;

class HomepageCms extends Action {
 
    protected $conn;
    protected $resultJsonFactory;
    protected $blockRepository;
    protected $pageFactory;

    public function __construct(        
        ResourceConnection $resourceConn,
        Context $context,
        JsonFactory $resultJsonFactory,
        BlockRepository $blockRepository,
        PageFactory $pageFactory
    ) {        
        $this->conn = $resourceConn->getConnection();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->blockRepository = $blockRepository;
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute() {
        $resultJson = $this->resultJsonFactory->create();
        $blockId = $this->getRequest()->getParam('block_id');

        try {
            // Create a page result to use the layout and render the block
            $page = $this->pageFactory->create();
            $block = $page->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($blockId);
            $blockContent = $block->toHtml();

            $response = [
                'success' => true,
                'html' => $blockContent
            ];
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }

        return $resultJson->setData($response);
    }
}

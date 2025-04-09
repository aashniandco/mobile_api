<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mec\Designerpage\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
	$resultPage = $this->resultPageFactory->create();
	$resultPage->getConfig()->getTitle()->set('Shop latest collections by premium designers at Aashni + Co');
	$resultPage->getConfig()->setDescription('Luxury designer wear collections for men and women at Aashni + Co. Shop the most trendy styles by designers like Manish Malhotra, Sabyasachi, Anita Dongre and more.');
	$resultPage->getConfig()->setKeywords('designers,Indian,premium,collections,men,women,trendy,Manish,Malhotra,Sabyasachi,Anita,Dongre');

	return $resultPage;

        #return $this->resultPageFactory->create();
    }
}


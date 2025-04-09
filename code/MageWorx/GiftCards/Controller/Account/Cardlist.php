<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Controller\Account;

use \Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultFactory;

class Cardlist extends Action
{
    /**
     * @var  \Magento\Framework\View\Result\Page
     */
    protected $resultPageFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Cardlist constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession   = $customerSession;
        parent::__construct($context);
    }

    /**
     * Cards Index
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {

            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('customer/account/login');

            return $resultRedirect;
        }

        return $this->resultPageFactory->create();
    }
}

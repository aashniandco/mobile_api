<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Controller\Adminhtml\GiftCards;

class NewAction extends \Magento\Backend\App\Action
{

    protected $resultForwardFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * Create new giftcard action
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        $resultForward->forward('edit');

        return $resultForward;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageWorx_GiftCards::mageworx_giftcards_giftcards');
    }
}

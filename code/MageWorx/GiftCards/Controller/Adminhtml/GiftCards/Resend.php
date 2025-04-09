<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\GiftCards\Controller\Adminhtml\GiftCards;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use MageWorx\GiftCards\Api\GiftCardManagementInterface;

class Resend extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var GiftCardManagementInterface
     */
    protected $giftCardManagement;

    /**
     * Resend constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param GiftCardManagementInterface $giftCardManagement
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        GiftCardManagementInterface $giftCardManagement
    ) {
        $this->resultPageFactory  = $resultPageFactory;
        $this->giftCardManagement = $giftCardManagement;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $giftCardId     = (int)$this->getRequest()->getParam('card_id');
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $this->giftCardManagement->sendEmailWithGiftCard($giftCardId);
            $this->messageManager->addSuccessMessage(__('Gift Card email was successfully resent'));
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->messageManager->addException($e, __('Something went wrong while sending the Gift Card.'));
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageWorx_GiftCards::mageworx_giftcards_giftcards');
    }
}

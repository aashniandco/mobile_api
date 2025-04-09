<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Controller\Adminhtml\ImportExport;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\GiftCards\Api\ExportHandlerInterface;
use MageWorx\GiftCards\Api\ImportHandlerInterface;

/**
 * Class ExpressImportPost
 */
class ExpressImportPost extends \MageWorx\GiftCards\Controller\Adminhtml\ImportExport
{
    /**
     * ExpressImportPost constructor.
     *
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param ExportHandlerInterface $exportHandler
     * @param ImportHandlerInterface $importHandler
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        ExportHandlerInterface $exportHandler,
        ImportHandlerInterface $importHandler
    ) {
        parent::__construct($context, $fileFactory, $exportHandler, $importHandler);
    }

    /**
     * Import action from import/export gift cards
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                if (!is_null($this->getRequest()->getFiles('import_giftcards_file'))) {
                    $this->importHandler->importFromCsvFile(
                        $this->getRequest()->getFiles('import_giftcards_file')
                    );
                }
                $this->messageManager->addSuccessMessage(__('Data has been imported.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Invalid file upload attempt'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Invalid file upload attempt'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());

        return $resultRedirect;
    }
}
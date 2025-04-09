<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Controller\Adminhtml\ExportGrid;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use MageWorx\GiftCards\Controller\Adminhtml\ImportExport;

class ExportCsv extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \MageWorx\GiftCards\Model\ImportExport\ExportHandler
     */
    protected $exportHandler;

    /**
     * ExportCsv constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \MageWorx\GiftCards\Model\ImportExport\ExportHandler $exportHandler
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \MageWorx\GiftCards\Model\ImportExport\ExportHandler $exportHandler
    ) {
        $this->fileFactory   = $fileFactory;
        $this->exportHandler = $exportHandler;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $ids    = [];
        $params = $this->getRequest()->getParams();

        if (!empty($params['selected']) && is_array($params['selected'])) {
            $ids = $params['selected'];
            $ids = array_map('intval', $ids);
        }

        $content = $this->exportHandler->getContent($ids);

        return $this->fileFactory->create('gift_cards_export_file.csv', $content, DirectoryList::VAR_DIR);
    }

    /**
     * Is access to section allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(ImportExport::ADMIN_RESOURCE);

    }
}
<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Controller\Adminhtml;

use MageWorx\GiftCards\Api\ExportHandlerInterface;
use MageWorx\GiftCards\Api\ImportHandlerInterface;

abstract class ImportExport extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'MageWorx_GiftCards::import_export';

    /**
     * Menu id
     */
    const MENU_IDENTIFIER = 'MageWorx_GiftCards::system_import_export';

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var ExportHandlerInterface
     */
    protected $exportHandler;

    /**
     * @var ImportHandlerInterface
     */
    protected $importHandler;

    /**
     * ImportExport constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param ExportHandlerInterface $exportHandler
     * @param ImportHandlerInterface $importHandler
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        ExportHandlerInterface $exportHandler,
        ImportHandlerInterface $importHandler
    ) {
        $this->fileFactory   = $fileFactory;
        $this->exportHandler = $exportHandler;
        $this->importHandler = $importHandler;
        parent::__construct($context);
    }
}
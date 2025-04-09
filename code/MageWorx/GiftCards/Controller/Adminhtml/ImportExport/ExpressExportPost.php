<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Controller\Adminhtml\ImportExport;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

/**
 * Class ExpressExportPost
 */
class ExpressExportPost extends \MageWorx\GiftCards\Controller\Adminhtml\ImportExport
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
     * Export action from import/export gift cards
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        /** @var \MageWorx\GiftCards\Model\ImportExport\ExportHandler $exportHandler */
        $content = $this->exportHandler->getContent();

        return $this->fileFactory->create(
            'gift_cards_' . date('Y-m-d') . '_' . time() . '.csv',
            $content,
            DirectoryList::VAR_DIR
        );
    }
}
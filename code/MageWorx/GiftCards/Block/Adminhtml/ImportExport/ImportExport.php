<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block\Adminhtml\ImportExport;

use Magento\Backend\Block\Template\Context;

/**
 * Class ImportExport
 *
 *
 * @method bool|null getIsReadonly()
 * @method ImportExport setUseContainer($bool)
 */
class ImportExport extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'MageWorx_GiftCards::datatransfer/import_export.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setUseContainer(true);
    }

    /**
     * Return CSS classes for the export gift cards form container (<div>)
     * as a string concatenated with a space
     *
     * @return string
     */
    public function getExportGiftCardsClasses()
    {
        $exportGiftCardsClasses = ['export-giftcards'];
        if ($this->getIsReadonly()) {
            $exportGiftCardsClasses[] = 'box-left';
        } else {
            $exportGiftCardsClasses[] = 'box-right';
        }

        $exportGiftCardsClasses = implode(' ', $exportGiftCardsClasses);

        return $exportGiftCardsClasses;
    }

    /**
     * @return string
     */
    public function getMigrationMessage()
    {
        return __(
            'If you migrate the gift cards from Magento 1 store, make sure you exported the gift card codes in Magento 1 with the following settings: '
            . 'Value delimiter = "," '
            . 'Enclose Values In = "'
        );
    }
}
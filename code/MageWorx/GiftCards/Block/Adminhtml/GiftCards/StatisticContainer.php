<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block\Adminhtml\GiftCards;

class StatisticContainer extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setBackUrl($this->getUrl('*/*/index'));
        $this->_addBackButton();
        $this->removeButton('add');
    }
}

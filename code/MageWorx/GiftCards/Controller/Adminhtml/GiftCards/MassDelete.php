<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Controller\Adminhtml\GiftCards;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

class MassDelete extends MassAction
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 record(s) have been deleted';
    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while deleting record(s).';

    /**
     * @param \MageWorx\GiftCards\Model\GiftCards $giftcard
     * @return $this
     */
    protected function doTheAction($giftcard)
    {
        $giftcard->getResource()->delete($giftcard);

        return $this;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageWorx_GiftCards::mageworx_giftcards_giftcards');
    }
}

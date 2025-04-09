<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Controller\Adminhtml\GiftCards;

use MageWorx\GiftCards\Model\GiftCards as GiftCard;

class MassDeactivate extends MassAction
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 gift cards have been deactivated';
    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while deactivating gift cards.';
    /**
     * @var integer
     */
    protected $status = GiftCard::STATUS_INACTIVE;

    /**
     * @param \MageWorx\GiftCards\Model\GiftCards $giftcard
     * @return $this
     */
    protected function doTheAction($giftcard)
    {
        $giftcard->setCardStatus($this->status);
        $giftcard->getResource()->save($giftcard);

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

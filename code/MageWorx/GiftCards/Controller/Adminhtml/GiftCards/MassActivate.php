<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Controller\Adminhtml\GiftCards;

use MageWorx\GiftCards\Model\GiftCards as GiftCard;

class MassActivate extends MassDeactivate
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 gift cards have been activated';

    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while activating gift cards.';

    /**
     * @var integer
     */
    protected $status = GiftCard::STATUS_ACTIVE;


    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageWorx_GiftCards::mageworx_giftcards_giftcards');
    }
}

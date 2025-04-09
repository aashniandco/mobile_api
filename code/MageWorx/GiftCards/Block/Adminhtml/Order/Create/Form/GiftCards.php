<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\GiftCards\Block\Adminhtml\Order\Create\Form;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use MageWorx\GiftCards\Model\QuoteGiftCardsDescription;

class GiftCards extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * @var QuoteGiftCardsDescription
     */
    protected $quoteGiftCardsDescription;

    /**
     * GiftCards constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param QuoteGiftCardsDescription $quoteGiftCardsDescription
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        QuoteGiftCardsDescription $quoteGiftCardsDescription,
        array $data = []
    ) {
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
        $this->quoteGiftCardsDescription = $quoteGiftCardsDescription;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_giftcards_form');
    }

    /**
     * @return array
     */
    public function getGiftCards(): array
    {
        return $this->quoteGiftCardsDescription->getCodes((string)$this->getQuote()->getMageworxGiftcardsDescription());
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Gift Cards');
    }

    /**
     * Get header css class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-giftcards';
    }
}

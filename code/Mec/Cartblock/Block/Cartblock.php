<?php
declare(strict_types=1);

namespace Mec\Cartblock\Block;

class Cartblock extends \Magento\Framework\View\Element\Template
{

    protected $_storeManager;
    protected $_currency;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\Currency $currency
     * @param array $data
     */
    public function __construct(
	\Magento\Framework\View\Element\Template\Context $context,
	\Magento\Store\Model\StoreManagerInterface $storeManager,
	\Magento\Directory\Model\Currency $currency,
        array $data = []
    ) {
	$this->_storeManager = $storeManager;
        $this->_currency = $currency;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function displayContent()
    {
        //Your block code
	    #return __('Hello Developer! This how to get the storename: %1 and this is the way to build a url: %2', $this->_storeManager->getStore()->getName(), $this->getUrl('contacts'));
	    #return __('You will be charged '. $this->getCurrentCurrencyCode());
    }

     /**
     * Get current store currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }       
}


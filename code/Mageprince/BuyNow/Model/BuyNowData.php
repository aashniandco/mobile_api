<?php 

namespace Mageprince\BuyNow\Model;

use Magento\Customer\Model\Session as CustomerSession;

class BuyNowData
{
	protected $customerSession;

	public function __construct(
		CustomerSession $customerSession
	){
		$this->customerSession = $customerSession;
	}


	public function setBuyNowFlag($val)
	{
		$this->customerSession->setData('buy_now_flag', $val);
	}

	public function getBuyNowFlag()
	{
		return $this->customerSession->getData('buy_now_flag');
	}

	public function setOriginalQuoteId($quoteId){
		$this->customerSession->setData('quote_id', $quoteId);
	}

	public function getOriginalQuoteId(){
		if($this->customerSession->getData('quote_id') == null || $this->customerSession->getData('quote_id') == ''){
			return null;
		}
		return $this->customerSession->getData('quote_id');
	}
}
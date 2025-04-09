<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Block\Panel;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Main contact form block
 */
class Toppanel extends Template
{
	
	protected $helper;
	
	public function __construct(
		Template\Context $context,
		\MGS\Mpanel\Helper\Data $helper,
		array $data = []
	){
        parent::__construct($context, $data);
		$this->_isScopePrivate = true;
		$this->helper = $helper;
    }
	
	public function getHelper(){
		return $this->helper;
	}
	
	/**
     * Returns customer id from session
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->helper->getCustomerId();
    }
	
	public function getCustomer(){
		return $this->helper->getCustomer();
	}
	
	public function getPageLayoutConfig(){
		$object = new \MGS\Mpanel\Model\Config\Source\Layout;
		$result = $object->toOptionArray();
		return $result;
	}
	
	public function getPerrowConfig(){
		$result = [['value' => '', 'label' => __('Use Config')]]; 
		$object = new \MGS\Mpanel\Model\Config\Source\Perrow;
		$result = array_merge($result, $object->toOptionArray());
		return $result;
	}
	
	public function getRatioConfig(){
		$result = [['value' => '', 'label' => __('Use Config')]]; 
		$object = new \MGS\Mpanel\Model\Config\Source\Ratio;
		$result = array_merge($result, $object->toOptionArray());
		return $result;
	}
}


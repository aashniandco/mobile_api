<?php

namespace Fermion\Pagelayout\Block\Account;

use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends \Magento\Framework\View\Element\Template
{
	/**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

	/**
     * Customer Account Delete Block constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->customerSession= $customerSession;
        parent::__construct($context, $data);
    }

    public function getCustomerId(){
    	return $this->customerSession->getCustomerId();
    }
}
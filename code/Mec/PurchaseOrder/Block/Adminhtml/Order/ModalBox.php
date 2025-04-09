<?php


namespace Mec\PurchaseOrder\Block\Adminhtml\Order;

/**
 * Class ModalBox
 *
 * @package StackExchange\MagentoTest\Block\Adminhtml\Order
 */
class ModalBox extends \Magento\Backend\Block\Template
{

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getInfo()
    {
        //Your block cod
        return __('Hello Developer! This how to get the ');
    }
    public function getFormUrl()
    {
        $orderId = false;
        if($this->hasData('order')){
            $orderId = $this->getOrder()->getId();
        }
        return $this->getUrl('magentotest/order/order',[
            'order_id' => $orderId
        ]);
    }
}
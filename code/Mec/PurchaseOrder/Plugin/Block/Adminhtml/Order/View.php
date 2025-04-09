<?php


namespace Mec\PurchaseOrder\Plugin\Block\Adminhtml\Order;

/**
 * Class View
 *
 * @package Mec\PurchaseOrder\Plugin\Magento\Sales\Block\Adminhtml\Order
 */
class View
{

    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $view)
    {
    $message ='Are you sure you want to do this?';
    $url = '/mymodule/controller/action/id/' . $view->getOrderId();


    $view->addButton(
    'purchase_order_button',
    [
        'label' => __('Create Purchase Order'),
        'class' => 'myclass action-default action-warranty-order',
        //'onclick' => "confirmSetLocation('{$message}', '{$url}')"
        'onclick' => ''
    ]
    );


    }

    

    /*public function afterToHtml(
        \Magento\Sales\Block\Adminhtml\Order\View $subject,
        $result
    ) {
        if($subject->getNameInLayout() == 'sales_order_edit'){
            $customBlockHtml = $subject->getLayout()->createBlock(
                \Mec\PurchaseOrder\Block\Adminhtml\Order\ModalBox::class,
                $subject->getNameInLayout().'_modal_box'
            )->setOrder($subject->getOrder())
                ->setTemplate('Mec_PurchaseOrder::order/view/modalbox.phtml')
                ->toHtml();
            return $result.$customBlockHtml;
        }
        return $result;
    }*/
}
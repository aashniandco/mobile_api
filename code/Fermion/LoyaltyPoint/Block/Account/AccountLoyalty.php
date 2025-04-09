<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Fermion\LoyaltyPoint\Block\Account;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Giftcards history block
 */
class AccountLoyalty extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Fermion\LoyaltyPoint\Helper\LoyaltypointHelper
     */
    protected $loyaltyHelper;

    /**
     * Cardlist constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Fermion\LoyaltyPoint\Helper\LoyaltypointHelper $loyaltyHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Fermion\LoyaltyPoint\Helper\LoyaltypointHelper $loyaltyHelper,
        array $data = []
    ) {
        $this->customerSession= $customerSession;
        $this->orderCollectionFactory= $orderCollectionFactory;
        $this->priceCurrency = $priceCurrency;
        $this->loyaltyHelper = $loyaltyHelper;
        parent::__construct($context, $data);
    }

    public function getLoyaltyData()
    {
        return $this->loyaltyHelper->getLoyaltyLevel();
    }


    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }


    public function getOrderData($currPage = null)
    {
        if($currPage == null){
            $currPage = 1;
        }
        $respArr = array();
        $paginationHtml = '';
        $orderHtml = '';
        $paginationHtml = '<div class="pages list-inline">';
        $customerId = $this->customerSession->getCustomerId();
        $orders = $this->orderCollectionFactory->create();
        $orders->addFieldToFilter('customer_id', $customerId);
        $totalPages = ceil(($orders->getSize())/10);
        $orders->setOrder('created_at', 'desc');
        $orders->setPageSize(10)->setCurPage($currPage);
        if($currPage == 1){
            $paginationHtml .= '<ul class="pagination" aria-labelledby="paging-label">
                                    <li class="item page-custom">
                                        <input type="number" class="page-number" name="page-number" value=1>
                                    </li>
                                    <li class="item pages-item-next">
                                        <a href="#" class="action next" data-value=2><span>&gt;</span></a>
                                    </li></ul> of &nbsp; <span class="pagination-total-count">'.$totalPages.'</span>';
        }
        elseif ($currPage == $totalPages) {
            $paginationHtml .= '<ul class="pagination" aria-labelledby="paging-label">
                                    <li class="item pages-item-previous">
                                        <a href="#" class="action next" data-value='.($currPage-1).'><span>&lt;</span></a>
                                    </li>
                                    <li class="item page-custom">
                                        <input type="number" class="page-number" name="page-number" value='.$currPage.'>
                                    </li></ul> of &nbsp; <span class="pagination-total-count">'.$totalPages.'</span>';
        }
        else{
            $paginationHtml .= '<ul class="pagination" aria-labelledby="paging-label">
                                    <li class="item pages-item-previous">
                                        <a href="#" class="action previous" data-value='.($currPage-1).'><span>&lt;</span></a>
                                    </li>
                                    <li class="item page-custom">
                                        <input type="number" class="page-number" name="page-number" value='.$currPage.'>
                                    </li>
                                    <li class="item pages-item-next">
                                        <a href="#" class="action next" data-value='.($currPage+1).'><span>&gt;</span></a>
                                    </li></ul> of &nbsp; <span class="pagination-total-count">'.$totalPages.'</span>';
        }
         
        $paginationHtml .= '</div>';
        $respArr['pagination'] = $paginationHtml;
        $orderHtml = '<div class="table-wrapper select-order-table-container">
                        <table class="table-order-items select-order-table">
                            <thead><tr>
                                <th scope="col" class="col id">Order#</th>
                                <th scope="col" class="col date">Date</th>
                                <th scope="col" class="col total">Total</th>
                                <th scope="col" class="col status">Status</th>
                                <th scope="col" class="col select-order">Select Order</th>
                            </tr></thead>
                            <tbody>';
        foreach ($orders as $order) {
            $orderId = $order->getId();
            $orderIncrementId = $order->getIncrementId();
            // $orderTotal = $order->getBaseSubtotal();
            $orderTotal = $order->getGrandtotal();
            $orderDate = new \DateTime($order->getCreatedAt());
            $orderDate = $orderDate->format('Y-m-d');
            $orderStatus = $order->getStatus();
            $orderHtml .= '<tr><td data-th="Order#" class="col id">'.$orderIncrementId.'</td>
                            <td data-th="Date" class="col date">'.$orderDate.'</td>
                            <td data-th="Total" class="col total">'.$orderTotal.'</td>
                            <td data-th="Status" class="col status">'.$orderStatus.'</td>
                            <td data-th="Select-Order" class="col select-order">
                                <input type="checkbox" class="order-check" name="order-check" data-incrementid="'.$orderIncrementId.'" value="'.$orderId.'"> <a href="/sales/order/view/order_id/'.$orderId.'/" target="_blank" class="action view">
                                    <span> (View Order)<span>
                                </a>
                            </td></tr>';
            // error_log("\nOrder Detail :: ".$orderId." <--|--> ".$orderTotal);   
        }
        $orderHtml .= '</tbody></table></div>';
        $respArr['orderHtml'] = $orderHtml;
        return $respArr;
    }

}
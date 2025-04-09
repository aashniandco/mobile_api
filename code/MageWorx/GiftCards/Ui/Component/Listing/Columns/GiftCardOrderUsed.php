<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use MageWorx\GiftCards\Model\ResourceModel\Order as GiftCardsResourceModelOrder;

class GiftCardOrderUsed extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var GiftCardsResourceModelOrder
     */
    protected $giftCardsResourceModelOrder;

    /**
     * GiftCardOrderUsed constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param GiftCardsResourceModelOrder $giftCardsResourceModelOrder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        GiftCardsResourceModelOrder $giftCardsResourceModelOrder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder                  = $urlBuilder;
        $this->giftCardsResourceModelOrder = $giftCardsResourceModelOrder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $listGiftCardId     = $this->getListCardIds($dataSource);
            $giftCardOrdersData = $this->giftCardsResourceModelOrder->getGiftCardOrdersDataByGiftCardId(
                $listGiftCardId
            );
            $giftCardOrdersData = $this->prepareGiftCardOrdersData($giftCardOrdersData);
            foreach ($dataSource['data']['items'] as & $item) {
                if (empty($giftCardOrdersData[$item['card_code']])) {
                    continue;
                }
                $ordersData = $giftCardOrdersData[$item['card_code']];
                foreach ($ordersData as $orderData) {
                    if (empty($orderData['order_id']) && empty($orderData['order_increment_id'])) {
                        continue;
                    }
                    $orderId                               = $orderData['order_id'];
                    $item['order_used'][$orderId]['href']  = $this->urlBuilder->getUrl(
                        'sales/order/view',
                        ['order_id' => $orderId]
                    );
                    $item['order_used'][$orderId]['label'] = $orderData['order_increment_id'];
                    $item['order_used'][$orderId]['pid']   = $orderId;
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param array $giftCardOrdersData
     * @return array
     */
    protected function prepareGiftCardOrdersData($giftCardOrdersData)
    {
        $ordersData = [];

        foreach ($giftCardOrdersData as $giftCardOrder) {

            if (empty($ordersData[$giftCardOrder['giftcard_code']])) {
                $ordersData[$giftCardOrder['giftcard_code']][] = $giftCardOrder;
            }

            if (!$this->hasDuplicate($ordersData, $giftCardOrder)) {
                $ordersData[$giftCardOrder['giftcard_code']][] = $giftCardOrder;
            }
        }

        return $ordersData;
    }

    /**
     * @param array $ordersData
     * @param array $giftCardOrder
     * @return bool
     */
    protected function hasDuplicate($ordersData, $giftCardOrder)
    {
        foreach ($ordersData[$giftCardOrder['giftcard_code']] as $value) {
            if ($value['order_id'] == $giftCardOrder['order_id']
                || $value['order_increment_id'] == $giftCardOrder['order_increment_id']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    protected function getListCardIds($dataSource)
    {
        $ids = [];
        foreach ($dataSource['data']['items'] as & $item) {
            if ($item['card_id']) {
                $ids[] = $item['card_id'];
            }
        }

        return $ids;
    }

    /**
     * @param array $ordersData
     * @param string $giftCardCode
     * @return array
     */
    public function getOrdersIdsByGiftCardCode($ordersData, $giftCardCode)
    {
        $ids = [];
        foreach ($ordersData as $data) {
            if ($data['giftcard_code'] == $giftCardCode) {
                $ids[] = $data['order_id'];
            }
        }

        return $ids;
    }

    /**
     * @param array $listOrders
     * @param string $cardCode
     * @return array
     */
    protected function getOrdersIds(array $listOrders, $cardCode)
    {
        $idsOrder = [];
        foreach ($listOrders as $orders) {
            if ($orders['card_code'] == $cardCode) {
                $idsOrder[] = $orders['order_id'];
            }
        }

        return $idsOrder;
    }
}
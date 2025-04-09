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
use Magento\Sales\Api\OrderRepositoryInterface;

use Magento\Framework\Exception\NoSuchEntityException;

class GiftCardOrderActions extends Column
{

    /** @var UrlInterface */
    protected $urlBuilder;

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        OrderRepositoryInterface $orderRepository,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder      = $urlBuilder;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                try {
                if ($item['order_id']) {
                    $incrementId                          = $this->orderRepository->get($item['order_id'])
                                                                                  ->getIncrementId();
                    $item[$this->getData('name')]['view'] = [
                        'href'   => $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $item['order_id']]),
                        'label'  => $incrementId,
                        'hidden' => false,
                        'target' => '_blank'
                    ];
                }
                } catch (NoSuchEntityException $e) {
                }
            }
        }

        return $dataSource;
    }
}

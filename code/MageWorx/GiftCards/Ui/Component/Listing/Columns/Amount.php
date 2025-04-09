<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Ui\Component\Listing\Columns;

use Magento\Framework\Currency\Exception\CurrencyException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Amount extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'card_amount';

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface                            $context,
        UiComponentFactory                          $uiComponentFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface  $storeManager,
        array                                       $components = [],
        array                                       $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->localeCurrency = $localeCurrency;
        $this->storeManager   = $storeManager;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @throws NoSuchEntityException
     * @throws CurrencyException
     */
    public function prepareDataSource(array $dataSource): array
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (isset($dataSource['data']['items'])) {
            $store = $this->storeManager->getStore(
                $this->context->getFilterParam('store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
            );

            $fieldName    = $this->getData('name');
            $currencyName = $this->getData('currency');
            foreach ($dataSource['data']['items'] as & $item) {

                if (isset($item['card_currency'])) {
                    $currency         = $this->localeCurrency->getCurrency($item['card_currency']);
                    $item[$fieldName] = $currency->toCurrency(sprintf("%f", $item[$fieldName]));
                    continue;
                }

                if (isset($item[$fieldName])) {
                    if (isset($item[$currencyName])) {
                        $currency = $this->localeCurrency->getCurrency($item[$currencyName]);
                    } else {
                        $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());
                    }

                    $item[$fieldName] = $currency->toCurrency(sprintf("%f", $item[$fieldName]));
                }
            }
        }

        return $dataSource;
    }
}

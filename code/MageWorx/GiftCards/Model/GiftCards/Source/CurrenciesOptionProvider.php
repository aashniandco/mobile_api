<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model\GiftCards\Source;

class CurrenciesOptionProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * CurrenciesOptionProvider constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
    ) {
        $this->storeManagerInterface = $storeManagerInterface;
    }

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $result = [];
        foreach ($this->storeManagerInterface->getStores(true) as $nextStore) {
            foreach ($nextStore->getAvailableCurrencyCodes() as $nextCurrencyCode) {
                $result[$nextCurrencyCode] = $nextCurrencyCode;
            }
        }
        array_unshift($result, __('Website Default Currency'));

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $types = [];
        foreach ($this->getAllOptions() as $value => $label) {
            $types[] = ['label' => $label, 'value' => $value];
        }

        return $types;
    }
}

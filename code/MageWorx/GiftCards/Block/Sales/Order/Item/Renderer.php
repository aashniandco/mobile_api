<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Block\Sales\Order\Item;

use MageWorx\GiftCards\Model\Product\Type\GiftCards as ProductTypeGiftCards;
use MageWorx\GiftCards\Model\Source\MainProductFormFields as MainProductFormFieldsOptions;

class Renderer extends \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
{
    /**
     * @var MainProductFormFieldsOptions
     */
    protected $mainProductFormFieldsOptions;

    /**
     * Renderer constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param MainProductFormFieldsOptions $mainProductFormFieldsOptions
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        MainProductFormFieldsOptions $mainProductFormFieldsOptions,
        array $data = []
    ) {
        parent::__construct($context, $string, $productOptionFactory, $data);

        $this->mainProductFormFieldsOptions = $mainProductFormFieldsOptions;
    }

    /**
     * Return gift card and custom options array
     *
     * @return array
     */
    public function getItemOptions()
    {
        return array_merge($this->getGiftCardOptions(), parent::getItemOptions());
    }

    /**
     * Get gift card option list
     *
     * @return array
     */
    protected function getGiftCardOptions()
    {
        $result = [];

        if ($this->getOrderItem()->getProductType() !== ProductTypeGiftCards::TYPE_CODE) {
            return $result;
        }

        $data = $this->getOrderItem()->getProductOptionByCode('info_buyRequest');

        if (!$data) {
            return $result;
        }

        foreach ($this->mainProductFormFieldsOptions->toArray() as $name => $label) {

            $value = !empty($data[$name]) ? $data[$name] : null;

            if ($value) {
                $result[] = ['label' => $label, 'value' => $value];
            }
        }

        return $result;
    }
}

<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Helper\Catalog\Product;

use Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Helper\Product\Configuration as ProductConfiguration;
use MageWorx\GiftCards\Model\Product\Type\GiftCards as ProductTypeGiftCards;
use MageWorx\GiftCards\Model\Source\MainProductFormFields as MainProductFormFieldsOptions;

class Configuration extends AbstractHelper implements ConfigurationInterface
{
    /**
     * @var ProductConfiguration
     */
    protected $productConfiguration;

    /**
     * @var MainProductFormFieldsOptions
     */
    protected $mainProductFormFieldsOptions;

    /**
     * Configuration constructor.
     *
     * @param Context $context
     * @param ProductConfiguration $productConfiguration
     * @param MainProductFormFieldsOptions $mainProductFormFieldsOptions
     */
    public function __construct(
        Context $context,
        ProductConfiguration $productConfiguration,
        MainProductFormFieldsOptions $mainProductFormFieldsOptions
    ) {
        $this->productConfiguration         = $productConfiguration;
        $this->mainProductFormFieldsOptions = $mainProductFormFieldsOptions;

        parent::__construct($context);
    }

    /**
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return array
     */
    public function getOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item)
    {
        return array_merge($this->getGiftCardOptions($item), $this->productConfiguration->getCustomOptions($item));
    }

    /**
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return array
     */
    public function getGiftCardOptions(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item)
    {
        $result = [];

        if ($item->getProduct()->getTypeId() !== ProductTypeGiftCards::TYPE_CODE) {
            return $result;
        }

        foreach ($this->mainProductFormFieldsOptions->toArray() as $name => $label) {

            $option = $item->getOptionByCode($name);

            if ($option) {

                $value = $option->getValue();

                if ($value) {
                    $result[] = ['label' => $label, 'value' => $value];
                }
            }
        }

        return $result;
    }
}

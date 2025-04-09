<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\GiftCards\Model\Quote\Item;

use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Quote\Model\Quote\ProductOptionFactory;
use Magento\Quote\Api\Data\ProductOptionExtensionFactory;
use MageWorx\GiftCards\Api\Data\GiftCardOptionInterface;
use MageWorx\GiftCards\Model\GiftCard\OptionFactory as GiftCardOptionFactory;

class CartItemProcessor implements CartItemProcessorInterface
{
    /**
     * @var DataObjectFactory
     */
    protected $objectFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var GiftCardOptionFactory
     */
    protected $giftCardOptionFactory;

    /**
     * @var ProductOptionFactory
     */
    protected $productOptionFactory;

    /**
     * @var ProductOptionExtensionFactory
     */
    protected $extensionFactory;

    /**
     * CartItemProcessor constructor.
     *
     * @param DataObjectFactory $objectFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param GiftCardOptionFactory $giftCardOptionFactory
     * @param ProductOptionFactory $productOptionFactory
     * @param ProductOptionExtensionFactory $extensionFactory
     */
    public function __construct(
        DataObjectFactory $objectFactory,
        DataObjectHelper $dataObjectHelper,
        GiftCardOptionFactory $giftCardOptionFactory,
        ProductOptionFactory $productOptionFactory,
        ProductOptionExtensionFactory $extensionFactory
    ) {
        $this->objectFactory         = $objectFactory;
        $this->dataObjectHelper      = $dataObjectHelper;
        $this->giftCardOptionFactory = $giftCardOptionFactory;
        $this->productOptionFactory  = $productOptionFactory;
        $this->extensionFactory      = $extensionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToBuyRequest(CartItemInterface $cartItem)
    {
        $productOptions = $cartItem->getProductOption();

        if ($productOptions
            && $productOptions->getExtensionAttributes()
            && $productOptions->getExtensionAttributes()->getMageworxGiftcardItemOption()
        ) {
            $data = $productOptions->getExtensionAttributes()->getMageworxGiftcardItemOption()->getData();

            if (is_array($data)) {
                $requestData = [];
                foreach ($data as $key => $value) {
                    $requestData[$key] = $value;
                }

                return $this->objectFactory->create($requestData);
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function processOptions(CartItemInterface $cartItem)
    {
        $options = $cartItem->getOptions();

        if (is_array($options)) {
            $optionsArray = [];
            foreach ($options as $option) {
                $optionsArray[$option->getCode()] = $option->getValue();
            }

            $giftCardItemOption = $this->giftCardOptionFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $giftCardItemOption,
                $optionsArray,
                GiftCardOptionInterface::class
            );

            $productOption       = $cartItem->getProductOption() ?: $this->productOptionFactory->create();
            $extensibleAttribute = $productOption->getExtensionAttributes() ?: $this->extensionFactory->create();

            $extensibleAttribute->setMageworxGiftcardItemOption($giftCardItemOption);
            $productOption->setExtensionAttributes($extensibleAttribute);
            $cartItem->setProductOption($productOption);
        }

        return $cartItem;
    }
}
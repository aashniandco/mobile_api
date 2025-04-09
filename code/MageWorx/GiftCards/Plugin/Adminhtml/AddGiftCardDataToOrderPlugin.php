<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Plugin\Adminhtml;

use MageWorx\GiftCards\Model\Source\MainProductFormFields as MainProductFormFieldsOptions;

class AddGiftCardDataToOrderPlugin
{
    /**
     * @var MainProductFormFieldsOptions
     */
    protected $mainProductFormFieldsOptions;

    /**
     * AddGiftCardDataToOrderPlugin constructor.
     *
     * @param MainProductFormFieldsOptions $mainProductFormFieldsOptions
     */
    public function __construct(MainProductFormFieldsOptions $mainProductFormFieldsOptions)
    {
        $this->mainProductFormFieldsOptions = $mainProductFormFieldsOptions;
    }

    /**
     * @param \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn $subject
     * @param array $result
     * @return array
     */
    public function afterGetOrderOptions(\Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn $subject, $result)
    {
        if ($subject->getItem()->getProductType() != 'mageworx_giftcards') {
            return $result;
        }

        $data = $subject->getItem()->getProductOptionByCode('info_buyRequest');

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
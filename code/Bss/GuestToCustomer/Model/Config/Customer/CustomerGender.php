<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    BSS_GuestToCustomer
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GuestToCustomer\Model\Config\Customer;

use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Option\ArrayInterface;

class CustomerGender implements ArrayInterface
{
    /**
     * @param Config $eavConfig
     */
    protected $eavConfig;

    /**
     * CustomerGender constructor.
     * @param Config $eavConfig
     */
    public function __construct(
        Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * GetCustomerGenderOptions
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCustomerGenderOptions()
    {
        $attribute = $this->eavConfig->getAttribute('customer', 'gender');
        $genderOptions = $attribute->getSource()->getAllOptions();
        return $genderOptions;
    }

    /**
     * ToOptionArray
     *
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray()
    {
        $optionArray = $this->getCustomerGenderOptions();
        return $optionArray;
    }

    /**
     * ToArray
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function toArray()
    {
        $genderOptions = $this->getCustomerGenderOptions();
        foreach ($genderOptions as $gender) {
            $array[$gender['value']] = $gender['label'];
        }
        return $array;
    }
}

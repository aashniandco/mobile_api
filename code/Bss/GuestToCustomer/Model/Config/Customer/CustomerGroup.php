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

use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Framework\Option\ArrayInterface;

class CustomerGroup implements ArrayInterface
{

    /**
     * Customer Group Collection
     * @var Collection
     */
    protected $customerGroupCollection;

    /**
     * CustomerGroup constructor.
     * @param Collection $customerGroupCollection
     */
    public function __construct(
        Collection $customerGroupCollection
    ) {
        $this->customerGroupCollection = $customerGroupCollection;
    }

    /**
     * Get List Customer Group
     *
     * @return array
     */
    protected function getListCustomerGroup()
    {
        return $this->customerGroupCollection->getData();
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        //Array List Customer Group
        $arrListCustomerGroup = $this->getListCustomerGroup();

        $arrOption = [];
        foreach ($arrListCustomerGroup as $customerGroup) {
            if ($customerGroup['customer_group_code'] != 'NOT LOGGED IN') {
                $group = [
                    'value' => $customerGroup['customer_group_id'],
                    'label' => $customerGroup['customer_group_code']
                ];
                array_push($arrOption, $group);
            }
        }
        return $arrOption;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $arrListCustomerGroup = $this->getListCustomerGroup();

        $array = [];
        foreach ($arrListCustomerGroup as $customerGroup) {
            if ($customerGroup['customer_group_code'] != 'NOT LOGGED IN') {
                $group = [
                    $customerGroup['customer_group_id'],
                    $customerGroup['customer_group_code']
                ];
                array_push($array, $group);
            }
        }

        return $array;
    }
}

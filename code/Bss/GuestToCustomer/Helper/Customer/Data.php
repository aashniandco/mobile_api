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
namespace Bss\GuestToCustomer\Helper\Customer;

use Bss\GuestToCustomer;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Math\Random;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

class Data extends AbstractHelper
{

    /**
     * OrderFactory
     * @var OrderFactory $orderFactory
     */
    protected $orderFactory;

    /**
     * Customer Interface Factory
     * @var CustomerInterfaceFactory $customerInterfaceFactory
     */
    protected $customerInterfaceFactory;

    /**
     * CustomerFactory
     * @var CustomerFactory $customerFactory
     */
    protected $customerFactory;

    /**
     * Registry
     * @var Registry
     */
    protected $registry;

    /**
     * Random
     * @var Random $mathRandom
     */
    protected $mathRandom;

    /**
     * @var GuestToCustomer\Helper\Observer\Helper
     */
    protected $helperObserver;

    /**
     * Data constructor.
     * @param OrderFactory $orderFactory
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param CustomerFactory $customerFactory
     * @param Registry $registry
     * @param Random $mathRamdom
     * @param GuestToCustomer\Helper\Observer\Helper $helperObserver
     * @param Context $context
     */
    public function __construct(
        OrderFactory $orderFactory,
        CustomerInterfaceFactory $customerInterfaceFactory,
        CustomerFactory $customerFactory,
        Registry $registry,
        Random $mathRamdom,
        GuestToCustomer\Helper\Observer\Helper $helperObserver,
        Context $context
    ) {
        $this->orderFactory = $orderFactory;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->customerFactory = $customerFactory;
        $this->registry = $registry;
        $this->mathRandom = $mathRamdom;
        $this->helperObserver = $helperObserver;
        parent::__construct($context);
    }

    /**
     * GetHelperObserver
     *
     * @return GuestToCustomer\Helper\Observer\Helper
     */
    public function getHelperObserver()
    {
        return $this->helperObserver;
    }

    /**
     * GetMathRanDom
     *
     * @return Random
     */
    public function getMathRanDom()
    {
        return $this->mathRandom;
    }

    /**
     * GetRegistry
     *
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * GetCustomerFactory
     *
     * @return Customer
     */
    public function getCustomerFactory()
    {
        return $this->customerFactory->create();
    }

    /**
     * CreateCustomerInterface
     *
     * @return CustomerInterface
     */
    public function createCustomerInterface()
    {
        return $this->customerInterfaceFactory->create();
    }

    /**
     * CreateOrder
     *
     * @return Order
     */
    public function createOrder()
    {
        return $this->orderFactory->create();
    }
}

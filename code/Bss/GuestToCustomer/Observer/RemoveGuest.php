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
namespace Bss\GuestToCustomer\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Bss\GuestToCustomer;

class RemoveGuest implements ObserverInterface
{

    /**
     * Resource Guest
     * @var GuestToCustomer\Model\ResourceModel\Guest
     */
    protected $resourceGuest;

    /**
     * RemoveGuest constructor.
     * @param GuestToCustomer\Model\ResourceModel\Guest $resourceGuest
     */
    public function __construct(
        GuestToCustomer\Model\ResourceModel\Guest $resourceGuest
    ) {
        $this->resourceGuest = $resourceGuest;
    }

    /**
     * Isset Guest Email
     *
     * @param string $email
     * @return bool
     */
    protected function issetGuestEmail($email)
    {
        $check = false;

        if ($this->resourceGuest->existEmailGuest($email)) {
            $check = true;
        }

        return $check;
    }

    /**
     * Execute
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $customer = $observer->getEvent()->getData("customer");
        $emailCustomer = $customer->getEmail();
        if ($this->issetGuestEmail($emailCustomer)) {
            $where = [
                "email = ?" => $emailCustomer
            ];
            $this->resourceGuest->deleteGuest($where);
        }
    }
}

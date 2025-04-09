<?php

namespace Sam\CaptureAdminOrder\Observer;

use \Magento\Framework\Event;
use \Magento\Backend\Model\Auth\Session;
use \Magento\Framework\App;

/**
 * Class SetAdminUserToOrder
 * @package Sam\CaptureAdminOrder\Observer
 */
class SetAdminUserToOrder implements Event\ObserverInterface
{
    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var App\State
     */
    private $state;

    /**
     * SetAdminUserToOrder constructor.
     * @param Session $authSession
     * @param App\State $state
     */
    public function __construct(
        Session $authSession,
        App\State $state
    )
    {
        $this->authSession = $authSession;
        $this->state = $state;
    }

    /**
     * @param Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Event\Observer $observer)
    {
        if ($this->state->getAreaCode() == App\Area::AREA_ADMINHTML) {
            $order = $observer->getEvent()->getOrder();
            $order->setAdminUsername($this->getCurrentUser()->getUserName());
            $order->setAdminUserid($this->getCurrentUser()->getId());
        }

    }

    /**
     * @return \Magento\User\Model\User| array
     */
    private function getCurrentUser()
    {
        return $this->authSession->getUser();
    }
}

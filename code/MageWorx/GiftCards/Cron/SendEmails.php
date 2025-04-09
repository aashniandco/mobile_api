<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Cron;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use \Psr\Log\LoggerInterface;

class SendEmails
{
    /** @var EventManagerInterface */
    protected $eventManager;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * SendEmails constructor.
     *
     * @param EventManagerInterface $eventManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        EventManagerInterface $eventManager,
        LoggerInterface $logger
    ) {
        $this->eventManager = $eventManager;
        $this->logger       = $logger;
    }

    /**
     * Send emails for expired gift cards
     */
    public function SendExpiredEmail()
    {
        $this->eventManager->dispatch('mageworx_giftcards_expired');
        //$this->logger->info('Send Giftcard Expired Email');
    }

    /**
     * Send expiration alert emails
     */
    public function SendExpirationAlertEmail()
    {
        $this->eventManager->dispatch('mageworx_giftcards_expiration_alert');
        //$this->logger->info('Send Giftcard Expiration Alert Email');
    }

    /**
     * Send Emails
     */
    public function sendEmailOnDeliveryDate()
    {
        $this->eventManager->dispatch('mageworx_giftcards_send_using_delivery_date');
    }
}

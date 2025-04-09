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
namespace Bss\GuestToCustomer\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Magento\Email\Model\Template\SenderResolver;

class Email extends AbstractHelper
{
    /**
     * Helper Config Admin
     * @var ConfigAdmin
     */
    protected $helper;

    /**
     * Scope Config Interface
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * State Interface
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * Escaper
     * @var Escaper
     */
    protected $escaper;

    /**
     * Logger
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Transport Builder
     *
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var SenderResolver
     */
    protected $senderResolver;

    /**
     * Email constructor.
     * @param Context $context
     * @param ConfigAdmin $helper
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        Context $context,
        ConfigAdmin $helper,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        SenderResolver $senderResolver
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $context->getLogger();
        $this->senderResolver    = $senderResolver;
    }

    /**
     * Send Email Function
     *
     * @param string|array $receivers
     * @param string $emailTemplate
     * @param array $templateVar
     * @param int $storeCustomer
     * @return void
     */
    public function sendEmail($receivers, $emailTemplate, $templateVar, $storeCustomer)
    {
        try {
            error_log("creating cutomer Email");
            error_log($emailTemplate);
            error_log("creating cutomer Email 1");
            $configEnable = $this->helper->getConfigEnableEmail();
            if ($configEnable) {
                $sender = $this->senderResolver->resolve($this->helper->getConfigEmailSender());
                $this->inlineTranslation->suspend();

                $sender = [
                    'email' => $sender['email'],
                    'name'  => $sender['name']
                ];

                //Send Email
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier($emailTemplate)
                    ->setTemplateOptions(
                        [
                            'area' => Area::AREA_FRONTEND,
                            'store' => $storeCustomer,
                        ]
                    )
                    ->setTemplateVars($templateVar)
                    ->setFrom($sender)
                    ->addTo($receivers)
                    ->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}

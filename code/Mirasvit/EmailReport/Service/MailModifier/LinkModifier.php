<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-email-report
 * @version   2.0.13
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\EmailReport\Service\MailModifier;

use Magento\Store\Api\Data\StoreInterface;
use Mirasvit\Email\Api\Data\QueueInterface;
use Mirasvit\Email\Api\Service\Queue\MailModifierInterface;
use Mirasvit\EmailReport\Api\Service\StorageServiceInterface;
use Mirasvit\EmailDesigner\Service\TemplateEngine\Liquid\Variable\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class LinkModifier implements MailModifierInterface
{

    private $urlBuilder;

    private $context;

    private $storeManager;

    /**
     * LinkModifier constructor.
     *
     * @param UrlInterface          $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param Context               $context
     */
    public function __construct(
        UrlInterface          $urlBuilder,
        StoreManagerInterface $storeManager,
        Context               $context
    ) {
        $this->urlBuilder   = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->context      = $context;
    }

    /**
     * {@inheritDoc}
     */
    public function modifyContent(QueueInterface $queue, $content)
    {
        $params = [
            StorageServiceInterface::QUEUE_PARAM_NAME => $queue->getUniqHash(),
        ];

        if (!$store = $this->context->getStore()) {
            $store = $this->storeManager->getStore();
        }
        $storeUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_DIRECT_LINK);

        if (preg_match_all('/<a\s[^>]*href=([\"\']??)([^\" >]*?)\\1[^>]*>(.*)<\/a>/siU', $content, $matches)) {
            foreach ($matches[2] as $key => $url) {
                // modify only store urls
                if (!strstr($url, $storeUrl)) {
                    continue;
                }

                $newUrl = $this->createLink($store, $url, $params);

                if ($newUrl) {
                    $backendFrontName = $this->urlBuilder->getAreaFrontName();
                    if ($backendFrontName && strpos($newUrl, '/'.$backendFrontName.'/') !== false) {
                        $newUrl = str_replace('/'.$backendFrontName.'/', '/', $newUrl);
                    }

                    $from = $matches[0][$key];
                    $to = str_replace('href="' . $url . '"', 'href="' . $newUrl . '"', $from);

                    $content = str_replace($from, $to, $content);
                }
            }
        }

        return $content;
    }

    /**
     * @param StoreInterface $store
     * @param string         $url
     * @param array          $params
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createLink(StoreInterface $store, $url, array $params)
    {
        $params['_query'] = [
            'url' => base64_encode($url),
        ];

        return $store->getUrl('emailreport/track/click', $params);
    }
}

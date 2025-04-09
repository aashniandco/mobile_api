<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Plugin\Api;

use Magento\Quote\Api\Data\TotalSegmentExtensionFactory;
use Magento\Quote\Api\Data\TotalSegmentExtensionInterface;
use Magento\Quote\Api\Data\TotalSegmentInterface;
use Magento\Quote\Model\Cart\TotalsConverter as CartTotalsConverter;
use Magento\Quote\Model\Quote\Address\Total as AddressTotal;
use MageWorx\GiftCards\Serializer\SerializeJson;
use MageWorx\GiftCards\Model\Session as GiftCardsSession;

/**
 * Class AddGiftCardsTotalToQuoteTotals
 *
 * @package MageWorx\GiftCards\Plugin\Api
 */
class AddFrontOptionsToGiftCardsQuoteTotalPlugin
{
    /**
     * @var TotalSegmentExtensionFactory
     */
    private $totalSegmentExtensionFactory;

    /**
     * @var string
     */
    private $code;

    /**
     * @var SerializeJson
     */
    private $serializer;

    /**
     * @var GiftCardsSession
     */
    private $giftcardsSession;

    /**
     * AddGiftCardsTotalToQuoteTotals constructor.
     *
     * @param TotalSegmentExtensionFactory $totalSegmentExtensionFactory
     * @param SerializeJson $serializer
     * @param GiftCardsSession $giftcardsSession
     */
    public function __construct(
        TotalSegmentExtensionFactory $totalSegmentExtensionFactory,
        SerializeJson $serializer,
        GiftCardsSession $giftcardsSession
    ) {
        $this->totalSegmentExtensionFactory = $totalSegmentExtensionFactory;
        $this->serializer                   = $serializer;
        $this->giftcardsSession             = $giftcardsSession;
        $this->code                         = 'mageworx_giftcards';
    }

    /**
     * Add mageworx_giftcards segment to the summary
     *
     * @param CartTotalsConverter $subject
     * @param TotalSegmentInterface[] $result
     * @param AddressTotal[] $addressTotals
     * @return TotalSegmentInterface[]
     * @throws \Exception
     */
    public function afterProcess(CartTotalsConverter $subject, $result, $addressTotals = [])
    {
        if (empty($this->giftcardsSession->getFrontOptions())) {
            return $result;
        }

        if (!isset($result[$this->code])) {
            return $result;
        }

        $frontOptions = array_values($this->giftcardsSession->getFrontOptions());

        /** @var TotalSegmentExtensionInterface $totalSegmentExtension */
        $totalSegmentExtension = $this->totalSegmentExtensionFactory->create();

        $totalSegmentExtension->setFrontOptions($this->serializer->serialize($frontOptions));
        $result[$this->code]->setExtensionAttributes($totalSegmentExtension);

        return $result;
    }
}

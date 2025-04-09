<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Plugin\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use MageWorx\GiftCards\Model\Session;
use MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory;
use MageWorx\GiftCards\Api\Data\GiftCardsInterface;
use MageWorx\GiftCards\Model\GiftCards;
use Magento\Framework\ObjectManagerInterface;

class ValidateGiftCardsBeforePlaceOrderPlugin
{
    /**
     * @var Session
     */
    protected $giftCardsSession;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * ValidateGiftCardsBeforePlaceOrderPlugin constructor.
     *
     * @param Session $giftCardsSession
     * @param CollectionFactory $collectionFactory
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Session $giftCardsSession,
        CollectionFactory $collectionFactory,
        ObjectManagerInterface $objectManager
    ) {
        $this->giftCardsSession  = $giftCardsSession;
        $this->collectionFactory = $collectionFactory;
        $this->objectManager     = $objectManager;
    }

    /**
     * @param CartManagementInterface $subject
     * @param callable $proceed
     * @param int $cartId
     * @param PaymentInterface|null $paymentMethod
     * @return int
     * @throws LocalizedException
     * @throws \Exception
     */
    public function aroundPlaceOrder(
        CartManagementInterface $subject,
        callable $proceed,
        $cartId,
        PaymentInterface $paymentMethod = null
    ) {
        $giftCardsIds = $this->giftCardsSession->getGiftCardsIds();

        if ($this->giftCardsSession->getActive() && $giftCardsIds) {
            $frontOptions = $this->giftCardsSession->getFrontOptions();
            $ids          = [];

            foreach ($giftCardsIds as $giftCardId => $giftCardData) {
                if ($frontOptions[$giftCardId]['applied'] > 0) {
                    $ids[] = $giftCardId;
                }
            }

            if ($ids) {
                $collection = $this->collectionFactory->create();
                $collection
                    ->addFieldToSelect(GiftCardsInterface::CARD_ID)
                    ->addFieldToFilter(
                        GiftCardsInterface::CARD_ID,
                        ['in' => $ids]
                    )
                    ->addFieldToFilter(GiftCardsInterface::CARD_STATUS, GiftCards::STATUS_IN_PROGRESS);

                if ($collection->getSize()) {
                    throw new LocalizedException(
                        __('The specified gift card code is used at the moment. Please try again later.')
                    );
                }

                $resource = $this->objectManager->create(\Magento\Framework\App\ResourceConnection::class);

                /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
                $connection = $resource->getConnection();
                $connection->update(
                    $collection->getTable('mageworx_giftcards_card'),
                    [GiftCardsInterface::CARD_STATUS => GiftCards::STATUS_IN_PROGRESS],
                    $connection->prepareSqlCondition(GiftCardsInterface::CARD_ID, ['in' => $ids])
                );

                try {
                    return $proceed($cartId, $paymentMethod);
                } catch (\Exception $e) {
                    if ($this->giftCardsSession->getActive()) {
                        $connection->update(
                            $collection->getTable('mageworx_giftcards_card'),
                            [GiftCardsInterface::CARD_STATUS => GiftCards::STATUS_ACTIVE],
                            $connection->prepareSqlCondition(GiftCardsInterface::CARD_ID, ['in' => $ids])
                        );
                    }

                    throw $e;
                }
            }
        }

        return $proceed($cartId, $paymentMethod);
    }
}

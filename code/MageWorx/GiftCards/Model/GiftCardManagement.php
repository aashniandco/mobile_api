<?php
/**
 *
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace MageWorx\GiftCards\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Sales\Api\OrderRepositoryInterface;
use MageWorx\GiftCards\Helper\Data as Helper;
use Magento\Quote\Api\CartRepositoryInterface as CartRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

class GiftCardManagement implements \MageWorx\GiftCards\Api\GiftCardManagementInterface
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var GiftCardsRepository
     */
    private $giftCardsRepository;

    /**
     * @var CartRepository
     */
    private $cartRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var QuoteGiftCardsDescription
     */
    protected $quoteGiftCardsDescription;

    /**
     * GiftCardManagement constructor.
     *
     * @param Helper $helper
     * @param GiftCardsRepository $giftCardsRepository
     * @param CartRepository $cartRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param QuoteGiftCardsDescription $quoteGiftCardsDescription
     */
    public function __construct(
        Helper $helper,
        GiftCardsRepository $giftCardsRepository,
        CartRepository $cartRepository,
        OrderRepositoryInterface $orderRepository,
        QuoteGiftCardsDescription $quoteGiftCardsDescription
    ) {
        $this->helper                    = $helper;
        $this->giftCardsRepository       = $giftCardsRepository;
        $this->cartRepository            = $cartRepository;
        $this->orderRepository           = $orderRepository;
        $this->quoteGiftCardsDescription = $quoteGiftCardsDescription;
    }

    /**
     * @param int $cartId
     * @param string $giftCardCode
     * @param bool $isAdmin
     * @return bool
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function applyToCart(int $cartId, string $giftCardCode, bool $isAdmin = false): bool
    {
        /** @var \MageWorx\GiftCards\Model\GiftCards $giftCard */
        $giftCard = $this->giftCardsRepository->getByCode($giftCardCode);

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $isAdmin ? $this->cartRepository->get($cartId) : $this->cartRepository->getActive($cartId);

        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %cartId doesn\'t contain products', $cartId));
        }

        if ($giftCard->isValid(
            true,
            true,
            $quote->getStoreId(),
            $quote->getCustomerGroupId(),
            true
        )) {
            try {
                $cardCodes = $this->quoteGiftCardsDescription->getCodes(
                    (string)$quote->getMageworxGiftcardsDescription()
                );

                if (in_array($giftCard->getCardCode(), $cardCodes)) {
                    throw new LocalizedException(__('This Gift Card is already in the Quote.'));
                }

                $cardCodes[] = $giftCard->getCardCode();

                $quote->setMageworxGiftcardsDescription($this->quoteGiftCardsDescription->getDescription($cardCodes));
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->collectTotals();
                $this->cartRepository->save($quote);
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('Could not add gift card code'));
            }

            return true;
        }

        return false;
    }

    /**
     * @param int $cartId
     * @param string $giftCardCode
     * @param bool $isAdmin
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function removeFromCart(int $cartId, string $giftCardCode, bool $isAdmin = false): bool
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $isAdmin ? $this->cartRepository->get($cartId) : $this->cartRepository->getActive($cartId);

        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %cartId doesn\'t contain products', $cartId));
        }

        try {
            $cardCodes = $this->quoteGiftCardsDescription->getCodes((string)$quote->getMageworxGiftcardsDescription());
            $key       = array_search($giftCardCode, $cardCodes);

            if ($key !== false) {
                unset($cardCodes[$key]);

                $quote->setMageworxGiftcardsDescription($this->quoteGiftCardsDescription->getDescription($cardCodes));
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->collectTotals();
                $this->cartRepository->save($quote);
            }
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete gift card from quote'));
        }

        return true;
    }

    /**
     * @param int $giftCardId
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function sendEmailWithGiftCard(int $giftCardId): bool
    {
        /** @var \MageWorx\GiftCards\Model\GiftCards $giftCard */
        $giftCard = $this->giftCardsRepository->get($giftCardId);
        error_log("Gift Card images::".$giftCard->getImageUrl()."|".json_encode($giftCard->getData()));
        if ($giftCard->getMailToEmail()) {
            try {
                if ($giftCard->getOrderId()) {
                    $order = $this->orderRepository->get($giftCard->getOrderId());
                    $giftCard->send($order);
                } else {
                    $giftCard->sendNoOrder();
                }

                if ($giftCard->getDeliveryStatus() != GiftCards::STATUS_DELIVERED) {
                    $giftCard->setDeliveryStatus(GiftCards::STATUS_DELIVERED);
                    $this->giftCardsRepository->save($giftCard);
                }
            } catch (LocalizedException $e) {
                throw $e;
            } catch (\Exception $e) {
                throw new \Exception(__('Something went wrong while sending the Gift Card.')->render());
            }
        } else {
            throw new ValidatorException(__('Unable to send Gift Card. Try to fill "To Email" field.'));
        }

        return true;
    }
}

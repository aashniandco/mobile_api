<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use MageWorx\GiftCards\Api\Data\GiftCardsInterface;
use MageWorx\GiftCards\Api\Data\GiftCardsOrderInterface;

class TransferDataToGiftCardOrderTable implements DataPatchInterface
{
    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup      = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->setGiftcardCodeValuesInGiftcardOrder();
        $this->setOrderIncrementIdValuesInGiftcardOrder();
    }

    private function setGiftcardCodeValuesInGiftcardOrder()
    {
        $giftcardOrderTableName = $this->moduleDataSetup->getTable('mageworx_giftcard_order');

        $select = $this->moduleDataSetup->getConnection()->select()
                        ->distinct(true)
                        ->from(
                            $giftcardOrderTableName,
                            GiftCardsOrderInterface::GIFTCARD_ID
                        )
                        ->where(GiftCardsOrderInterface::GIFTCARD_CODE . ' IS NULL');

        $giftcardIds = $this->moduleDataSetup->getConnection()->fetchCol($select);

        if (!empty($giftcardIds)) {
            $select = $this->moduleDataSetup->getConnection()->select()
                            ->from(
                                $this->moduleDataSetup->getTable('mageworx_giftcards_card'),
                                [GiftCardsInterface::CARD_ID, GiftCardsInterface::CARD_CODE]
                            )
                            ->where(
                                GiftCardsInterface::CARD_ID . ' IN(?)',
                                $giftcardIds
                            );

            $cardCodes = $this->moduleDataSetup->getConnection()->fetchPairs($select);

            foreach ($giftcardIds as $giftcardId) {
                if (empty($cardCodes[$giftcardId])) {
                    continue;
                }

                $where = GiftCardsOrderInterface::GIFTCARD_ID . ' = ' . $giftcardId;
                $bind  = [GiftCardsOrderInterface::GIFTCARD_CODE => $cardCodes[$giftcardId]];

                $this->moduleDataSetup->getConnection()->update($giftcardOrderTableName, $bind, $where);
            }
        }
    }

    private function setOrderIncrementIdValuesInGiftcardOrder()
    {
        $giftcardOrderTableName = $this->moduleDataSetup->getTable('mageworx_giftcard_order');

        $select = $this->moduleDataSetup->getConnection()->select()
                        ->distinct(true)
                        ->from(
                            $giftcardOrderTableName,
                            GiftCardsOrderInterface::ORDER_ID
                        )
                        ->where(GiftCardsOrderInterface::ORDER_INCREMENT_ID . ' IS NULL');

        $orderIds = $this->moduleDataSetup->getConnection()->fetchCol($select);

        if (!empty($orderIds)) {
            $select = $this->moduleDataSetup->getConnection()->select()
                            ->from(
                                $this->moduleDataSetup->getTable('sales_order'),
                                ['entity_id', 'increment_id']
                            )
                            ->where(
                                'entity_id IN(?)',
                                $orderIds
                            );

            $orderIncrementIds = $this->moduleDataSetup->getConnection()->fetchPairs($select);

            foreach ($orderIds as $orderId) {
                if (empty($orderIncrementIds[$orderId])) {
                    continue;
                }

                $where = GiftCardsOrderInterface::ORDER_ID . ' = ' . $orderId;
                $bind  = [GiftCardsOrderInterface::ORDER_INCREMENT_ID => $orderIncrementIds[$orderId]];

                $this->moduleDataSetup->getConnection()->update($giftcardOrderTableName, $bind, $where);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}

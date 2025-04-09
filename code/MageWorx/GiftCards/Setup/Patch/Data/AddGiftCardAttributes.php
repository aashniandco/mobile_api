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

class AddGiftCardAttributes implements DataPatchInterface
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
        $categorySetup         = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityTypeId          = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $defaultAttributeSetId = $categorySetup->getDefaultAttributeSetId(Product::ENTITY);
        $attributeGroupName    = 'Gift Card Information';
        $categorySetup->addAttributeGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $categorySetup->getAttributeSetId($entityTypeId, $defaultAttributeSetId),
            $attributeGroupName,
            100
        );

        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mageworx_gc_type',
            [
                'label'            => 'Giftcards Type',
                'group'            => $attributeGroupName,
                'required'         => true,
                'visible_on_front' => true,
                'apply_to'         => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                'input'            => 'select',
                'sort_order'       => 34,
                'source'           => \MageWorx\GiftCards\Model\GiftCards\Source\Types::class,
            ]
        );

        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mageworx_gc_additional_price',
            [
                'label'            => 'Predefined Prices',
                'group'            => $attributeGroupName,
                'required'         => false,
                'visible_on_front' => false,
                'apply_to'         => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                'note'             => 'List here possible gift card prices to be selected from the dropdown on the frontend. ' .
                    'Separate them by semicolon. Predefined Prices drop-down is displayed only if the price is equal to "0".',
                'sort_order'       => 35,
                'backend'          => \MageWorx\GiftCards\Model\GiftCards\Backend\AdditionalPrice::class,
                'user_defined'     => false,
            ]
        );

        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mageworx_gc_customer_groups',
            [
                'label'            => 'Available for Customer Groups',
                'group'            => $attributeGroupName,
                'input'            => 'multiselect',
                'required'         => true,
                'visible_on_front' => false,
                'apply_to'         => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                'sort_order'       => 36,
                'type'             => 'text',
                'system'           => 0,
                'backend'          => \MageWorx\GiftCards\Model\GiftCards\Backend\CustomerGroups::class,
                'source'           => \MageWorx\GiftCards\Model\GiftCards\Source\CustomerGroups::class,
                'user_defined'     => false
            ]
        );

        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mageworx_gc_lifetime_value',
            [
                'label'            => 'Expiration Period',
                'group'            => $attributeGroupName,
                'required'         => false,
                'visible_on_front' => false,
                'apply_to'         => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                'sort_order'       => 37,
                'user_defined'     => false,
            ]
        );

        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mageworx_gc_allow_open_amount',
            [
                'label'            => 'Allow Open Amount',
                'group'            => $attributeGroupName,
                'required'         => false,
                'visible_on_front' => false,
                'type'             => 'int',
                'input'            => 'boolean',
                'source'           => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'apply_to'         => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                'sort_order'       => 38,
                'user_defined'     => false,
            ]
        );

        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mageworx_gc_open_amount_min',
            [
                'label'            => 'Open Amount Min Value',
                'group'            => $attributeGroupName,
                'required'         => false,
                'visible_on_front' => false,
                'apply_to'         => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                'sort_order'       => 39,
                'user_defined'     => false,
            ]
        );

        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mageworx_gc_open_amount_max',
            [
                'label'            => 'Open Amount Max Value',
                'group'            => $attributeGroupName,
                'required'         => false,
                'visible_on_front' => false,
                'apply_to'         => \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE,
                'sort_order'       => 40,
                'user_defined'     => false,
            ]
        );
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

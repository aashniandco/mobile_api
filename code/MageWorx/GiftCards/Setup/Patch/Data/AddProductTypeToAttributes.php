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

class AddProductTypeToAttributes implements DataPatchInterface
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
        $categorySetup   = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityTypeId    = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $defaultEntities = $categorySetup->getDefaultEntities();

        foreach ($defaultEntities['catalog_product']['attributes'] as $code => $attribute) {
            $applyTo = explode(',', $categorySetup->getAttribute($entityTypeId, $code, 'apply_to'));

            if (!in_array(\MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE, $applyTo)
                && in_array('simple', $applyTo)
            ) {
                $applyTo[] = \MageWorx\GiftCards\Model\Product\Type\GiftCards::TYPE_CODE;
                $categorySetup->updateAttribute($entityTypeId, $code, 'apply_to', implode(',', $applyTo));
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

<?php
/**
* Copyright © 2016 SW-THEMES. All rights reserved.
*/

namespace MGS\Mpanel\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
	/**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;
	
    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CustomerSetupFactory $customerSetupFactory)
    {
		$this->customerSetupFactory = $customerSetupFactory;
    }
    
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
		
		if (version_compare($context->getVersion(), '1.0.0', '<=')) {
            $entityAttributes = [
				'customer' => [
					'is_builder_account' => [
						'is_user_defined' => false,
						'is_used_in_grid' => false,
						'is_visible_in_grid' => false,
						'is_filterable_in_grid' => false,
						'is_searchable_in_grid' => false,
					]
				]
			];
			$this->upgradeAttributes($entityAttributes, $customerSetup);
        }
        
        $setup->endSetup();
    }
	
	/**
     * @param array $entityAttributes
     * @param CustomerSetup $customerSetup
     * @return void
     */
    protected function upgradeAttributes(array $entityAttributes, \Magento\Customer\Setup\CustomerSetup $customerSetup)
    {
        foreach ($entityAttributes as $entityType => $attributes) {
            foreach ($attributes as $attributeCode => $attributeData) {
                $attribute = $customerSetup->getEavConfig()->getAttribute($entityType, $attributeCode);
                foreach ($attributeData as $key => $value) {
                    $attribute->setData($key, $value);
                }
                $attribute->save();
            }
        }
    }
}

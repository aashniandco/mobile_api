<?php 
	namespace AttributesCustomade\CatalCateg\Setup;
	
	use Magento\Eav\Setup\EavSetupFactory;
	use Magento\Catalog\Setup\CategorySetupFactory;
	use Magento\Framework\Setup\ModuleContextInterface;
	use Magento\Framework\Setup\ModuleDataSetupInterface;
	use Magento\Framework\Setup\UpgradeDataInterface;
	use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
	class UpgradeData implements UpgradeDataInterface{
		
		/**
		* @var EavSetupFactory
		**/
		private $eavSetupFactory;

		/**
		* @var CategorySetupFactory
		**/
		private $categorySetupFactory;

		public function __construct(
			EavSetupFactory $eavSetupFactory,
			CategorySetupFactory $categorySetupFactory
		){
			$this->eavSetupFactory      = $eavSetupFactory;
			$this->categorySetupFactory = $categorySetupFactory;
		}
		public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context){
			
			$setup->startSetup();
			
			$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
			$categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
			
			$eavSetup->AddAttribute(
				\Magento\Catalog\Model\Category::ENTITY,
				'show_in_filters',
				[
					'type' => 'int',
					'label' => 'Show in Filters',
					'input' => 'boolean',
					'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
					'global' => ScopedAttributeInterface::SCOPE_STORE,
					'visible' => true,
					'required' => 'false',
					'default' => '0',
					'group' => '',
				]
			);
	        $eavSetup->addAttribute(
	            \Magento\Catalog\Model\Category::ENTITY,
	            'contextual_footer',
	            [
	                'type' => 'text',
	                'label' => 'Contextual Footer',
	                'input' => 'textarea',
	                'required' => false,
	                'sort_order' => 100,
	                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
	                'group' => '',
	            ]
	        );
			$setup->endSetup();
		}
	}
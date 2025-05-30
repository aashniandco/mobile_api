<?php

namespace Mec\Shipitem\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '1.0.0') < 0){

				
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$salesSetup = $objectManager->create('Magento\Sales\Setup\SalesSetup');
				
                                $salesSetup->addAttribute('order_item', 'itemstatus', ['type' =>'int']);
                                $salesSetup->addAttribute('order_item', 'statusdate', ['type' =>'varchar']);
                                $salesSetup->addAttribute('order_item', 'tracking', ['type' =>'varchar']);
				$salesSetup->addAttribute('order_item', 'follow_up', ['type' =>'int']);
				$quoteSetup = $objectManager->create('Magento\Quote\Setup\QuoteSetup');
				
				

		}

    }
}

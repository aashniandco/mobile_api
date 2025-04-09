<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backup types option array
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace MGS\Mmegamenu\Model\Grid;

class MegaMenu implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
		$this->_objectManager = $objectManager;
    }
	
	public function getModel(){
		return $this->_objectManager->create('MGS\Mmegamenu\Model\Mmegamenu');
	}

    /**
     * Return backup types array
     * @return array
     */
    public function toOptionArray()
    {
		$collection = $this->getModel()->getCollection();
		$result = [];
        $counter = 0;
		if(count($collection)>0){
			foreach($collection as $menu){
				$result[$menu->getId()]['title'] = $menu->getTitle();
                $result[$menu->getId()]['html'] = $menu->getData('static_content');
                $result[$menu->getId()]['position'] = $menu->getData('position');
			}
		}
		return $result;
    }
}

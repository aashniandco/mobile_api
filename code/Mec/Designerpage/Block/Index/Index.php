<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mec\Designerpage\Block\Index;

class Index extends \Magento\Framework\View\Element\Template
{
protected $categoryRepository;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
	\Magento\Framework\View\Element\Template\Context $context,
	\Magento\Catalog\Model\CategoryRepository $categoryRepository,
        array $data = []
    ) {
	$this->categoryRepository = $categoryRepository;
        parent::__construct($context, $data);
    }
	public function getDesignerCategories()
	{
		$parent_category_id = 1375;
		$categoryObj = $this->categoryRepository->get($parent_category_id);
		$subcategories = $categoryObj->getChildrenCategories()->addAttributeToSort('name', 'asc');
		return $subcategories;
	}
}


<?php

namespace Mec\ProductTag\Block\Magento\Catalog\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\Product;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
	protected $_customerSession;
	protected $categoryFactory;
	protected $resourceConnection;

	/**
	 * ListProduct constructor.
	 * @param \Magento\Catalog\Block\Product\Context $context
	 * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
	 * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
	 * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
	 * @param \Magento\Framework\Url\Helper\Data $urlHelper
	 * @param Helper $helper
	 * @param array $data
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
	 */
	public function __construct(
		\Magento\Catalog\Block\Product\Context $context,
		\Magento\Framework\Data\Helper\PostHelper $postDataHelper,
		\Magento\Catalog\Model\Layer\Resolver $layerResolver,
		\Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
		\Magento\Framework\Url\Helper\Data $urlHelper,
		ResourceConnection $resourceConnection,
		array $data = [],
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory
	) {
		$this->_customerSession = $customerSession;
		$this->categoryFactory = $categoryFactory;
		$this->urlHelper = $urlHelper;
		$this->resourceConnection = $resourceConnection;
		parent::__construct(
			$context,
			$postDataHelper,
			$layerResolver,
			$categoryRepository,
			$urlHelper,
			$data
		);

	}

	public function getTagTitle($tag_id)
	{
		$connection = $this->resourceConnection->getConnection();
		$tableName = $connection->getTableName('lof_producttags_tag');
		$sql = "SELECT tag_title FROM $tableName WHERE tag_id = '$tag_id' LIMIT 1";
		$result = $connection->fetchOne($sql);
		return $result;
	}

	public function getTag($id){
		$connection = $this->resourceConnection->getConnection();
		$tableName = $connection->getTableName('lof_producttags_product');
		$sql = "SELECT tag_id FROM $tableName WHERE product_id = '$id' LIMIT 1";
		$result = $connection->fetchOne($sql);
		$tag_title = $this->getTagTitle($result);
		return $tag_title;
	} 


}

	
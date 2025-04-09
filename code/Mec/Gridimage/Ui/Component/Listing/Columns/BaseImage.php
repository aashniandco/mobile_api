<?php

namespace Mec\Gridimage\Ui\Component\Listing\Columns;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class BaseImage
 *
 * @api
 * @since 100.0.2
 */
class BaseImage extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'image';

    const ALT_FIELD = 'name';

    private $imageHelper;
    private $urlBuilder;
    private $productRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        ProductRepositoryInterface $productRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->productRepository = $productRepository;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $product = new \Magento\Framework\DataObject($item);
        //        $product = $this->productRepository->getById($item['entity_id']);
		$imageHelper = $this->imageHelper->init($product, 'product_listing_image');
                $item[$fieldName . '_src'] = $imageHelper->getUrl();
                $item[$fieldName . '_alt'] = $this->getAlt($item) ?: $imageHelper->getLabel();
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'catalog/product/edit',
                    ['id' => $product->getEntityId(), 'store' => $this->context->getRequestParam('store')]
                );
                $origImageHelper = $this->imageHelper->init($product, 'product_listing_image_preview');
                $item[$fieldName . '_orig_src'] = $origImageHelper->getUrl();
            }
        }

        return $dataSource;
    }

    /**
     * Get Alt
     *
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return $row[$altField] ?? null;
    }
}

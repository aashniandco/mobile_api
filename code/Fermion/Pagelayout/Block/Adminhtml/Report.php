<?php
namespace Fermion\Pagelayout\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\RequestInterface;

class Report extends Template
{
    protected $eavConfig;

    public function __construct(
        Template\Context $context,
        \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    ) {
        $this->eavConfig = $eavConfig;
        parent::__construct($context, $data);
    }

    /**
     * Get the attribute options for a product
     *
     * @return array
     */
    public function getMultiselectAttributeOptions($attributeCode)
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions(false);
        return $options;
    }
}

?>
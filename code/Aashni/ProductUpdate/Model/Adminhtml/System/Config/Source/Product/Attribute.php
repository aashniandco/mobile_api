<?php
namespace  Aashni\ProductUpdate\Model\Adminhtml\System\Config\Source\Product ;

use Magento\Framework\Data\OptionSourceInterface;
use \Magento\Eav\Model\Config;

class Attribute implements OptionSourceInterface
{
    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @param Config $eavConfig
     */
    public function __construct(
        Config $eavConfig
    ) {
        $this->_eavConfig = $eavConfig;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $designerOptions = $this->getDesignerOption();
        return $designerOptions;
    }

    /**
     * Get Designer Option
     *
     * @return array
     */
    protected function getDesignerOption()
    {
        $attribute = $this->_eavConfig
        ->getAttribute('catalog_product', 'designer');
        $options = $attribute->getSource()->getAllOptions();
        $optionsExists = [];
        foreach ($options as $option) {
            $optionsExists[] = $option['label'];
        }
        return $options;
    }
}

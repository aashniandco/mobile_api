<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Swatches\Model\Plugin;

use Amasty\Shopby\Model\Source\DisplayMode;
use Magento\Swatches\Model\Swatch;
use Magento\LayeredNavigation\Block\Navigation\FilterRenderer;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;

class FilterRendererPlugin
{
    /**
     * @var \Amasty\Shopby\Helper\FilterSetting
     */
    private $filterSetting;

    public function __construct(\Amasty\Shopby\Helper\FilterSetting $filterSetting)
    {
        $this->filterSetting = $filterSetting;
    }

    /**
     * @param $subject
     * @param FilterRenderer $filterRenderer
     * @param $closure
     * @param FilterInterface $filter
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeAroundRender($subject, FilterRenderer $filterRenderer, $closure, FilterInterface $filter)
    {
        if ($filter->hasAttributeModel()) {
            $displayMode = $this->filterSetting->getSettingByLayerFilter($filter)->getDisplayMode();
            if (in_array($displayMode, [DisplayMode::MODE_DEFAULT, DisplayMode::MODE_DROPDOWN])) {
                $filter->getAttributeModel()->setData(
                    Swatch::SWATCH_INPUT_TYPE_KEY,
                    Swatch::SWATCH_INPUT_TYPE_DROPDOWN
                );
            }
        }

        return [$filterRenderer, $closure, $filter];
    }
}

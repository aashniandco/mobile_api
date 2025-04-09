<?php
namespace Fermion\LoyaltyPoint\Block\Adminhtml\Column\Renderer;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\App\ObjectManager;

class Link extends AbstractRenderer {
    /**
     *
     * @param  \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row) {
        $request    = ObjectManager::getInstance()->get(\Magento\Framework\App\Request\Http::class);
        $frontname  = $request->getRouteName();
        $controller = $request->getControllerName();                
        $value =  $row->getData($this->getColumn()->getIndex());                
        $actionUrl  = "/admin/$frontname/$controller/edit/id/$value";
        $output = '<a href="' . $actionUrl . '" target="_blank" title="' . $actionUrl . '">Edit</a>';
        return $output;
    }
}
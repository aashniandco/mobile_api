<?php

namespace Mec\Enquire\Block\Index;

use Magento\Framework\View\Element\Template;

class Index extends Template {


    public function __construct(
    	\Magento\Catalog\Block\Product\Context $context, array $data = []) {

        parent::__construct($context, $data);

    }


    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    

}
<?php

namespace Mec\SessionConfig\Plugin\View\Element\Js;

use Magento\Framework\View\Element\Js\Cookie;

class ManagePath
{
    public function afterGetPath(\Magento\Framework\View\Element\Js\Cookie $subject, $path)
    {
        
        if (preg_match('/SameSite/', $path)) {
             $path_array = explode(';', $path);
             $path = $path_array[0];
        }
        
        return $path;
    }
}

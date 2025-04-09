<?php 

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Fermion\JCVersioning\Block\View\Page\Config;

use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Page\Config;

class Renderer extends \Magento\Framework\View\Page\Config\Renderer
{
//     /**
//     * @var array
//     */
//    protected $assetTypeOrder = ['css', 'ico', 'js'];
//
//
    protected function getAssetTemplate($contentType, $attributes)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $versionString = $objectManager->create('Fermion\JCVersioning\Helper\Data')->getVersionString();
        if (strpos($attributes, 'canonical') !== false) {
            $versionString = '';
        }
		//$versionString = '?v=1.1.10.162inter';
        switch ($contentType) {
            case 'js':
                $groupTemplate = '<script ' . $attributes . ' src="%s' . $versionString . '"></script>' . "\n";
                break;

            case 'css':
            default:
                $groupTemplate = '<link ' . $attributes . ' href="%s' . $versionString . '" />' . "\n";
                break;
        }
        return $groupTemplate;
    }
}

<?php
namespace Mec\CountryAutoSelect\Plugin\Checkout\Block\Cart;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Amasty\Geoip\Model\Geolocation;

class LayoutProcessor
{
    private $remoteAddress;
    private $geolocation;

    public function __construct(
        RemoteAddress $remoteAddress,
        Geolocation $geolocation
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->geolocation = $geolocation;
        //parent::__construct($data );
    }

    public function getCountryCode(){
        $currentIp = $this->remoteAddress->getRemoteAddress();
        $location = $this->geolocation->locate($currentIp);
        $country = $location->getCountry();
        return $country;
    }

    public function afterProcess(
        \Magento\Checkout\Block\Cart\LayoutProcessor $subject,
        $jsLayout
    ) {
        //$selectedCountry = 'GB';
        $selectedCountry = $this->getCountryCode();
        if (isset($jsLayout['components']['checkoutProvider']['dictionaries'])) {

            foreach ($jsLayout['components']['checkoutProvider']['dictionaries']['country_id'] as &$country) {
                if ($country['value'] == $selectedCountry) {
                    $country['is_default'] = 1;
                } else {
                    if (isset($country['is_default'])) {
                        unset($country['is_default']);
                    }
                }
            }
        }
        if (isset($jsLayout['components']['block-summary']['children']['block-shipping']['children']['address-fieldsets'])) {
            $jsLayout['components']['block-summary']['children']['block-shipping']['children']['address-fieldsets']['children']['country_id']['value'] = $selectedCountry;
        }

        return $jsLayout;
    }
}
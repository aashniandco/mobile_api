<?php

namespace Mec\Enquire\Block;

use Magento\Catalog\Block\Product\View;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Session\SessionManager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Form\FormKey;

class EnquireFormPdp extends View {
	protected $session;
	protected $formKey;

	public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = [],
        SessionManager $session,
        FormKey $formKey

    ) {
        $this->session = $session;
        $this->formKey = $formKey;
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency, 
            $data
        );
    }

    public function getCountryOptions()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get("\Magento\Framework\App\ResourceConnection");
        $connection = $resource->getConnection();

        $sql = "SELECT DISTINCT a.name, a.dial_code FROM msp_tfa_country_codes a ORDER BY a.name;";

        $contry  = $connection->fetchAll($sql);
        return $contry;
    }

    public function getFormKeyHtml()
    {
        return '<input type="hidden" name="form_key" value="' . $this->formKey->getFormKey() . '">';
    }
    

}

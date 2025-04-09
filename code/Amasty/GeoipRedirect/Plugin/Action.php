<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GeoipRedirect
 */


namespace Amasty\GeoipRedirect\Plugin;

use Amasty\Base\Model\MagentoVersion;
use Amasty\Base\Model\Serializer;
use Amasty\Geoip\Model\Geolocation;
use Amasty\GeoipRedirect\Helper\Data;
use Amasty\GeoipRedirect\Model\Source\Logic;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Cms\Helper\Page as PageHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store as StoreModel;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class Action
{
    const LOCAL_IP = '127.0.0.1';
    const URL_TRIM_CHARACTER = '/';

    protected $redirectAllowed = false;

    protected $addressPath = [
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR'
    ];

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Data
     */
    private $geoipHelper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Geolocation
     */
    private $geolocation;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var StoreCookieManagerInterface
     */
    private $storeCookieManager;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var Http
     */
    private $response;

    /**
     * @var SessionManagerInterface $sessionManager
     */
    private $sessionManager;

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    public function __construct(
        RemoteAddress $remoteAddress,
        ScopeConfigInterface $scopeConfig,
        Data $geoipHelper,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        Geolocation $geolocation,
        Session $customerSession,
        StoreCookieManagerInterface $storeCookieManager,
        RedirectFactory $resultRedirectFactory,
        Http $response,
        SessionManagerInterface $sessionManager,
        ResolverInterface $resolver,
        Serializer $serializer,
        UrlFinderInterface $urlFinder,
        MagentoVersion $magentoVersion
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->scopeConfig = $scopeConfig;
        $this->geoipHelper = $geoipHelper;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->geolocation = $geolocation;
        $this->customerSession = $customerSession;
        $this->storeCookieManager = $storeCookieManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->response = $response;
        $this->sessionManager = $sessionManager;
        $this->resolver = $resolver;
        $this->serializer = $serializer;
        $this->urlFinder = $urlFinder;
        $this->magentoVersion = $magentoVersion;
    }

    /**
     * @param FrontControllerInterface $subject
     * @param \Closure $proceed
     * @param RequestInterface $request
     * @return Redirect
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundDispatch(
        FrontControllerInterface $subject,
        \Closure $proceed,
        RequestInterface $request
    ) {
        $currentStoreId = $this->storeManager->getStore()->getId();
        $scopeStores = ScopeInterface::SCOPE_STORES;
        if (!$this->scopeConfig->isSetFlag('amgeoipredirect/general/enable', $scopeStores, $currentStoreId)
            || $this->isNeedToProceed($request)
        ) {
            return $proceed($request);
        }

        $session = $this->sessionManager;
        $countRedirectStore = $countRedirectCurrency = $countRedirectUrl = 0;
        $isNotFirstTime = null;
        $changeCurrency = false;

        if ($this->scopeConfig->getValue('amgeoipredirect/restriction/first_visit_redirect')) {
            // session value getters should be before processed request, otherwise will return null with FPC enabled
            $isNotFirstTime = $session->getIsNotFirstTime();
            $countRedirectStore = $session->getAmYetRedirectStore();
            $countRedirectCurrency = $session->getAmYetRedirectCurrency();
            $countRedirectUrl = $session->getAmYetRedirectUrl();
            $session->setIsNotFirstTime(1);
        }
        $this->applyLogic($request);
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->scopeConfig->getValue('amgeoipredirect/general/enable', $scopeStores, $currentStoreId)
            || !$this->redirectAllowed
        ) {
            return $proceed($request);
        }

        $currentIp = $this->getCurrentIp($request);
        if ($this->isIpBlock($currentIp)) {
            $websiteId = $this->storeManager->getWebsite()->getId();
            $page = $this->scopeConfig->getValue(
                'amgeoipredirect/restrict_by_ip/cms_to_show',
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
            $url = $this->urlBuilder->getUrl($page);
            if (rtrim($this->parseUrl($url)['path'], '/')
                !== rtrim($this->parseUrl($this->urlBuilder->getCurrentUrl())['path'], '/')
            ) {
                $this->response->setNoCacheHeaders();
                return $resultRedirect->setUrl($url);
            }
        }
        $location = $this->geolocation->locate($currentIp);
        if(isset($_GET['ci']) && $_GET['ci'] != ''){
            $country = $_GET['ci'];
        }
        else if(isset($_COOKIE['currency'])){
            $currencyCode = $_COOKIE['currency'];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $currency = $objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($currencyCode);

            if($currency->getId()){
                $country = $currency->getCountry();
            }
        }
        else{
            
            $country = $location->getCountry();
        }
        

        if (!$countRedirectCurrency
            && !$isNotFirstTime
            && $this->scopeConfig->getValue('amgeoipredirect/country_currency/enable_currency')
        ) {
            $changeCurrency = true;
        }

        if (!$countRedirectUrl
            && !$isNotFirstTime
            && $this->scopeConfig->getValue('amgeoipredirect/country_url/enable_url')
            && $country
        ) {
            $urlMapping = $this->serializer->unserialize(
                $this->scopeConfig->getValue(
                    'amgeoipredirect/country_url/url_mapping',
                    $scopeStores,
                    $currentStoreId
                )
            );

            $currentUrl = trim($this->urlBuilder->getCurrentUrl(), self::URL_TRIM_CHARACTER);

            foreach ($urlMapping as $countries => $url) {
                $url = trim($url, self::URL_TRIM_CHARACTER);

                if (strpos($countries, $country) !== false && $url != $currentUrl) {
                    $session->setAmYetRedirectUrl(1);
                    $this->response->setNoCacheHeaders();

                    if ($this->needShowRedirectionPopup()) {
                        $session->setAmYetRedirectUrl(null);
                        $session->setIsNotFirstTime(null);
                        $session->setAmPopupCountry($country);
                        return $proceed($request);
                    }

                    if ($this->sessionManager->getWillRedirect() !== false) {
                        return $resultRedirect->setUrl($url);
                    }
                }
            }
        }

        if (!$countRedirectStore
            && !$isNotFirstTime
            && $this->scopeConfig->getValue('amgeoipredirect/country_store/enable_store')
        ) {
            $allStores = $this->storeManager->getStores();
            foreach ($allStores as $store) {
                $currentStoreUrl = str_replace('&amp;', '&', $store->getCurrentUrl(false));
                $redirectStoreUrl = trim($currentStoreUrl, '/');

                $countries = $this->scopeConfig->getValue(
                    'amgeoipredirect/country_store/affected_countries',
                    $scopeStores,
                    $store->getId()
                );
                if (!$this->scopeConfig->getValue('amgeoipredirect/restriction/redirect_between_websites')) {
                    $useMultistores = $store->getWebsiteId() == $this->storeManager->getStore()->getWebsiteId();
                } else {
                    $useMultistores = true;
                }

                if ($country && $countries && strpos($countries, $country) !== false
                    && $store->getId() != $currentStoreId
                    && $useMultistores
                ) {
                    $currentUrl = $this->urlBuilder->getCurrentUrl();
                    $neededBaseUrl = $store->getBaseUrl();

                    if ((strpos($currentUrl, $neededBaseUrl) === false)
                        || !$this->_compareEqualUrlFromStore($request, $store)
                    ) {
                        if ($changeCurrency) {
                            $this->_setNewCurrencyIfExist($country, $scopeStores, $store->getId());
                        }
                        $fromUrl = '___from_store=' . $this->storeManager->getStore()->getCode();
                        $redirectStoreUrl .= $this->parseUrl($redirectStoreUrl, PHP_URL_QUERY)
                            ? '&' . $fromUrl
                            : '?' . $fromUrl;

                        if ($this->needShowRedirectionPopup()) {
                            $session->setAmYetRedirectStore(null);
                            $session->setIsNotFirstTime(null);
                            $session->setAmPopupCountry($country);

                            return $proceed($request);
                        } elseif ($this->sessionManager->getWillRedirect() !== false) {
                            $this->_setDefaultLocale($store);
                            $session->setAmYetRedirectStore(1);
                            $this->storeCookieManager->setStoreCookie($store);
                            $this->response->setNoCacheHeaders();

                            return $resultRedirect->setUrl(
                                $this->tryReplaceWithUrlRewrite($request, $redirectStoreUrl, $store->getWebsiteId())
                            );
                        }
                    }
                }
            }
        }

        if ($changeCurrency && !empty($country)) {
            $this->_setNewCurrencyIfExist($country, $scopeStores, $currentStoreId);
        }

        return $proceed($request);
    }

    /**
     * @param RequestInterface $request
     * @param string $url
     * @param int $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    private function tryReplaceWithUrlRewrite(RequestInterface $request, string $url, int $storeId): string
    {
        $rewrite = $this->urlFinder->findOneByData([
            UrlRewrite::REQUEST_PATH => ltrim($request->getPathInfo(), '/'),
            UrlRewrite::STORE_ID => $this->storeManager->getStore()->getId(),
        ]);

        if ($rewrite) {
            $rewriteToOtherStore = $this->urlFinder->findOneByData([
                UrlRewrite::TARGET_PATH => $rewrite->getTargetPath(),
                UrlRewrite::STORE_ID => $storeId,
            ]);

            if ($rewriteToOtherStore) {
                return str_replace(
                    ltrim($request->getPathInfo(), '/'),
                    $rewriteToOtherStore->getRequestPath(),
                    $url
                );
            }
        }

        return $url;
    }

    protected function needShowRedirectionPopup()
    {
        $websiteId = $this->storeManager->getWebsite()->getId();
        $needPopup = $this->scopeConfig->getValue(
            'amgeoipredirect/general/redirection_decline',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        if ($needPopup && $this->sessionManager->getNeedShow() !== false) {
            $this->sessionManager->setNeedShow(true);
            return true;
        }

        return false;
    }

    /**
     * @param StoreInterface $store
     * @return bool
     */
    protected function _setDefaultLocale($store)
    {
        if ($store->getId()) {
            $localeCode = $this->scopeConfig->getValue(
                'general/locale/code',
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            );

            $this->resolver->setDefaultLocale($localeCode)->setLocale($localeCode);
        } else {
            return false;
        }
    }

    /**
     * @param $country
     * @param $scopeStores
     * @param $currentStoreId
     * @return $this
     */
    protected function _setNewCurrencyIfExist($country, $scopeStores, $currentStoreId)
    {
        $currencyMapping = $this->serializer->unserialize(
            $this->scopeConfig->getValue(
                'amgeoipredirect/country_currency/currency_mapping',
                $scopeStores,
                $currentStoreId
            )
        );

        foreach ($currencyMapping as $countries => $currency) {
            if (strpos($countries, $country) !== false
                && $this->storeManager->getStore()
                    ->getCurrentCurrencyCode() != $currency
            ) {
                $this->sessionManager->setAmYetRedirectCurrency(1);
                $this->storeManager->getStore()->setCurrentCurrencyCode($currency);
            }
        }

        return $this;
    }

    /**
     * @param RequestInterface $request
     * @param $checkStore
     * @return bool
     */
    protected function _compareEqualUrlFromStore($request, $checkStore)
    {
        if (version_compare($this->magentoVersion->get(), '2.3.0', '>=')) {
            $param = StoreManagerInterface::PARAM_NAME;
        } else {
            $param = StoreResolverInterface::PARAM_NAME;
        }
        if ($request->getParam($param)) {

            return ($checkStore
                && ($request->getParam($param) != $checkStore->getCode()))
                ? false : true;
        }

        return false;
    }

    /**
     * Is redirect allowed
     *
     * @param $request
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    protected function applyLogic($request)
    {
        $applyLogic = $this->scopeConfig->getValue('amgeoipredirect/restriction/apply_logic');
        $currentUrl = $this->urlBuilder->getCurrentUrl();
        switch ($applyLogic) {
            case Logic::SPECIFIED_URLS:
                $acceptedUrls = explode(
                    PHP_EOL,
                    $this->scopeConfig->getValue('amgeoipredirect/restriction/accepted_urls')
                );
                foreach ($acceptedUrls as $url) {
                    $url = trim($url);
                    if ($url && $currentUrl && $this->_compareUrls($currentUrl, $url)) {
                        $this->redirectAllowed = true;
                        return $url;
                    }
                }
                break;
            case Logic::HOMEPAGE_ONLY:
                if ($this->isHomePage()) {
                    $this->redirectAllowed = true;
                }
                break;
            default:
                $exceptedUrls = explode(
                    PHP_EOL,
                    $this->scopeConfig->getValue('amgeoipredirect/restriction/excepted_urls')
                );
                foreach ($exceptedUrls as $url) {
                    $url = trim($url);
                    if ($url && $currentUrl && strpos($currentUrl, $url) !== false) {
                        $this->redirectAllowed = false;
                        return $url;
                    } else {
                        $this->redirectAllowed = true;
                    }
                }
        }

        return '';
    }

    /**
     * @param string $currentUrl
     * @param string $comapareUrl
     * @return bool
     */
    protected function _compareUrls($currentUrl, $comapareUrl)
    {
        $urlParse = $this->parseUrl($comapareUrl);
        $currentUrlParse = $this->parseUrl($currentUrl);

        return (is_array($urlParse)
            && is_array($currentUrlParse)
            && (!isset($urlParse['host']) || $urlParse['host'] === $currentUrlParse['host'])
            && (!isset($urlParse['path']) && !isset($currentUrlParse['path'])
                || (isset($urlParse['path'])
                    && isset($currentUrlParse['path'])
                    && str_replace('/', '', $urlParse['path']) === str_replace('/', '', $currentUrlParse['path']))
            )
        );
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    protected function isHomePage()
    {
        $result = false;
        $currentUrl = $this->urlBuilder->getCurrentUrl();
        $homePageKey = $this->scopeConfig->getValue(PageHelper::XML_PATH_HOME_PAGE);
        $storeCode = $this->storeManager->getStore()->getCode();

        if ($this->scopeConfig->getValue(StoreModel::XML_PATH_SECURE_IN_FRONTEND)) {
            $baseUrl = $this->scopeConfig->getValue(StoreModel::XML_PATH_SECURE_BASE_URL);
        } else {
            $baseUrl = $this->scopeConfig->getValue(StoreModel::XML_PATH_UNSECURE_BASE_URL);
        }

        if (substr($baseUrl, -1) === '/') {
            $baseUrlWithSlash = $baseUrl;
            $baseUrl = substr($baseUrl, 0, -1);
        } else {
            $baseUrlWithSlash = $baseUrl . '/';
        }

        $homePageUrl = $baseUrlWithSlash . $homePageKey;
        $homePageUrlWithSlash = $homePageUrl . '/';
        $baseUrlWithStoreCode = $baseUrlWithSlash . $storeCode;
        $baseUrlWithStoreCodeWithSlash = $baseUrlWithStoreCode . '/';
        $homePageUrlWithStoreCode = $baseUrlWithStoreCodeWithSlash . $homePageKey;
        $homePageUrlWithStoreCodeWithSlash = $homePageUrlWithStoreCode . '/';

        $homePageUrls = [
            'base_url' => $baseUrl,
            'base_url_with_slash' => $baseUrlWithSlash,
            'home_page_url' => $homePageUrl,
            'home_page_url_with_slash' => $homePageUrlWithSlash,
            'base_url_with_store_code' => $baseUrlWithStoreCode,
            'base_url_with_store_code_with_slash' => $baseUrlWithStoreCodeWithSlash,
            'home_page_url_with_store_code' => $homePageUrlWithStoreCode,
            'home_page_url_with_store_code_with_slash' => $homePageUrlWithStoreCodeWithSlash,
        ];

        foreach ($homePageUrls as $key => $url) {
            if ($this->_compareUrls($currentUrl, $url)) {
                $result = true;

                break;
            }
        }

        return $result;
    }

    /**
     * Is redirect by GeoIP has not needed
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    protected function isNeedToProceed($request)
    {
        if ($this->isIpRestricted($request)
            || $request->isXmlHttpRequest()
        ) {
            return true;
        }
        $isApi = $request->getControllerModule() == 'Mage_Api';
        if ($isApi || !$this->geoipHelper->isModuleOutputEnabled('Amasty_Geoip')) {
            return true;
        }
        $userAgent = $request->getHeader('USER_AGENT');
        $userAgentsIgnore = $this->scopeConfig->getValue('amgeoipredirect/restriction/user_agents_ignore');
        if ($userAgent && !empty($userAgentsIgnore)) {
            $userAgentsIgnore = array_map("trim", explode(',', $userAgentsIgnore));
            foreach ($userAgentsIgnore as $agent) {
                if ($agent && stripos($userAgent, $agent) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * is IP in restricted list
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    private function isIpRestricted($request)
    {
        $ipRestriction = $this->scopeConfig->getValue('amgeoipredirect/restriction/ip_restriction');
        $currentIp = $this->getCurrentIp($request);
        if ($currentIp && !empty($ipRestriction)) {
            $ipRestriction = array_map("rtrim", explode(PHP_EOL, $ipRestriction));
            foreach ($ipRestriction as $ip) {
                if ($currentIp == $ip) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    private function getCurrentIp($request)
    {
        foreach ($this->addressPath as $path) {
            $ip = $request->getServer($path);
            if ($ip) {
                if (strpos($ip, ',') !== false) {
                    $addresses = explode(',', $ip);
                    foreach ($addresses as $address) {
                        if (trim($address) != self::LOCAL_IP) {
                            return trim($address);
                        }
                    }
                } else {
                    if ($ip != self::LOCAL_IP) {
                        return $ip;
                    }
                }
            }

        }

        return $this->remoteAddress->getRemoteAddress();
    }

    /**
     * @param $userIp
     * @return bool
     * @throws LocalizedException
     */
    private function isIpBlock($userIp)
    {
        $websiteId = $this->storeManager->getWebsite()->getId();
        $configIpsToBlock = $this->scopeConfig->getValue("amgeoipredirect/restrict_by_ip/ip_to_block");
        $websiteIpsToBlock = $this->scopeConfig->getValue(
            "amgeoipredirect/restrict_by_ip/ip_to_block",
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        if (empty($websiteIpsToBlock) && empty($configIpsToBlock)) {
            return false;
        }
        $ipsWeb = preg_split('/\n|\r\n?/', $websiteIpsToBlock);
        $ipsConfig = preg_split('/\n|\r\n?/', $configIpsToBlock);
        $ips = array_unique(array_merge($ipsWeb, $ipsConfig));

        foreach ($ips as $ip) {
            if (trim($ip) === $userIp) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $url
     * @param int $component
     * @return array|false|int|string|null
     */
    private function parseUrl(string $url, int $component = -1)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return parse_url($url, $component);
    }
}

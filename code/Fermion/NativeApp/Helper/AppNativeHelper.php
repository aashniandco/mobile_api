<?php

namespace Fermion\NativeApp\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Bss\SocialLogin\Model\SocialLogin as SocailLoginModel;
use Magento\Framework\App\ResourceConnection;

use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Math\Random;
use Fermion\NativeApp\Model\NativeTokens;
use Fermion\NativeApp\Model\ResourceModel\NativeTokens as NativeTokensResource;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class AppNativeHelper extends AbstractHelper
{
    
    /**
     * @var Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var Bss\SocialLogin\Model\SocialLogin
     */
    protected $socailLoginModel;
    
    /**
     * @var Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var Magento\Framework\Math\Random
     */
    private $mathRandom;

    /**
     * @var Fermion\NativeApp\Model\NativeTokens
     */
    private $nativeTokens;

    /**
     * @var Fermion\NativeApp\Model\ResourceModel\NativeTokens
     */
    private $nativeTokensResource;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Customer\Model\CustomerExtractor
     */
    private $customerExtractor;
    
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;


    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        SocailLoginModel $socailLoginModel,
        ResourceConnection $resourceConnection,
        Random $mathRandom,
        NativeTokens $nativeTokens,
        NativeTokensResource $nativeTokensResource,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManagement,
        Session $customerSession,
        CustomerExtractor $customerExtractor,
        ActionContext $actionContext,
        RequestFactory $requestFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->socailLoginModel = $socailLoginModel;
        $this->connection = $resourceConnection->getConnection();
        $this->mathRandom = $mathRandom;
        $this->nativeTokens = $nativeTokens;
        $this->nativeTokensResource = $nativeTokensResource;
        $this->storeManager = $storeManager;
        $this->accountManagement = $accountManagement;
        $this->session = $customerSession;
        $this->customerExtractor = $customerExtractor;
        $this->_eventManager = $actionContext->getEventManager();
        $this->request = $requestFactory->create($data = array());
        $this->scopeConfig = $scopeConfig;
    }

    public function sendResponse($respCode, $respMsg, $data, $errCode = '', $errMsg = '')
    {
        $respArr['response_code'] = $respCode;
        $respArr["response_message"] = $respMsg;
        $respArr["data"] = $data;
        if($errCode != '' || $errMsg != ''){
            $respArr["error"]["code"] = $errCode;
            $respArr["error"]["message"] = $errMsg;
        }
        else{
            $respArr["error"]["code"] = "";
            $respArr["error"]["message"] = "";
        }
        echo json_encode($respArr);
        die;
    }

    public function loadCustomerByRpToken($rpToken)
    {
        if (empty($rpToken)) {
            return null;
        }

        $filter = $this->filterBuilder
            ->setField('rp_token')
            ->setValue($rpToken)
            ->setConditionType('eq')
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters([$filter])
            ->create();

        $customerList = $this->customerRepository->getList($searchCriteria);
        $customers = $customerList->getItems();

        return !empty($customers) ? reset($customers) : null;
    }

    public function setSocialLoginCustomerData($data)
    { 
        try{
            $this->socailLoginModel->addData($data)->save();
            return true;
        }
        catch(Exception $e){
            return false;
        }
    }

    public function saveSocialCustomerDetails($data)
    {
        $social_entity_id = $this->socailLoginModel->getData('id');
        $sql = "INSERT INTO bss_social_customer_details (social_id, email, first_name, last_name) VALUES(?, ?, ?, ?)";
        $this->connection->query($sql, [$data['social_id'], $data['email'], $data['firstname'], $data['lastname']]);
    }

    public function getSocialCustomerDetails($social_id)
    {
        $sql = "SELECT bscd.email, bscd.first_name, bscd.last_name FROM bss_social_customer_details bscd WHERE bscd.social_id = ? ORDER BY bscd.entity_id DESC";
        $data = $this->connection->fetchRow($sql, [$social_id]);
        return $data;
    }

    public function updateSocialLoginCustomerData($user_id)
    {
        $sql = "UPDATE bss_sociallogin bs SET bs.password_fake_email = '', bs.pass_change_flag = 1 WHERE bs.customer_id = ?";
        $this->connection->query($sql, [$user_id]);
    }

    public function getDataforSocailSignedInUsers($social_id, $customer_id = null)
    {
        $sql = "SELECT bs.customer_id, bs.password_fake_email FROM bss_sociallogin bs LEFT JOIN customer_entity AS ce ON ce.entity_id = bs.customer_id WHERE ";

        if($customer_id != null || !empty($customer_id)){
            $sql .= " bs.customer_id = ".$customer_id;
        }
        else{
            $sql .= " bs.token_id = '".$social_id."'";
        }

        $sql .= " AND bs.pass_change_flag = 0 order by bs.id desc LIMIT 1";

        $result = $this->connection->fetchRow($sql);
        return $result;
    }

    public function signUpCustomer($customerDetails, $type)
    {
        $dataArray = array();

        try{
            $customer = $this->customerExtractor->extract('customer_account_create', $this->request);
            $customer->setAddresses([]);
            $firstname = $customerDetails['firstname'];
            $lastname = $customerDetails['lastname'];

            if(preg_match('/[^\x20-\x7e]/', $firstname)) {
                $this->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", "Please enter valid first name");
            }
            if(preg_match('/[^\x20-\x7e]/', $lastname)) {
                $this->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", "Please enter valid last name");
            }

            $password = $customerDetails['password'];
            $confirmation = $customerDetails['password_confirmation'];
            $this->checkPasswordConfirmation($password, $confirmation);
            $customer->setEmail($customerDetails['email']);
            $customer->setFirstName($firstname);
            $customer->setLastName($lastname);
            $extensionAttributes = $customer->getExtensionAttributes();
            $extensionAttributes->setIsSubscribed(false);
            $customer->setExtensionAttributes($extensionAttributes);

            $customer = $this->accountManagement
                    ->createAccount($customer, $password);

            $token = $this->mathRandom->getUniqueHash();
            $data = ['token' => $token, 'customer_id' => $customer->getId()];
            $NativeTokensModel = $this->nativeTokens;
            $NativeTokensModel->setData($data);
            $this->nativeTokensResource->save($NativeTokensModel);

            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            $redirectUrl = $baseUrl."?token=".$token;
            
            $customer_name = $customer->getFirstname()." ".$customer->getLastname();

            $this->_eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );

            $data = [
                'type' => $type,
                'token_id' => (string)$customerDetails['social_id'],
                'customer_id' => $customer->getId(),
                'password_fake_email' => $password
            ];
            $this->setSocialLoginCustomerData($data);

            $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
            if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $dataArray = ["redirect_url" => $redirectUrl, "user_id" => $customer->getId(), "name" => $customer_name, "email" => $customer->getEmail(), "created_at" => date("Y/m/d h:i:sa"), "password" => $password];
                $this->sendResponse(200, "Success", $dataArray);
            } 
        
        } 
        catch (StateException $e) {
            $this->sendResponse(400, "Bad Request", "", "StateException", "Customer with specified email already exists");
        } 
        catch (InputException $e) {
            $this->sendResponse(400, "Bad Request", "", "InputException", $e->getMessage());
        } 
        catch (LocalizedException $e) {
            $this->sendResponse(400, "Bad Request", "", "LocalizedException", $e->getMessage());
        } 
        catch (\Exception $e) {
            $this->sendResponse(400, "Bad Request", "", "Exception", $e->getMessage());
        }

        $this->session->setCustomerFormData($customerDetails);
        $dataArray = ["redirect_url" => $redirectUrl, "user_id" => $customer->getId(), "name" => $customer_name, "email" => $customer->getEmail(), "created_at" => date("Y/m/d h:i:sa"), "password" => $password];
        $this->sendResponse(200, "Success", $dataArray);
    }

    protected function checkPasswordConfirmation($password, $confirmation)
    {
        if ($password != $confirmation) {
            $this->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", "Please make sure your passwords match.");
        }
    }

    public function getAPISecretKey(){
        $key = $this->scopeConfig->getValue('nativeapp/api/secret', ScopeInterface::SCOPE_STORE);
        return $key;
    }

    public function validateRequests($apiEndpoint, $requestJson){
        $secretKey = $this->getAPISecretKey();
        $recievedToken = $this->request->getHeader('request-token');
        $requestToken = hash('sha256', $secretKey.$apiEndpoint.$requestJson);
        if($recievedToken != $requestToken){
            $this->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", "Bad Token Generated.");
        }
    }
}

<?php 

namespace Fermion\NativeApp\Model\Api;

use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\AddressRegistry;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\ObjectManager;
use Fermion\NativeApp\Helper\AppNativeHelper;
use Magento\Store\Model\StoreManagerInterface;

class EditCustomerDetails implements \Fermion\NativeApp\Api\EditCustomerDetailsInterface
{
	/**
     * Form code for data extractor
     */
    const FORM_DATA_EXTRACTOR_CODE = 'customer_account_edit';

	/**
     * @var Session
     */
    protected $session;

	/**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;
	
	/**
     * @var CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var Fermion\NativeApp\Helper\AppNativeHelper
     */
    protected $helper;

    /**
     * @var Mapper
     */
    private $customerMapper;

     /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @var \Magento\Customer\Model\EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;


    /**
     * @param Session $customerSession
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerExtractor $customerExtractor
     * @param RequestInterface $request
     * @param AddressRegistry|null $addressRegistry
     * @param AppNativeHelper $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Session $customerSession,
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        CustomerExtractor $customerExtractor,
        RequestInterface $request,
        AddressRegistry $addressRegistry = null,
        ManagerInterface $eventManager,
        AppNativeHelper $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->session = $customerSession;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->customerExtractor = $customerExtractor;
        $this->_request = $request;
        $this->addressRegistry = $addressRegistry ?: ObjectManager::getInstance()->get(AddressRegistry::class);
        $this->_eventManager = $eventManager;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    public function editCustomerDetails($customerDetails)
    {

        $requestJson = json_encode($customerDetails);
        $this->helper->validateRequests('/rest/V1/app_edit_customer_details', $requestJson);

    	if(!isset($customerDetails['user_id'])){
    		$this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", "Please provide user id");
    	}
    	$customer_id = $customerDetails['user_id'];
    	unset($customerDetails['user_id']);
        try{
            $currentCustomerDataObject = $this->customerRepository->getById($customer_id);
            $this->session->setCustomerDataAsLoggedIn($currentCustomerDataObject);
            $this->_request->setPostValue($customerDetails);
            $customerCandidateDataObject = $this->populateNewCustomerDataObject(
                $this->_request,
                $currentCustomerDataObject
            );
        }
        catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->helper->sendResponse(400, "Bad Request", "", "USER_NOT_FOUND", $e->getMessage());
        }
        catch (\Exception $e) {
            $this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", $e->getMessage());
        }
    	
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        
        try{
        	$this->processChangeEmailRequest($currentCustomerDataObject, $customerDetails);

            // whether a customer enabled change password option
            $isPasswordChanged = $this->changeCustomerPassword($currentCustomerDataObject->getEmail(), $customerDetails, $customer_id);

            // No need to validate customer address while editing customer profile
            $this->disableAddressValidation($customerCandidateDataObject);

            $this->customerRepository->saveWithoutEventDispatch($customerCandidateDataObject);
            $this->getEmailNotification()->credentialsChanged(
                $customerCandidateDataObject,
                $currentCustomerDataObject->getEmail(),
                $isPasswordChanged
            );

            $this->_eventManager->dispatch(
	            'customer_account_edited',
	            ['email' => $customerCandidateDataObject->getEmail()]
	        );

            $customer_name = $customerCandidateDataObject->getFirstname()." ".$customerCandidateDataObject->getLastname();
            $redirectUrl = $baseUrl."customer/account/";
            $dataArray = ["redirect_url" => $redirectUrl, "user_id" => $customerCandidateDataObject->getId(), "name" => $customer_name, "email" => $customerCandidateDataObject->getEmail(), "message" =>"You saved the account information.", "created_at" => date("Y/m/d h:i:sa")];
	        $this->helper->sendResponse(200, "Success", $dataArray);

        }
        catch (InvalidEmailOrPasswordException $e) {
			$this->session->setCustomerFormData($customerDetails);
            $this->helper->sendResponse(400, "Bad Request", "", "InvalidEmailOrPasswordException", $e->getMessage());
        }
        catch (UserLockedException $e) {
            $this->session->logout();
            $this->session->start();
			$this->session->setCustomerFormData($customerDetails);
            $redirectUrl = $baseUrl."customer/account/";
            $data = ['redirect_url' => $redirectUrl];
            $this->helper->sendResponse(400, "Bad Request", $data, "UserLockedException", 'The account sign-in was incorrect or your account is disabled temporarily. Please wait and try again later.');
        }
        catch (InputException $e) {
        	$this->session->setCustomerFormData($customerDetails);
            $this->helper->sendResponse(400, "Bad Request", "", "InputException", $e->getMessage());
        }
        catch (\Magento\Framework\Exception\LocalizedException $e) {
        	$this->session->setCustomerFormData($customerDetails);
            $this->helper->sendResponse(400, "Bad Request", "", "LocalizedException", $e->getMessage());
        }
        catch (\Exception $e) {
        	$this->session->setCustomerFormData($customerDetails);
            // $this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", "We can't save the customer.");
            $this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", $e->getMessage());
        }

    }


    /**
     * Create Data Transfer Object of customer candidate
     *
     * @param \Magento\Framework\App\RequestInterface $inputData
     * @param \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function populateNewCustomerDataObject(
        \Magento\Framework\App\RequestInterface $inputData,
        \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData
    ) {
        $attributeValues = $this->getCustomerMapper()->toFlatArray($currentCustomerData);
        $customerDto = $this->customerExtractor->extract(
            self::FORM_DATA_EXTRACTOR_CODE,
            $inputData,
            $attributeValues
        );
        $customerDto->setId($currentCustomerData->getId());
        if (!$customerDto->getAddresses()) {
            $customerDto->setAddresses($currentCustomerData->getAddresses());
        }
        if (!$inputData->getParam('change_email')) {
            $customerDto->setEmail($currentCustomerData->getEmail());
        }

        return $customerDto;
    }

    /**
     * Get Customer Mapper instance
     *
     * @return Mapper
     *
     * @deprecated 100.1.3
     */
    private function getCustomerMapper()
    {
        if ($this->customerMapper === null) {
            $this->customerMapper = ObjectManager::getInstance()->get(\Magento\Customer\Model\Customer\Mapper::class);
        }
        return $this->customerMapper;
    }

    /**
     * Process change email request
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $currentCustomerDataObject
     * @param array $customerDetails
     * @return void
     */
    private function processChangeEmailRequest(\Magento\Customer\Api\Data\CustomerInterface $currentCustomerDataObject, $customerDetails)
    {
        if (isset($customerDetails['change_email']) && $customerDetails['change_email']) {
            // authenticate user for changing email
            try {
                $this->getAuthentication()->authenticate(
                    $currentCustomerDataObject->getId(),
                    $customerDetails['current_password']
                );
            } catch (InvalidEmailOrPasswordException $e) {
                $this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", "The password doesn't match this account. Verify the password and try again.");
            }
        }
    }

    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */
    private function getAuthentication()
    {

        if (!($this->authentication instanceof AuthenticationInterface)) {
            return ObjectManager::getInstance()->get(
                \Magento\Customer\Model\AuthenticationInterface::class
            );
        } else {
            return $this->authentication;
        }
    }

    /**
     * Change customer password
     *
     * @param string $email
     * @param array $customerDetails
     * @return boolean
     */
    protected function changeCustomerPassword($email, $customerDetails, $customer_id)
    {
        $isPasswordChanged = false;
        if (isset($customerDetails['change_password']) && $customerDetails['change_password']) {
            $currPass = $customerDetails['current_password'];
            $newPass = $customerDetails['password'];
            $confPass = $customerDetails['password_confirmation'];
            if ($newPass != $confPass) {
                $this->helper->sendResponse(400, "Bad Request", "", "UNABLE_TO_PROCESS", "Password confirmation doesn't match entered password.");
            }

            $isPasswordChanged = $this->accountManagement->changePassword($email, $currPass, $newPass);

            $this->helper->updateSocialLoginCustomerData($customer_id);
        }

        return $isPasswordChanged;
    }

    /**
     * Disable Customer Address Validation
     *
     * @param CustomerInterface $customer
     * @throws NoSuchEntityException
     */
    private function disableAddressValidation($customer)
    {
        foreach ($customer->getAddresses() as $address) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
            $addressModel->setShouldIgnoreValidation(true);
        }
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated 100.1.0
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

}
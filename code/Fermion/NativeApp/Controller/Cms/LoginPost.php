<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Fermion\NativeApp\Controller\Cms;



/**
 * Post login customer action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPost extends \Magento\Customer\Controller\Account\LoginPost
{
    private $cookieMetadataManager;
    private $cookieMetadataFactory;
    private $scopeConfig;


    
    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */

    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }

    public function execute()
    {
        die('____');
        if(isset($_GET['token'])){
            $token = $_GET['token'];
            
            if (!empty($token)) {
                try {

                    $nativeTokensCollection = $this->nativeTokenFactory->create();
                    $nativeTokensCollection->addTokenFilter($token);

                    $customer_id = null;
                    foreach ($nativeTokensCollection as $item) {
                        $customer_id = $item->getData("customer_id");
                        break;
                    }

                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $customerRepository = $ObjectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');

                    $customer = $customerRepository->getById($customer_id);


                    $customer = $this->customerAccountManagement->authenticate($customer->getEmail(), 'token');

                    $this->session->setCustomerDataAsLoggedIn($customer);
                    if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                        $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                        $metadata->setPath('/');
                        $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                    }

                    $redirectUrl = $this->accountRedirect->getRedirectCookie();
                    if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                        $this->accountRedirect->clearRedirectCookie();
                        $resultRedirect = $this->resultRedirectFactory->create();
                    }
                    $response['error'] = 0;
                    $response['msg'] = 'Logged In';
                } catch (EmailNotConfirmedException $e) {

                    $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $response['error'] = 1;
                    $response['msg'] = __(
                        'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                } catch (UserLockedException $e) {
                    $response['error'] = 1;
                    $response['msg'] = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    );
                } catch (AuthenticationException $e) {
                    $response['error'] = 1;
                    $response['msg']  = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    );
                } catch (LocalizedException $e) {

                    $message = $e->getMessage();
                } catch (\Exception $e) {
                    
                    // PA DSS violation: throwing or logging an exception here can disclose customer password
                    $response['error'] = 1;
                    $response['msg'] = $e->getMessage();
                   
                } finally {
                    if (isset($message)) {
                        $this->messageManager->addError($message);
                        $this->session->setUsername($login['username']);
                    }
                }
            }elseif(!empty($login['username']) && !empty($login['otp'])){
                $this->verifyOtp($login['username'],$login['otp']);
            }else{
                $response['error'] = 1;
                $response['msg'] = 'A login and a password are required.';
            }


            echo json_encode($response);die;
        }else{
            if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
                /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            }
            if ($this->getRequest()->isPost()) {
                $login = $this->getRequest()->getPost('login');
                if (!empty($login['username']) && !empty($login['password'])) {
                    try {
                        $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                        $this->session->setCustomerDataAsLoggedIn($customer);
                        if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                            $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                            $metadata->setPath('/');
                            $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                        }
                        $redirectUrl = $this->accountRedirect->getRedirectCookie();
                        if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                            $this->accountRedirect->clearRedirectCookie();
                            $resultRedirect = $this->resultRedirectFactory->create();
                            // URL is checked to be internal in $this->_redirect->success()
                            $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
                            return $resultRedirect;
                        }
                    } catch (EmailNotConfirmedException $e) {
                        $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                        $message = __(
                            'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                            $value
                        );
                    } catch (UserLockedException $e) {
                        $message = __(
                            'The account sign-in was incorrect or your account is disabled temporarily. '
                            . 'Please wait and try again later.'
                        );
                    } catch (AuthenticationException $e) {
                        $message = __(
                            'The account sign-in was incorrect or your account is disabled temporarily. '
                            . 'Please wait and try again later.'
                        );
                    } catch (LocalizedException $e) {
                        $message = $e->getMessage();
                    } catch (\Exception $e) {
                        // PA DSS violation: throwing or logging an exception here can disclose customer password
                        $this->messageManager->addError(
                            __('An unspecified error occurred. Please contact us for assistance.')
                        );
                    } finally {
                        if (isset($message)) {
                            $this->messageManager->addError($message);
                            $this->session->setUsername($login['username']);
                        }
                    }
                } else {
                    $this->messageManager->addError(__('A login and a password are required.'));
                }
            }

            return $this->accountRedirect->getRedirect();   
        }


        
    }

    public function verifyOtp($email,$otp){
        $response = array();
        $ObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $CustomerFactory = $ObjectManager->create('\Magento\Customer\Model\CustomerFactory')->create();
        $customer = $CustomerFactory->loadByEmail($email);
        $customerId = $customer->getId();
        if($customerId){
            $connection = $ObjectManager->create('\Magento\Framework\App\ResourceConnection')->getConnection();
                    
            $query =  $connection->select()
                    ->from(['sio' => 'sign_in_otp'])
                    ->where('sio.otp = ?', $otp)
                    ->where('sio.customer_id = ?', $customerId)
                    ->where('sio.is_active = ?', '1')
                    ->where('sio.is_used = ?', '0')
                    ->limit(1)
                    ->order('id desc');
            $otpResult = $connection->fetchAll($query);
            
            
            if (count($otpResult) > 0) {
                $id = isset($otpResult[0]['id']) ? $otpResult[0]['id']: '';
                $dataToUpdate = ['is_active' => '0','is_used' => '1'];
                $where = ['id = ?' => $id];
                $tableName = 'sign_in_otp';
                $connection->update($tableName, $dataToUpdate, $where);

                try {
                    $customer = $this->customerAccountManagement->customauthenticate($email);

                    $this->session->setCustomerDataAsLoggedIn($customer);
                    if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                        $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                        $metadata->setPath('/');
                        $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                    }

                    $redirectUrl = $this->accountRedirect->getRedirectCookie();
                    if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                        $this->accountRedirect->clearRedirectCookie();
                        $resultRedirect = $this->resultRedirectFactory->create();
                    }
                    $response['error'] = 0;
                    $response['msg'] = 'Logged In';
                } catch (EmailNotConfirmedException $e) {

                    $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $response['error'] = 1;
                    $response['msg'] = __(
                        'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                } catch (UserLockedException $e) {
                    $response['error'] = 1;
                    $response['msg'] = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    );
                } catch (AuthenticationException $e) {
                    $response['error'] = 1;
                    $response['msg']  = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    );
                } catch (LocalizedException $e) {

                    $message = $e->getMessage();
                } catch (\Exception $e) {
                    
                    // PA DSS violation: throwing or logging an exception here can disclose customer password
                    $response['error'] = 1;
                    $response['msg'] = $e->getMessage();
                   
                } finally {
                    if (isset($message)) {
                        $this->messageManager->addError($message);
                        $this->session->setUsername($login['username']);
                    }
                }
            } else {
                $response['error'] = 1;
                $response['msg'] = 'Wrong OTP is inserted';
            } 
            
        }else{
            $response['error'] = 1;
            $response['msg'] = 'User does not exist';
        }
        echo json_encode($response);die;
    }
}

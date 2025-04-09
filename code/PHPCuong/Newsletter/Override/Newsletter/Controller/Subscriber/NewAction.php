<?php
/**
 * GiaPhuGroup Co., Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GiaPhuGroup.com license that is
 * available through the world-wide-web at this URL:
 * https://www.giaphugroup.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    PHPCuong
 * @package     PHPCuong_Newsletter
 * @copyright   Copyright (c) 2019-2020 GiaPhuGroup Co., Ltd. All rights reserved. (http://www.giaphugroup.com/)
 * @license     https://www.giaphugroup.com/LICENSE.txt
 */

namespace PHPCuong\Newsletter\Override\Newsletter\Controller\Subscriber;

use Magento\Framework\App\ObjectManager;

class NewAction extends \Magento\Newsletter\Controller\Subscriber\NewAction
{
    //die("aaa");
    /**
     * @var \Magento\Framework\Controller\Result\Json
     */
    protected $_resultJson;

    /**
     * New subscription action
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute()
    {
        error_log("mithilesh_newaction_step1");
        $result = [];
        error_log("mithilesh_newaction_step2");
        $result['error'] = true;
        error_log("mithilesh_newaction_step3");
        $result['message'] = __('Something went wrong, Please try later.');
        error_log("mithilesh_newaction_step4");
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            error_log("mithilesh_newaction_step5");
            $email = (string)$this->getRequest()->getPost('email');
            error_log("mithilesh_newaction_step6");

            try {
                error_log("mithilesh_newaction_step7");
                $this->validateEmailFormat($email);
                error_log("mithilesh_newaction_step8");
                $this->validateGuestSubscription();
                error_log("mithilesh_newaction_step9");
                $this->validateEmailAvailable($email);
                error_log("mithilesh_newaction_step10");

                $subscriber = $this->_subscriberFactory->create()->loadByEmail($email);
                error_log("mithilesh_newaction_step11");
                if ($subscriber->getId()
                    && $subscriber->getSubscriberStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED
                ) {
                    error_log("mithilesh_newaction_step12");
                    $result['message'] = __('This email address is already subscribed.');
                    error_log("mithilesh_newaction_step13");
                } else {
                    error_log("mithilesh_newaction_step14");
                    $status = $this->_subscriberFactory->create()->subscribe($email);
                    error_log("mithilesh_newaction_step15");
                    if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                        error_log("mithilesh_newaction_step16");
                        $result['message'] = __('The confirmation request has been sent.');
                        error_log("mithilesh_newaction_step17");
                        $result['error'] = false;
                        error_log("mithilesh_newaction_step18");
                    } else {
                        error_log("mithilesh_newaction_step19");
                        $result['message'] = __('Thank you for your subscription.');
                        error_log("mithilesh_newaction_step20");
                        $result['error'] = false;
                        error_log("mithilesh_newaction_step21");
                    }
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                error_log("mithilesh_newaction_step22");
                $result['message'] = __('There was a problem with the subscription: %1', $e->getMessage());
                error_log("mithilesh_newaction_step23");
            } catch (\Exception $e) {
                error_log("mithilesh_newaction_step24");
                $result['message'] = $e->getMessage();
                error_log("mithilesh_newaction_step25");
            }
        }
        //echo json_encode($result);die("xyz");
        error_log("mithilesh_newaction_step26");
        //print_r($result);die("asdf");


//$message = __('This email address is already subscribed.');        

// $message = $result['message'];
// $this->messageManager->addErrorMessage($message);
// $resultRedirect = $this->resultRedirectFactory->create();
// $resultRedirect->setPath('/');
// return $resultRedirect;

        error_log("mithilesh_newaction_step27");
        if($result['error']){
            $this->messageManager->addErrorMessage($result['message']);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('/');
            // return $resultRedirect;
            return $this->getResultJson()->setData($result);
        }else{
            $this->messageManager->addSuccessMessage($result['message']);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('/');
            // return $resultRedirect;
            return $this->getResultJson()->setData($result);
        }
        // return $this->getResultJson()->setData($result); 
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    protected function getResultJson()
    {
        if ($this->_resultJson === null) {
            $this->_resultJson = ObjectManager::getInstance()->get(\Magento\Framework\Controller\Result\Json::class);
        }
        return $this->_resultJson;
    }
}

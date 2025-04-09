<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fermion\NativeApp\Controller\Account;

class ForgotPassword extends \Magento\Customer\Controller\Account\ForgotPassword
{
    public function execute()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if($userAgent == 'android' || $userAgent == 'ios'){
            $resultPage = $this->resultPageFactory->create();
            $resultPage->addHandle('empty_layout');
            return $resultPage;
        }
        if ($this->session->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('forgotPassword')->setEmailValue($this->session->getForgottenEmail());

        $this->session->unsForgottenEmail();

        return $resultPage;
    }
}

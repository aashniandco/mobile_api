<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fermion\NativeApp\Controller\Account;


class Edit extends \Magento\Customer\Controller\Account\Edit
{
    public function execute()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if($userAgent == 'android' || $userAgent == 'ios'){
            $resultPage = $this->resultPageFactory->create();
            $resultPage->addHandle('empty_layout');
            return $resultPage;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $block = $resultPage->getLayout()->getBlock('customer_edit');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }

        $data = $this->session->getCustomerFormData(true);
        $customerId = $this->session->getCustomerId();
        $customerDataObject = $this->customerRepository->getById($customerId);
        if (!empty($data)) {
            $this->dataObjectHelper->populateWithArray(
                $customerDataObject,
                $data,
                \Magento\Customer\Api\Data\CustomerInterface::class
            );
        }
        $this->session->setCustomerData($customerDataObject);
        $this->session->setChangePassword($this->getRequest()->getParam('changepass') == 1);

        $resultPage->getConfig()->getTitle()->set(__('Account Information'));
        return $resultPage;
    }
}

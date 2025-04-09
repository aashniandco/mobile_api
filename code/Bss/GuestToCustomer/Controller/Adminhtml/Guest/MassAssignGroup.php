<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_GuestToCustomer
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GuestToCustomer\Controller\Adminhtml\Guest;

use Bss\GuestToCustomer\Helper\ConfigAdmin;
use Bss\GuestToCustomer\Helper\ConfigCustomerAdmin;
use Bss\GuestToCustomer\Helper\Customer\SaveCustomer;
use Bss\GuestToCustomer\Helper\MassActionHelper;
use Bss\GuestToCustomer\Model\Guest;
use Bss\GuestToCustomer\Model\ResourceModel\Guest\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

class MassAssignGroup extends Action
{
    const BSS_BILLING_ADDRESS_TYPE = true;
    const BSS_SHIPPING_ADDRESS_TYPE = false;

    /**
     * Helper Config Admin
     * @var ConfigAdmin $helperConfigAdmin
     */
    protected $helperConfigAdmin;

    /**
     * CollectionFactory
     * @var CollectionFactory $collectionFactory
     */
    protected $collectionFactory;

    /**
     * Guest
     * @var Guest
     */
    protected $modelGuest;

    /**
     * Helper Save Customer
     * @var SaveCustomer
     */
    protected $helperSaveCustomer;

    /**
     * Config Customer Admin
     *
     * @var ConfigCustomerAdmin $helperConfigCustomerAdmin
     */
    protected $helperConfigCustomerAdmin;

    /**
     * @var MassActionHelper
     */
    protected $massActionHelper;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Bss\GuestToCustomer\Helper\Customer\Address
     */
    protected $helperConvertAddress;

    /**
     * MassAssignGroup constructor.
     * @param Context $context
     * @param SerializerInterface $serializer
     * @param SaveCustomer $helperSaveCustomer
     * @param Guest $modelGuest
     * @param CollectionFactory $collectionFactory
     * @param ConfigAdmin $helperConfigAdmin
     * @param ConfigCustomerAdmin $helperConfigCustomerAdmin
     * @param MassActionHelper $massActionHelper
     * @param \Bss\GuestToCustomer\Helper\Customer\Address $helperConvertAddress
     */
    public function __construct(
        Context $context,
        SerializerInterface $serializer,
        SaveCustomer $helperSaveCustomer,
        Guest $modelGuest,
        CollectionFactory $collectionFactory,
        ConfigAdmin $helperConfigAdmin,
        ConfigCustomerAdmin $helperConfigCustomerAdmin,
        MassActionHelper $massActionHelper,
        \Bss\GuestToCustomer\Helper\Customer\Address $helperConvertAddress
    ) {
        $this->serializer = $serializer;
        $this->modelGuest = $modelGuest;
        $this->helperSaveCustomer = $helperSaveCustomer;
        $this->collectionFactory = $collectionFactory;
        $this->helperConfigAdmin = $helperConfigAdmin;
        $this->helperConfigCustomerAdmin = $helperConfigCustomerAdmin;
        $this->massActionHelper = $massActionHelper;
        $this->helperConvertAddress = $helperConvertAddress;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return Redirect|ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->massActionHelper->getCoreSession()->start();
        $this->massActionHelper->getCoreSession()->setData('bss_guest_to_customer_type', 1);

        $groupId = $this->getRequest()->getParam('group');
        $collection=$this->massActionHelper->getFilter()->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        foreach ($collection as $guest) {
            try {
                if ($this->helperConfigAdmin->getConfigEnableModule()) {
                    $customerData = [];
                    $addressBilling = $this->serializer->unserialize($guest->getBillingAddress());
                    $addressShipping = $this->serializer->unserialize($guest->getShippingAddress());
                    $addressConverted = $this->helperConvertAddress->isAddressImported($addressBilling, $addressShipping);
                    if ($addressConverted) {
                        // Case: import from old orders
                        $billing = $addressBilling[0];
                        $shipping = $addressShipping[0];
                        if (!is_array($shipping) || empty($shipping)) {
                            $shipping = $billing;
                        }
                        $this->helperConfigAdmin->getConfigTelephoneRequire($shipping);
                        $this->helperConfigAdmin->getConfigTelephoneRequire($billing);
                        $customerData = $this->makeCustomerData($guest, $groupId, $billing);
                    } else {
                        if (!is_array($addressShipping) || empty($addressShipping)) {
                            $addressShipping = $addressBilling;
                        }
                        $this->helperConfigAdmin->getConfigTelephoneRequire($addressShipping);
                        $this->helperConfigAdmin->getConfigTelephoneRequire($addressBilling);
                        $customerData = $this->makeCustomerData($guest, $groupId, $addressBilling);
                    }
                    $this->saveOneCustomer($customerData, $addressBilling, $addressShipping);
                    $this->deleteGuest($guest);

                    // Only reindex if no error occur
                    $indexer = $this->massActionHelper->getIndexer()->get("customer_grid");
                    $indexer->reindexAll();
                    $this->messageManager->addSuccessMessage(
                        __(
                            'A total of %1 record(s) have been assign.',
                            $collectionSize
                        )
                    );
                }
            } catch (AlreadyExistsException $exception) {
                // If email already exist. Remove guest only
                $this->messageManager->addWarningMessage(
                    $exception->getMessage()
                );
                $this->deleteGuest($guest);
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $this->massActionHelper->getCoreSession()->setData('bss_guest_to_customer_type', 0);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $guest
     * @param $groupId
     * @param $addressBilling
     * @return array
     */
    protected function makeCustomerData($guest, $groupId, $addressBilling)
    {
        return [
            "website_id" => $guest['website_id'],
            "store_id" => $guest['store_id'],
            "country_id" => $guest['country_id'],
            "group_id" => $groupId,
            "disable_auto_group_change" => 0,
            "prefix" => $this->helperConvertAddress->getValueElementArray($addressBilling, 'prefix'),
            "firstname" => $this->helperConvertAddress->getValueElementArray($addressBilling, 'firstname'),
            "lastname" => $this->helperConvertAddress->getValueElementArray($addressBilling, 'lastname'),
            "suffix" => $this->helperConvertAddress->getValueElementArray($addressBilling, 'suffix'),
            "email" => $this->helperConvertAddress->getValueElementArray($addressBilling, 'email'),
            "fax" => $this->helperConvertAddress->getValueElementArray($addressBilling, 'fax'),
            "telephone" => $this->helperConvertAddress->getValueElementArray($addressBilling, 'telephone'),
            "company" => $this->helperConvertAddress->getValueElementArray($addressBilling, 'company'),
            "vat_id" => $this->helperConvertAddress->getValueElementArray($addressBilling, 'vat_id'),
            "sendemail_store_id" => 1
        ];
    }

    /**
     * Is async address
     *
     * @param array $shippingAddress
     * @param array $billingAddress
     * @return bool
     */
    public function isAsyncAddress($shippingAddress, $billingAddress)
    {
        unset($shippingAddress['address_type']);
        unset($billingAddress['address_type']);
        unset($shippingAddress['quote_address_id']);
        unset($billingAddress['quote_address_id']);
        if (!empty(array_diff($shippingAddress, $billingAddress))) {
            $sameAddress = false;
        } else {
            $sameAddress = true;
        }
        if ($sameAddress && $this->helperConfigAdmin->isAsyncAddress()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Save One Customer
     *
     * @param array $dataCustomer
     * @param array $dataAddressBilling
     * @param array $dataAddressShipping
     * @return void
     */
    protected function saveOneCustomer($dataCustomer = [], $dataAddressBilling = [], $dataAddressShipping = [])
    {
        $addresses = $this->helperConvertAddress->processAddressesCustomer($dataAddressBilling, $dataAddressShipping);
        $customer = $this->helperSaveCustomer->processCreateCustomer($addresses, $dataCustomer);
        if ($customer instanceof \Magento\Customer\Model\Customer && $customer->getId()) {
            /**
                Fix customer data customer group when config
                Stores->Configuration->Customers->Customer Configuration
                ->Create New Account Options->Enable Automatic Assignment to Customer Group (Yes)
            */
            $dataCustomer['group_id'] = $customer->getGroupId();
            $config = $this->helperConfigAdmin->getConfigAssignOrders();
            if ($config) {
                $this->helperSaveCustomer->saveOrders($customer->getId(), $dataCustomer);
            }
        }
    }

    /**
     * Delete Guest
     *
     * @param Guest $guest
     * @return void
     */
    private function deleteGuest($guest)
    {
        try {
            $this->modelGuest->load($guest->getId());
            $this->modelGuest->delete();
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
    }

    /**
     * Check Rule
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Bss_GuestToCustomer::assign");
    }
}

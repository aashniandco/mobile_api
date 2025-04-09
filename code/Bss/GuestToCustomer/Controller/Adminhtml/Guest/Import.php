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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Serialize\SerializerInterface;

class Import extends Action
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const SPLIT_ROW = 5000;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Bss\GuestToCustomer\Model\ResourceModel\Guest\CollectionFactory
     */
    protected $guestCollectionFactory;

    /**
     * @var \Bss\GuestToCustomer\Model\ResourceModel\Guest
     */
    protected $guestResource;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Bss\GuestToCustomer\Model\GuestFactory
     */
    protected $guestFactory;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Import constructor.
     * @param Context $context
     * @param SerializerInterface $serializer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Bss\GuestToCustomer\Model\ResourceModel\Guest\CollectionFactory $guestCollectionFactory
     * @param \Bss\GuestToCustomer\Model\ResourceModel\Guest $guestResource
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Bss\GuestToCustomer\Model\GuestFactory $guestFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        SerializerInterface $serializer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Bss\GuestToCustomer\Model\ResourceModel\Guest\CollectionFactory $guestCollectionFactory,
        \Bss\GuestToCustomer\Model\ResourceModel\Guest $guestResource,
        \Bss\GuestToCustomer\Model\GuestFactory $guestFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->guestFactory = $guestFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->guestCollectionFactory = $guestCollectionFactory;
        $this->guestResource = $guestResource;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $result = [
            'success' => false,
            'errorMessage' => '',
        ];
        $model = $this->guestFactory->create();
        $emailsExist = $this->getCustomerEmails();
        $emailsGuestExist = $this->getGuestExist();
        $emailsArr = array_merge($emailsExist, $emailsGuestExist);
        $guestsDetailed = $this->getGuestNotExist($emailsArr);
        $guests = $this->getGuests($guestsDetailed);
        try {
            if (!empty($guests)) {
                $totalRows = count($guests);
                $count = ceil($totalRows/self::SPLIT_ROW);
                for ($i = 0; $i < $count; $i++) {
                    $dataImport = array_slice($guests, $i * self::SPLIT_ROW, self::SPLIT_ROW);
                    $model->importGuest($dataImport);
                }
            }
            $result['success'] = true;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result['errorMessage'] = $e->getMessage();
        } catch (\Exception $e) {
            $message = __($e->getMessage());
            $result['errorMessage'] = $message;
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }

    /**
     * @return array
     */
    private function getCustomerEmails()
    {
        $customers = $this->customerCollectionFactory->create()
            ->addFieldToSelect('email');
        $emails = [];
        if ($customers->getSize() > 0) {
            foreach ($customers as $customer) {
                $emails[] = $customer->getEmail();
            }
        }
        return $emails;
    }

    /**
     * @param $emails
     * @return array
     */
    private function getGuestExist()
    {
        $guests = $this->guestCollectionFactory->create()
            ->addFieldToSelect('email');
        $emails = [];
        if ($guests->getSize() > 0) {
            foreach ($guests as $guest) {
                $emails[] = $guest->getEmail();
            }
        }
        return $emails;
    }

    /**
     * @param array $emails
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getGuestNotExist($emails = [])
    {
        $from = $this->getRequest()->getParam('from', false);
        $to = $this->getRequest()->getParam('to', false);

        $from = $from ? date('Y-m-d 00:00:01', strtotime($from)) : false;
        $to = $to ? date('Y-m-d 23:59:59', strtotime($to)) : date('Y-m-d 23:59:59');
        $orders = $this->guestResource->getOrdersListData($emails, $from, $to);
        $guests = [];
        if (count($orders) > 0) {
            $addressMerge = [];
            foreach ($orders as $order) {
                $addressMerge[$order['entity_id']][] = $order;
            }
            foreach ($addressMerge as $orderId => $addresses) {
                $arrShippingAddress = $this->getAddress($addresses, 'shipping');
                $arrBillingAddress = $this->getAddress($addresses, 'billing');
                if (isset($arrBillingAddress['firstname']) &&
                    isset($arrBillingAddress['lastname']) &&
                    isset($arrBillingAddress['email']) &&
                    isset($arrBillingAddress['store_id']) &&
                    isset($arrBillingAddress['website_id'])) {
                    $emailGuest = $arrBillingAddress['email'];
                    $storeId = $arrBillingAddress['store_id'];
                    $websiteId = $arrBillingAddress['website_id'];
                    $guestInfo = [
                        'email' => $emailGuest,
                        'first_name' => $arrBillingAddress['firstname'],
                        'last_name' => $arrBillingAddress['lastname'],
                        'website_id' => $websiteId,
                        'store_id' => $storeId,
                        'shipping_address' => $arrShippingAddress,
                        'billing_address' => $arrBillingAddress
                    ];
                    $guests[$emailGuest][] = $guestInfo;
                }
            }
        }

        return $guests;
    }

    /**
     * @param $addresses
     * @param $type
     * @return array
     */
    private function getAddress($addresses, $type)
    {
        $cols = [
            'customer_address_id',
            'quote_address_id',
            'region_id',
            'region',
            'postcode',
            'lastname',
            'street',
            'city',
            'email',
            'telephone',
            'firstname',
            'address_type',
            'prefix',
            'middlename',
            'suffix',
            'company',
            'vat_id',
            'store_id',
            'website_id',
            'country_id'
        ];
        $address = [];
        $orderData = [];
        $addressCounter = count($addresses);
        if (!$addressCounter) {
            return $address;
        } elseif ($addressCounter == 1) {
            // Checkout virtual + download have no shipping
            $orderData = $addresses[array_key_first($addresses)];
        } elseif ($addressCounter == 2) {
            foreach ($addresses as $addressItem) {
                if (isset($addressItem['address_type']) &&
                    $addressItem['address_type'] == $type) {
                    $orderData = $addressItem;
                    break;
                }
            }
        }
        foreach ($cols as $col) {
            if (isset($orderData[$col])) {
                $address[$col] = $orderData[$col];
            }
        }
        return $address;
    }

    /**
     * @param $guests
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getGuests($guests)
    {
        $guestsDetails = [];
        foreach ($guests as $email => $guestAddresses) {
            $info = [];
            $guestShipping = [];
            $guestBilling = [];
            $idx = 1;
            foreach ($guestAddresses as $guestAddress) {
                if ($idx == 1) {
                    $info = [
                        'email' =>  $guestAddress['email'],
                        'first_name' => $guestAddress['first_name'],
                        'last_name' => $guestAddress['last_name'],
                        'website_id' => $guestAddress['website_id'],
                        'store_id' => $guestAddress['store_id'],
                    ];
                }
                $guestShipping[] = $guestAddress['shipping_address'];
                $guestBilling[] = $guestAddress['billing_address'];
                $idx ++;
            }
            if (!empty($info) && !empty($guestBilling) && !empty($guestShipping)) {
                $info['shipping_address'] = $this->serializer->serialize($guestShipping);
                $info['billing_address'] = $this->serializer->serialize($guestBilling);
                $guestsDetails[] = $info;
            }
        }
        return $guestsDetails;
    }
}

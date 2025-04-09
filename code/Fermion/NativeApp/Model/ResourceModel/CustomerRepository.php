<?php
/**
 * This class contains an extra function which is same as the "save" function
 * in vendor's CustomerRepository excluding the 'customer_save_after_data_object' 
 * event dispatch in the last of the method
 * This function is made for Native app edit customer details API
 */

namespace Fermion\NativeApp\Model\ResourceModel;

use Magento\Customer\Model\ResourceModel\CustomerRepository as VendorCustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerMetadataInterface;

class CustomerRepository extends VendorCustomerRepository
{
	public function saveWithoutEventDispatch(CustomerInterface $customer, $passwordHash = null)
    {
        /** @var NewOperation|null $delegatedNewOperation */
        $delegatedNewOperation = !$customer->getId() ? $this->delegatedStorage->consumeNewOperation() : null;
        $prevCustomerData = null;
        $prevCustomerDataArr = null;
        if ($customer->getId()) {
            $prevCustomerData = $this->getById($customer->getId());
            $prevCustomerDataArr = $prevCustomerData->__toArray();
        }
        /** @var $customer \Magento\Customer\Model\Data\Customer */
        $customerArr = $customer->__toArray();
        $customer = $this->imageProcessor->save(
            $customer,
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            $prevCustomerData
        );
        $origAddresses = $customer->getAddresses();
        $customer->setAddresses([]);
        $customerData = $this->extensibleDataObjectConverter->toNestedArray($customer, [], CustomerInterface::class);
        $customer->setAddresses($origAddresses);
        /** @var CustomerModel $customerModel */
        $customerModel = $this->customerFactory->create(['data' => $customerData]);
        //Model's actual ID field maybe different than "id" so "id" field from $customerData may be ignored.
        $customerModel->setId($customer->getId());
        $storeId = $customerModel->getStoreId();
        if ($storeId === null) {
            $customerModel->setStoreId(
                $prevCustomerData ? $prevCustomerData->getStoreId() : $this->storeManager->getStore()->getId()
            );
        }
        // Need to use attribute set or future updates can cause data loss
        if (!$customerModel->getAttributeSetId()) {
            $customerModel->setAttributeSetId(CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER);
        }
        $this->populateCustomerWithSecureData($customerModel, $passwordHash);
        // If customer email was changed, reset RpToken info
        if ($prevCustomerData && $prevCustomerData->getEmail() !== $customerModel->getEmail()) {
            $customerModel->setRpToken(null);
            $customerModel->setRpTokenCreatedAt(null);
        }
        if (!array_key_exists('addresses', $customerArr)
            && null !== $prevCustomerDataArr
            && array_key_exists('default_billing', $prevCustomerDataArr)
        ) {
            $customerModel->setDefaultBilling($prevCustomerDataArr['default_billing']);
        }
        if (!array_key_exists('addresses', $customerArr)
            && null !== $prevCustomerDataArr
            && array_key_exists('default_shipping', $prevCustomerDataArr)
        ) {
            $customerModel->setDefaultShipping($prevCustomerDataArr['default_shipping']);
        }
        $this->setValidationFlag($customerArr, $customerModel);
        $customerModel->save();
        $this->customerRegistry->push($customerModel);
        $customerId = $customerModel->getId();
        if (!$customer->getAddresses()
            && $delegatedNewOperation
            && $delegatedNewOperation->getCustomer()->getAddresses()
        ) {
            $customer->setAddresses($delegatedNewOperation->getCustomer()->getAddresses());
        }
        if ($customer->getAddresses() !== null && !$customerModel->getData('ignore_validation_flag')) {
            if ($customer->getId()) {
                $existingAddresses = $this->getById($customer->getId())->getAddresses();
                $getIdFunc = function ($address) {
                    return $address->getId();
                };
                $existingAddressIds = array_map($getIdFunc, $existingAddresses);
            } else {
                $existingAddressIds = [];
            }
            $savedAddressIds = [];
            foreach ($customer->getAddresses() as $address) {
                $address->setCustomerId($customerId)
                    ->setRegion($address->getRegion());
                $this->addressRepository->save($address);
                if ($address->getId()) {
                    $savedAddressIds[] = $address->getId();
                }
            }
            $addressIdsToDelete = array_diff($existingAddressIds, $savedAddressIds);
            foreach ($addressIdsToDelete as $addressId) {
                $this->addressRepository->deleteById($addressId);
            }
        }
        $this->customerRegistry->remove($customerId);
        $savedCustomer = $this->get($customer->getEmail(), $customer->getWebsiteId());
        
        // Below event excluded:

        // $this->eventManager->dispatch(
        //     'customer_save_after_data_object',
        //     [
        //         'customer_data_object' => $savedCustomer,
        //         'orig_customer_data_object' => $prevCustomerData,
        //         'delegate_data' => $delegatedNewOperation ? $delegatedNewOperation->getAdditionalData() : [],
        //     ]
        // );

        return $savedCustomer;
    }


    private function populateCustomerWithSecureData($customerModel, $passwordHash = null)
    {
        if ($customerModel->getId()) {
            $customerSecure = $this->customerRegistry->retrieveSecureData($customerModel->getId());

            $customerModel->setRpToken($passwordHash ? null : $customerSecure->getRpToken());
            $customerModel->setRpTokenCreatedAt($passwordHash ? null : $customerSecure->getRpTokenCreatedAt());
            $customerModel->setPasswordHash($passwordHash ?: $customerSecure->getPasswordHash());

            $customerModel->setFailuresNum($customerSecure->getFailuresNum());
            $customerModel->setFirstFailure($customerSecure->getFirstFailure());
            $customerModel->setLockExpires($customerSecure->getLockExpires());
        } elseif ($passwordHash) {
            $customerModel->setPasswordHash($passwordHash);
        }

        if ($passwordHash && $customerModel->getId()) {
            $this->customerRegistry->remove($customerModel->getId());
        }
    }

    private function setValidationFlag($customerArray, $customerModel)
    {
        if (isset($customerArray['ignore_validation_flag'])) {
            $customerModel->setData('ignore_validation_flag', true);
        }
    }
}
<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Controller\Adminhtml\Generate;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\GiftCards\Api\Data\GiftCardsInterface;
use Magento\Store\Model\Store;

class Save extends \Magento\Backend\App\Action
{

    /**
     * @var \MageWorx\GiftCards\Model\GiftCardsRepository
     */
    protected $giftCardsRepository;

    /**
     * @var \MageWorx\GiftCards\Model\GiftCardsFactory
     */
    protected $giftCardsFactory;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param \MageWorx\GiftCards\Model\GiftCardsRepository $giftCardsRepository
     * @param \MageWorx\GiftCards\Model\GiftCardsFactory $giftCardsFactory
     */
    public function __construct(
        Context $context,
        \MageWorx\GiftCards\Model\GiftCardsRepository $giftCardsRepository,
        \MageWorx\GiftCards\Model\GiftCardsFactory $giftCardsFactory
    ) {

        $this->giftCardsRepository = $giftCardsRepository;
        $this->giftCardsFactory    = $giftCardsFactory;
        parent::__construct($context);
    }

    public function execute()
    {
         error_log("expireDate");
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->getPostValue()) {
             error_log("expireDate1");
            $model           = $this->giftCardsFactory->create();
            $count           = $this->getRequest()->getParam('giftcards_count');
            $amount          = $this->getRequest()->getParam('giftcards_amount');
            $currency        = $this->getRequest()->getParam('card_currency');
            $status          = $this->getRequest()->getParam(
                'giftcards_status',
                \MageWorx\GiftCards\Model\GiftCards::STATUS_ACTIVE
            );
            $type            = $this->getRequest()->getParam(
                'giftcards_type',
                \MageWorx\GiftCards\Model\GiftCards::TYPE_EMAIL
            );
            $customerGroups  = $this->getRequest()->getParam('customer_group_id');
            $expireDate      = $this->getRequest()->getParam(
                \MageWorx\GiftCards\Api\Data\GiftCardsInterface::EXPIRE_DATE
            );
             error_log("expireDate2".$expireDate);
            $storeIds        = $this->getRequest()->getParam('store_id');
            $storeIdForEmail = $this->getRequest()->getParam(
                \MageWorx\GiftCards\Api\Data\GiftCardsInterface::STORE_ID_FOR_EMAIL
            );

            try {
                 error_log("expireDate3");
                $this->validateStoreIdForEmailSending($storeIdForEmail, (array)$storeIds);

                for ($i = 0; $i < $count; $i++) {
                     error_log("expireDate4");
                    $model->setData([]);
                    $model->setCardAmount($amount);
                    $model->setCardCurrency($currency);
                    $model->setCardType($type);
                    $model->setCardStatus($status);
                    $model->setCustomerGroupId($customerGroups);
                    $model->setStoreId($storeIds);
                    $model->setStoreIdForEmailSending($storeIdForEmail);
                    $model->setExpireDate($expireDate);
                    $this->giftCardsRepository->save($model);
                }

                $this->messageManager->addSuccessMessage(__($count . ' Gift Cards was successfully generated'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                 error_log("expireDate5");
                return $resultRedirect->setPath('*/giftcards/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while generate the Gift Card.'));
            }

            return $resultRedirect->setPath('*/*/');
        }

        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageWorx_GiftCards::mageworx_giftcards_generate');
    }

    /**
     * @param GiftCardsInterface $model
     * @return bool
     * @throws LocalizedException
     */

    /**
     * @param int|null|string $storeIdForEmail
     * @param array $storeIds
     * @return bool
     * @throws LocalizedException
     */
    protected function validateStoreIdForEmailSending($storeIdForEmail, $storeIds)
    {
        if ($storeIdForEmail && array_intersect([Store::DEFAULT_STORE_ID, $storeIdForEmail], $storeIds)) {
            return true;
        }

        throw new LocalizedException(__('Send Email From must be one of the Store Views'));
    }
}
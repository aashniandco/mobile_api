<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Controller\Adminhtml\GiftCards;

use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Ui\Component\MassAction\Filter;
use MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory;

abstract class MassAction extends \Magento\Backend\App\Action
{
    /**
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var string
     */
    protected $successMessage = 'Mass Action successful on %1 records';

    /**
     * @var string
     */
    protected $errorMessage = 'Mass Action failed';

    /**
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * MassAction constructor.
     *
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     */
    public function __construct(
        Filter $filter,
        CollectionFactory $collectionFactory,
        Context $context
    ) {
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @param \MageWorx\GiftCards\Model\GiftCards $giftcard
     * @return $this
     */
    abstract protected function doTheAction($giftcard);

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection     = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->count();

            foreach ($collection as $attachment) {
                $this->doTheAction($attachment);
            }
            $this->messageManager->addSuccessMessage(__($this->successMessage, $collectionSize));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __($this->errorMessage));
        }
        $redirectResult = $this->resultRedirectFactory->create();
        $redirectResult->setPath('mageworx_giftcards/*/index');

        return $redirectResult;
    }
}

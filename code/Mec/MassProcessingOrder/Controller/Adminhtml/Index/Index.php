<?php
declare(strict_types=1);

namespace Mec\MassProcessingOrder\Controller\Adminhtml\Index;



use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Class Index
 */
class Index extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
    }

    /**
     * Hold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countDeleteOrder = 0;
        $model = $this->_objectManager->create('Magento\Sales\Model\Order');
        foreach ($collection->getItems() as $order) {
            if (!$order->getEntityId()) {
                continue;
            }
            $loadedOrder = $model->load($order->getEntityId());
	    #$loadedOrder->delete();
	    $loadedOrder->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
    	    $loadedOrder->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $loadedOrder->save();
	    $countDeleteOrder++;
        }
        $countNonDeleteOrder = $collection->count() - $countDeleteOrder;

        if ($countNonDeleteOrder && $countDeleteOrder) {
            $this->messageManager->addError(__('%1 order(s) not able to change status', $countNonDeleteOrder));
        } elseif ($countNonDeleteOrder) {
            $this->messageManager->addError(__('No order(s) were changed to Processing.'));
        }

        if ($countDeleteOrder) {
            $this->messageManager->addSuccess(__('You have changed status Processing for %1 order(s).', $countDeleteOrder));
        }

#        $resultRedirect = $this->resultRedirectFactory->create();
#        $resultRedirect->setPath($this->getComponentRefererUrl());
#        return $resultRedirect;
	return $this->_redirect('sales/order/index');

    }
}


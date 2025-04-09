<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ndsl\Orderexport\Controller\Adminhtml\Order;

//use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\Filesystem\DirectoryList;
class ExportOrderStatus extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    const ENCLOSURE = '"';
    const DELIMITER = ',';

    protected $_directoryList;
    public $_resource;
    private $deploymentConfig;
    private $objectManager;

    public function __construct(
        Context $context,
        ResourceConnection $resource,
        Filter $filter, 
        CollectionFactory $collectionFactory,
        DeploymentConfig $deploymentConfig,
        DirectoryList $directory_list
    ){
        parent::__construct($context , $filter);
        $this->_resource = $resource;
        $this->deploymentConfig = $deploymentConfig;
        $this->collectionFactory = $collectionFactory;
        $this->_directoryList = $directory_list;
    	$this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Export selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        //die("controller found new massation");
        if (!file_exists($this->_directoryList->getRoot().'/pub/media/orderexport')) {
            mkdir($this->_directoryList->getRoot().'/pub/media/orderexport', 0777, true);
        }

        $todayDate = date('Y_m_d_H_i_s', time());
        $fileName = $this->_directoryList->getRoot().'/pub/media/orderexport/orderexport'.$todayDate.'.csv';

        $fp = fopen($fileName, 'w');
        $this->writeHeadRow($fp);

        $countOrderExport = 0;
        foreach ($collection->getItems() as $_order) {
          $orderId = $_order->getId();
          if ($orderId) {
            $order = $this->objectManager->create('\Magento\Sales\Model\Order')->load($_order->getId());
            $this->writeOrder($order, $fp);
            $incId = $order->getIncrementId();
            $countOrderExport++;
          }
        }
        fclose($fp);

        $this->downloadCsv($fileName);
        $this->messageManager->addSuccess(__('We Exported %1 order(s).', $countOrderExport));
    }

    public function downloadCsv($file)
    {
        $csvExporter = $this->objectManager->create('Magento\Framework\App\Response\Http\FileFactory');
        $csvExporter->create(
            basename($file),
            [
                'type'  => "filename",
                'value' => $file,
                'rm'    => true, // True => File will be remove from directory after download.
            ],
            DirectoryList::MEDIA,
            'text/csv',
            null
        );
        /*if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();flush();
            readfile($file);
        }*/
    }

    protected function writeHeadRow($fp)
    {
        fputcsv($fp, $this->getHeadRowValues(), self::DELIMITER, self::ENCLOSURE);
    }

    protected function getHeadRowValues()
    {
        return array(
            'Order Number',
            'Order Date',
            'Order Status',
            'Customer Name',
            'Customer Email',
            'Order Item Increment',
            'Designer',
            'Item SKU',
            'Item Status',
            'In Production',
            'Quality Control',
            'Shipped',
            'Custom Clearance',
            'Delivered',
            'Cancelled'
        );
    }

    protected function writeOrder($order, $fp)
    {
        $common = $this->getCommonOrderValues($order);


        $orderItems = $order->getAllVisibleItems();
        $itemInc = 0;
        $item = "";
        foreach ($orderItems as $item)
        {
          //  if (!$item->isDummy()) {
                $record = array_merge($common, $this->getOrderItemValues($item, $order, ++$itemInc));
                fputcsv($fp, $record, self::DELIMITER, self::ENCLOSURE);
          //  }
        }

    }

        protected function getCommonOrderValues($order)
    {
        $shippingAddress = !$order->getIsVirtual() ? $order->getShippingAddress() : null;
        $billingAddress = $order->getBillingAddress();

        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $methodTitle = $method->getTitle();
        $total_item_qty = $this->getTotalQtyItemsOrdered($order);

        $priceHelper = $this->objectManager->create('Magento\Framework\Pricing\Helper\Data');
        $objDate = $this->objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $country_name = $this->objectManager->create('\Magento\Directory\Model\Country');

        return array(
            $order->getIncrementId(),
            $objDate->date(new \DateTime($order->getCreatedAt()))->format('m-d-Y'),
            $order->getStatus(),
            $order->getData('customer_firstname')." ".$order->getData('customer_lastname'),
            $order->getData('customer_email')
        );

    }

        protected function getOrderItemValues($item, $order, $itemInc=1)
    {
        $custom_option = "";
        if($item->hasProductOptions()){
            if (array_key_exists("options",$item->getData('product_options'))){
                $option_coll = $item->getData('product_options')['options'];
                foreach ($option_coll as $cptions) {
                    $custom_option .= strip_tags($cptions['label']).";";
                }
            }
        }
#       $product = $this->objectManager->get('Magento\Catalog\Model\Product')->load($item->getId());
        $productRepository = $this->objectManager->get('\Magento\Catalog\Model\ProductRepository');
        $product = $productRepository->get($item->getSku());        
        $patterns = $product->getResource()->getAttribute("patterns")->getFrontend()->getValue($product);
        $theme = $product->getResource()->getAttribute("theme")->getFrontend()->getValue($product);
        $designerCode = $product->getDesignerCode();
        #unset($product);
        return array(
            $itemInc,
            $item->getName(),
            $item->getSku(),
        );
    }

    protected function getTotalQtyItemsOrdered($order)
    {

        $qty = 0;

        $orderedItems = $order->getAllVisibleItems();
        foreach ($orderedItems as $qitem)
        {
            //if (!$item->isDummy()) {
                $qty += (int)$qitem->getQtyOrdered();
            //}
        }

        return $qty;

    }
}
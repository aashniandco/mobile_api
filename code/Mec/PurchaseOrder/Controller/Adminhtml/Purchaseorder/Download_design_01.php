<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mec\PurchaseOrder\Controller\Adminhtml\Purchaseorder;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends \Magento\Backend\App\Action
{

    protected $dataPersistor;
    protected $fileFactory;
    protected $filesystem;
    protected $productrepository;  

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Api\ProductRepositoryInterface $productrepository,
        FileFactory $fileFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fileFactory = $fileFactory;
        $this->productrepository = $productrepository;
        parent::__construct($context);
    }

    public function getProductData($productid) {
       return $this->productrepository->getById($productid);
   }
   

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        //$this->poPdfTest($data);
       

        if ($data) {
            $id = $this->getRequest()->getParam('purchase_order_id');
        
            $model = $this->_objectManager->create(\Mec\PurchaseOrder\Model\PurchaseOrder::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Purchase Order no longer exists.'));
                //return $resultRedirect->setPath('*/*/');
                $orderId = $data['order_id'];
                return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
            }
        
            $model->setData($data);
        
            try {
                $model->save();
                //$this->poPdf($data);
                $this->poPdfTest($data);
                exit('Purchase Order Pdf has been created.');

                $this->messageManager->addSuccessMessage(__('You saved the Purchase Order.'));
                $this->dataPersistor->clear('mec_purchaseorder_purchase_order');
        
                //if ($this->getRequest()->getParam('back')) {
                //    return $resultRedirect->setPath('*/*/edit', ['purchase_order_id' => $model->getId()]);
                //}
                //return $resultRedirect->setPath('sales/order/');
                $orderId = $data['order_id'];
                return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Purchase Order.'));
            }
        
            $this->dataPersistor->set('mec_purchaseorder_purchase_order', $data);
            return $resultRedirect->setPath('*/*/edit', ['purchase_order_id' => $this->getRequest()->getParam('purchase_order_id')]);
        }
        return $resultRedirect->setPath('sales/order/');
    }

    public function poPdfTest($data)
     {
     $pdf = new \Zend_Pdf();
     $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
     $page = $pdf->pages[0]; // this will get reference to the first page.
     $style = new \Zend_Pdf_Style();
     $style->setLineColor(new \Zend_Pdf_Color_Rgb(0,0,0));
     $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
     $style->setFont($font,13);
     $page->setStyle($style);
     $width = $page->getWidth();
     $hight = $page->getHeight();
     $x = 30;
     $pageTopalign = 850;
     $this->y = 850 - 100;
     
     $style->setFont($font,14);
     $page->setStyle($style);
     $page->drawRectangle(30, $this->y + 10, $page->getWidth()-30, $this->y +70, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
     $style->setFont($font,20);
     $page->setStyle($style);

     $folderName = \Magento\Config\Model\Config\Backend\Image\Logo::UPLOAD_DIR;
     $path = $folderName . '/logo_email.png';

    // $folderName = "/var/www/html/aashni/pub/media/catalog/product/s/a";
     //$path = $folderName . '/sab16w002_a_.jpg';

     

     $pdfImage = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($path));

     $top = 830;
    //top border of the page
    $widthLimit = 170;
    //half of the page width
    $heightLimit = 30;
    //assuming the image is not a "skyscraper"
    $width = $pdfImage->getPixelWidth();
    $height = $pdfImage->getPixelHeight();

    //preserving aspect ratio (proportions)
    $ratio = $width / $height;
    if ($ratio > 1 && $width > $widthLimit) {
        $width = $widthLimit;
        $height = $width / $ratio;
    } elseif ($ratio < 1 && $height > $heightLimit) {
        $height = $heightLimit;
        $width = $height * $ratio;
    } elseif ($ratio == 1 && $height > $heightLimit) {
        $height = $heightLimit;
        $width = $widthLimit;
    }

    $y1 = $top - $height;
    $y2 = $top;
    $x1 = 210;
    $x2 = $x1 + $width;

     //exit('hello');
     $page->drawImage($pdfImage, $x1, $y1, $x2, $y2);

     //$page->drawText(__("AASHNI + CO"), $x + 200, $this->y+50, 'UTF-8');
     $style->setFont($font,9);
     $page->setStyle($style);
     $page->drawText(__("Aashni Ecommerce Pvt. Ltd."), $x + 215, $this->y+40, 'UTF-8');
     $page->drawText(__("1703 & 1704, Peninsula Park, Off. Veera Desai Road, Mumbai 400053, Maharashtra, India."), $x + 120, $this->y+30, 'UTF-8');
     $page->drawText(__("T: +91-8375036648 | Email: customercare@aashniandco.com"), $x + 160, $this->y+20, 'UTF-8');
     $page->drawRectangle(30, $this->y + 10, $page->getWidth()-30, $this->y +0, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
     $page->drawRectangle(30, $this->y + 0, $page->getWidth()-30, $this->y -20, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
     $style->setFont($font,15);
     $page->setStyle($style);
     $page->drawText(__("PURCHASE ORDER"), $x + 200, $this->y-15, 'UTF-8');

     $page->drawRectangle(30, $this->y -20, $page->getWidth()-30, $this->y -40, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
     $style->setFont($font,10);
     $page->setStyle($style);
     $po_date = date("jS F Y"); 
     $page->drawText(__("Date: %1",$po_date), $x + 35, $this->y-33, 'UTF-8');

    $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0));
    $page->setLineWidth(0.5);
    $page->drawLine($x + 168, $this->y -20, $x + 168, $this->y -40);

     $po_number = $data['po_number'];
     $page->drawText(__("PO Number: %1",$po_number), $x + 210, $this->y-33, 'UTF-8');

    $page->drawLine($x + 367, $this->y -20, $x + 367, $this->y -40);


     $gst_number = $data['gst_number'];
     $page->drawText(__("GST: %1",$gst_number), $x + 395, $this->y-33, 'UTF-8');

     //$page->drawLine($x + 180, $this->y - 20, $x + 180, $this->y – 40);
     //$page->drawLine($x + 330, $this->y - 20, $x + 330, $this->y – 40);

     $page->drawRectangle(30, $this->y -40, $page->getWidth()-30, $this->y -110, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
     $style->setFont($font,10);

     $page->drawText(__("Vendor:"), $x + 10, $this->y-50, 'UTF-8');
     $page->drawText(__("Merchandiser's Details:"), $x + 180, $this->y-50, 'UTF-8');
     $page->drawText(__("Ship to:"), $x + 380, $this->y-50, 'UTF-8');
     
     $style->setFont($font,8);
     $page->setStyle($style);

     $vendor = $data['vendor'];
     $merchandiser_details = $data['merchandiser_details'];

     $page->drawText(__($vendor), $x + 10, $this->y-70, 'UTF-8');

     $page->drawLine($x + 168, $this->y -40, $x + 168, $this->y -110);

     $textChunk = wordwrap($merchandiser_details, 50, "\n"); 
     $line = $this->y-70;
     //$page->drawText(__($merchandiser_details), $x + 220, $this->y-60, 'UTF-8');
     foreach(explode("\n", $textChunk) as $textLine){
        if ($textLine!=='') {
            $page->drawText(strip_tags(ltrim($textLine)), $x + 180, $line, 'UTF-8');
            $line -=10;
        }
    }

    $page->drawLine($x + 367, $this->y -40, $x + 367, $this->y -110);  

    $ship_to = "Aashni Ecommerce Pvt. Ltd
                   1703 & 1704, Peninsula Park,
             veera Desai road, near Fun Republic,
                Andheri (W), Mumbai-400053.";

    $textChunk = wordwrap($ship_to, 50, "\n");
    $line = $this->y-70;
     //$page->drawText(__($ship_to), $x + 400, $this->y-60, 'UTF-8');
   
    foreach(explode("\n", $textChunk) as $textLine){
        if ($textLine!=='') {
            $page->drawText(strip_tags(ltrim($textLine)), $x + 380, $line, 'UTF-8');
            $line -=10;
        }
    }

    $page->drawRectangle(30, $this->y - 110, $page->getWidth()-30, $this->y -120, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

    $page->drawRectangle(30, $this->y - 120, $page->getWidth()-30, $this->y -140, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

    $style->setFont($font,10);
    $page->setStyle($style);

    $page->drawText(__("S.No"), $x + 5, $this->y-133, 'UTF-8');
    $page->drawLine($x + 30, $this->y -120, $x + 30, $this->y -200);

    $page->drawText(__("Order No."), $x + 35, $this->y-133, 'UTF-8');
    $page->drawLine($x + 80, $this->y -120, $x + 80, $this->y -200);

    $page->drawText(__("SKU"), $x + 100, $this->y-133, 'UTF-8');
    $page->drawLine($x + 145, $this->y -120, $x + 145, $this->y -200);

    $page->drawText(__("Vendor Code"), $x + 150, $this->y-133, 'UTF-8');
    $page->drawLine($x + 210, $this->y -120, $x + 210, $this->y -200);

    $page->drawText(__("Description"), $x + 260, $this->y-133, 'UTF-8');
    $page->drawLine($x + 367, $this->y -120, $x + 367, $this->y -200);

    $page->drawText(__("Size"), $x + 380, $this->y-133, 'UTF-8');
    $page->drawLine($x + 415, $this->y -120, $x + 415, $this->y -200);
    $page->drawText(__("Price"), $x + 430, $this->y-133, 'UTF-8');
    $page->drawLine($x + 470, $this->y -120, $x + 470, $this->y -200);
    $page->drawText(__("Delivery Date"), $x + 475, $this->y-133, 'UTF-8');


    $page->drawRectangle(30, $this->y - 140, $page->getWidth()-30, $this->y -200, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

    $style->setFont($font,8);
    $page->setStyle($style);

    $sl = 1;
    $order_id = $data['order_increment_id'];
    $sku = $data['sku'];
    $vendor_code = $data['vendor_code'];
    $description = $data['description'];
    $size = $data['size'];
    $price = $data['price'];
    $delivery_date = $data['delivery_date'];

    $page->drawText(__($sl), $x + 10, $this->y-160, 'UTF-8');
    $page->drawText(__($order_id), $x + 35, $this->y-160, 'UTF-8');

    $textChunk = wordwrap($sku, 5, "\n");
    $line = $this->y-160;    
   
    foreach(explode("\n", $textChunk) as $textLine){
        if ($textLine!=='') {
            $page->drawText(strip_tags(ltrim($textLine)), $x + 85, $line, 'UTF-8');
            $line -=10;
        }
    }


    //$page->drawText(__($sku), $x + 90, $this->y-160, 'UTF-8');


    $page->drawText(__($vendor_code ), $x + 150, $this->y-160, 'UTF-8');

    $textChunk = wordwrap($description, 50, "\n");
    $line = $this->y-160;    
   
    foreach(explode("\n", $textChunk) as $textLine){
        if ($textLine!=='') {
            $page->drawText(strip_tags(ltrim($textLine)), $x + 225, $line, 'UTF-8');
            $line -=10;
        }
    }
    //$page->drawText(__($description), $x + 220, $this->y-160, 'UTF-8');

    $page->drawText(__($size), $x + 370, $this->y-160, 'UTF-8');
    $page->drawText(__($price), $x + 425, $this->y-160, 'UTF-8');
    $page->drawText(__($delivery_date), $x + 485, $this->y-160, 'UTF-8');

    $page->drawRectangle(30, $this->y - 200, $page->getWidth()-30, $this->y -220, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
    $page->drawRectangle(30, $this->y - 220, $page->getWidth()-30, $this->y -400, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

    $style->setFont($font,10);
    $page->setStyle($style);

    $page->drawText(__("Product Image"), $x + 10, $this->y-230, 'UTF-8');
    $page->drawText(__("Special Instructions"), $x + 200, $this->y-230, 'UTF-8');
    $page->drawText(__("Measurements"), $x + 400, $this->y-230, 'UTF-8');

    $style->setFont($font,9);
    $page->setStyle($style);

     $product_id = $data['product_id'];
     $product = $this->getProductData($product_id);
     $image =  $product->getData('image');
     if($image != ""){
     $folderName = "/var/www/html/aashni/pub/media/catalog/product";
     $path = $folderName . $image;

     

     $pdfImage = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($path));

     $top = $this->y - 250;
    //top border of the page
    $widthLimit = 80;
    //half of the page width
    $heightLimit = 120;
    //assuming the image is not a "skyscraper"
    $width = $pdfImage->getPixelWidth();
    $height = $pdfImage->getPixelHeight();

    //preserving aspect ratio (proportions)
    $ratio = $width / $height;
    if ($ratio > 1 && $width > $widthLimit) {
        $width = $widthLimit;
        $height = $width / $ratio;
    } elseif ($ratio < 1 && $height > $heightLimit) {
        $height = $heightLimit;
        $width = $height * $ratio;
    } elseif ($ratio == 1 && $height > $heightLimit) {
        $height = $heightLimit;
        $width = $widthLimit;
    }

    $y1 = $top - $height;
    $y2 = $top;
    $x1 = 50;
    $x2 = $x1 + $width;

     //exit('hello');
     //$page->drawImage($pdfImage, $x1, $y1, $x2, $y2);

    }

     $page->drawImage($pdfImage, $x1, $y1, $x2, $y2);

    //$page->drawText(__($image), $x + 10, $this->y-250, 'UTF-8');

    $special_instruction = $data['special_instructions'];

    $textChunk = wordwrap($special_instruction, 50, "\n");
    $line = $this->y-250;    
   
    foreach(explode("\n", $textChunk) as $textLine){
        if ($textLine!=='') {
            $page->drawText(strip_tags(ltrim($textLine)), $x + 200, $line, 'UTF-8');
            $line -=10;
        }
    }

    $measurements = $data['measurements'];
    //$page->drawText(__("Special Instructions"), $x + 200, $this->y-420, 'UTF-8');
    $textChunk = wordwrap($measurements, 30, "\n");
    $line = $this->y-250;    
   
    foreach(explode("\n", $textChunk) as $textLine){
        if ($textLine!=='') {
            $page->drawText(strip_tags(ltrim($textLine)), $x + 400, $line, 'UTF-8');
            $line -=10;
        }
    }
    //$page->drawText(__("Measurements"), $x + 400, $this->y-420, 'UTF-8');


     $page->drawRectangle(375, $this->y - 350, $page->getWidth()-30, $this->y -400, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

     $style->setFont($font,10);
     $page->setStyle($style);

     $page->drawText(__("Authorised Signatory:"), $x + 400, $this->y-360, 'UTF-8');

     $style->setFont($font,9);
     $page->setStyle($style);
     $po_creator = $data['authorised_signatory']; //$data['authorised_signatory'];
     $page->drawText(__($po_creator), $x + 420, $this->y-380, 'UTF-8');
     $page->drawText(__("(Head Of Operations) Aashni+Co"), $x + 380, $this->y-390, 'UTF-8');



     $page->drawRectangle(30, $this->y - 400, $page->getWidth()-30, $this->y -430, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

     $style->setFont($font,10);
     $page->setStyle($style);
     $page->drawText(__("Thank You"), ($page->getWidth()/2)-50, $this->y-415);


     $pro = $sku;

     $fileName = $data['order_increment_id'].'_'.$pro.'_'.'purchase_order.pdf';

     $this->fileFactory->create(
    'pdf/'.$fileName,
    $pdf->render(),
    \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR, // this pdf will be saved in var directory with the name meetanshi.pdf
    'application/pdf'
     );
 
    }

     public function poPdf($data)
     {
     $pdf = new \Zend_Pdf();
     $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
     $page = $pdf->pages[0]; // this will get reference to the first page.
     $style = new \Zend_Pdf_Style();
     $style->setLineColor(new \Zend_Pdf_Color_Rgb(0,0,0));
     $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
     $style->setFont($font,13);
     $page->setStyle($style);
     $width = $page->getWidth();
     $hight = $page->getHeight();
     $x = 30;
     $pageTopalign = 850;
     $this->y = 850 - 100;

     $style->setFont($font,14);
     $page->setStyle($style);
     $page->drawRectangle(30, $this->y + 10, $page->getWidth()-30, $this->y +70, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

     $style->setFont($font,13);
     //$page->setStyle($style);
     //$page->drawText(__("Cutomer Details"), $x + 5, $this->y+50, 'UTF-8');
     $style->setFont($font,11);
     $page->setStyle($style);
     $vendor = $data['vendor'];
     $gst = $data['gst_number'];
     $page->drawText(__(" vendor Name : %1", $vendor), $x + 5, $this->y+20, 'UTF-8');
     $page->drawText(__("Email : %1","vendor@gmail.com"), $x + 5, $this->y+16, 'UTF-8');
     //$page->drawText(__("GST : %1",$gst), $x + 10, $this->y+10, 'UTF-8');

     $style->setFont($font,11);
     $page->setStyle($style);

     $page->drawText(__("PRODUCT NAME"), $x + 60, $this->y-10, 'UTF-8');
     $page->drawText(__("PRODUCT PRICE"), $x + 200, $this->y-10, 'UTF-8');
     $page->drawText(__("QTY"), $x + 310, $this->y-10, 'UTF-8');
     $page->drawText(__("SUB TOTAL"), $x + 440, $this->y-10, 'UTF-8');

     $style->setFont($font,10);
     $page->setStyle($style);
     $add = 9;
     $price = $data['price'];
     $qty = 1;
     $total = ($price*$qty);
     $page->drawText($price, $x + 210, $this->y-30, 'UTF-8');
     $page->drawText($qty, $x + 330, $this->y-30, 'UTF-8');
     $page->drawText($total, $x + 470, $this->y-30, 'UTF-8');
     $pro = $data['sku'];
     $page->drawText($pro, $x + 65, $this->y-30, 'UTF-8');

     $page->drawRectangle(30, $this->y -62, $page->getWidth()-30, $this->y + 10, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

     $page->drawRectangle(30, $this->y -62, $page->getWidth()-30, $this->y - 100, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

     $page->drawRectangle(30, $this->y -62, $page->getWidth()-30, $this->y - 150, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);

     $style->setFont($font,15);
     $page->setStyle($style);
     $page->drawText(__("Total : %1", $total), $x + 435, $this->y-85, 'UTF-8');

     $style->setFont($font,10);
     $page->setStyle($style);
     $page->drawText(__("AASHNI AND CO"), ($page->getWidth()/2)-50, $this->y-200);



     $fileName = $data['order_increment_id'].'_'.$pro.'_'.'purchase_order.pdf';

     $this->fileFactory->create(
    $fileName,
    $pdf->render(),
    \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR, // this pdf will be saved in var directory with the name meetanshi.pdf
    'application/pdf'
     );
 
    }
}


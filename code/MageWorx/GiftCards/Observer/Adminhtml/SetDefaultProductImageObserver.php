<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use MageWorx\GiftCards\Model\Product\Type\GiftCards as ProductTypeGiftCards;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\View\Asset\Repository as ViewAssetRepository;
use Magento\Framework\Exception\LocalizedException;

class SetDefaultProductImageObserver implements ObserverInterface
{
    const NOT_SELECTED_IMAGE = 'no_selection';

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var IoFile
     */
    protected $ioFile;

    /**
     * @var ViewAssetRepository
     */
    protected $assetRepo;

    /**
     * @var string
     */
    protected $defaultProductImage = "MageWorx_GiftCards::images/default-product-image.png";

    /**
     * SetDefaultProductImageObserver constructor.
     *
     * @param DirectoryList $directoryList
     * @param IoFile $ioFile
     * @param ViewAssetRepository $assetRepo
     */
    public function __construct(
        DirectoryList $directoryList,
        IoFile $ioFile,
        ViewAssetRepository $assetRepo
    ) {
        $this->directoryList = $directoryList;
        $this->ioFile        = $ioFile;
        $this->assetRepo     = $assetRepo;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $observer->getEvent()->getProduct();

        if ($product->getTypeId() !== ProductTypeGiftCards::TYPE_CODE || $product->getId()) {
            return;
        }

        $mediaAttributes = $this->getMediaAttributes($product);

        if (empty($mediaAttributes)) {
            return;
        }

        $fileDir  = $this->getFileDir();
        $fileName = basename($this->defaultProductImage);
        $filePath = $fileDir . DIRECTORY_SEPARATOR . $fileName;

        if (!$this->ioFile->fileExists($filePath)) {
            $asset     = $this->assetRepo->createAsset($this->defaultProductImage);
            $imagePath = $asset->getSourceFile();

            $this->ioFile->checkAndCreateFolder($fileDir);

            $result = $this->ioFile->read($imagePath, $filePath);

            if (!$result) {
                throw new LocalizedException(__('The issue appeared while creating "%1" file.', $filePath));
            }
        }

        $product->addImageToMediaGallery($filePath, $mediaAttributes, false, false);

        return;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getMediaAttributes($product)
    {
        $mediaAttributes = [];

        $image      = $product->getImage();
        $smallImage = $product->getSmallImage();
        $thumbnail  = $product->getThumbnail();

        if (!$image || $image == self::NOT_SELECTED_IMAGE) {
            $mediaAttributes[] = 'image';
        }

        if (!$smallImage || $smallImage == self::NOT_SELECTED_IMAGE) {
            $mediaAttributes[] = 'small_image';
        }

        if (!$thumbnail || $thumbnail == self::NOT_SELECTED_IMAGE) {
            $mediaAttributes[] = 'thumbnail';
        }

        return $mediaAttributes;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function getFileDir()
    {
        $mediaDir = $this->directoryList->getPath(DirectoryList::MEDIA);
        $ds       = DIRECTORY_SEPARATOR;

        return $mediaDir . $ds . 'mageworx' . $ds . 'giftcards' . $ds . 'images';
    }
}

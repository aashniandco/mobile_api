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
 * @package    Bss_Quickview
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Quickview\Controller\Catalog\Product;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product\Compare;
use Magento\Catalog\Model\Product\Compare\ItemFactory;
use Magento\Catalog\Model\Product\Compare\ListCompare;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory;
use Magento\Catalog\Model\Session;
use Magento\Catalog\ViewModel\Product\Checker\AddToCompareAvailability;
use Magento\Customer\Model\Visitor;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Add
 *
 * @package Bss\Quickview\Controller\Catalog\Product
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends \Magento\Catalog\Controller\Product\Compare\Add
{
    /**
     * @var Escaper
     */
    protected $escaper;
    /**
     * @var Compare
     */
    protected $compare;

    /**
     * Add constructor.
     * @param Escaper $escaper
     * @param Compare $compare
     * @param Context $context
     * @param ItemFactory $compareItemFactory
     * @param CollectionFactory $itemCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Visitor $customerVisitor
     * @param ListCompare $catalogProductCompareList
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param PageFactory $resultPageFactory
     * @param ProductRepositoryInterface $productRepository
     * @param AddToCompareAvailability|null $compareAvailability
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Escaper $escaper,
        Compare $compare,
        Context $context,
        ItemFactory $compareItemFactory,
        CollectionFactory $itemCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        Visitor $customerVisitor,
        ListCompare $catalogProductCompareList,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        PageFactory $resultPageFactory,
        ProductRepositoryInterface $productRepository,
        AddToCompareAvailability $compareAvailability = null
    ) {
        $this->compare = $compare;
        $this->escaper = $escaper;
        parent::__construct(
            $context,
            $compareItemFactory,
            $itemCollectionFactory,
            $customerSession,
            $customerVisitor,
            $catalogProductCompareList,
            $catalogSession,
            $storeManager,
            $formKeyValidator,
            $resultPageFactory,
            $productRepository,
            $compareAvailability
        );
    }

    /**
     * Add product
     *
     * @return Redirect|ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setRefererUrl();
        }

        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId && ($this->_customerVisitor->getId() || $this->_customerSession->isLoggedIn())) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productId, false, $storeId);
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('The product that was requested doesn\'t exist. Verify the product and try again.'));
                return $resultRedirect->setRefererOrBaseUrl();
            }

            if ($product) {
                $this->_catalogProductCompareList->addProduct($product);
                $productName = $this->escaper->escapeHtml($product->getName());
                $this->messageManager->addSuccessMessage(__('You added product %1 to the comparison list.', $productName));
                $this->_eventManager->dispatch('catalog_product_compare_add_product', ['product' => $product]);
            }

            $this->compare->calculate();
        }
        $params = $this->getRequest()->getParams();
        if (isset($params['bssquickview']) && $params['bssquickview'] == 1) {
            return $resultRedirect->setPath($product->getUrlModel()->getUrl($product));
        }
        return $resultRedirect->setRefererOrBaseUrl();
    }
}

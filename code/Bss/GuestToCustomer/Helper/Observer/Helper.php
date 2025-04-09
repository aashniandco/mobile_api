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
 * @package    BSS_GuestToCustomer
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GuestToCustomer\Helper\Observer;

use Bss\GuestToCustomer;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class Helper extends AbstractHelper
{

    /**
     * Helper Email
     * @var GuestToCustomer\Helper\Email
     */
    protected $helperEmail;

    /**
     * Helper Config Admin
     * @var GuestToCustomer\Helper\ConfigAdmin
     */
    protected $helperConfigAdmin;

    /**
     * StoreManager Interface
     * @var StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * Filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var IndexerRegistry
     */
    protected $indexer;

    /**
     * @var SessionManagerInterface
     */
    protected $coreSession;

    /**
     * Helper constructor.
     * @param GuestToCustomer\Helper\Email $helperEmail
     * @param GuestToCustomer\Helper\ConfigAdmin $helperConfigAdmin
     * @param StoreManagerInterface $storeManager
     * @param Filter $filter
     * @param IndexerRegistry $indexer
     * @param SessionManagerInterface $coreSession
     * @param Context $context
     */
    public function __construct(
        GuestToCustomer\Helper\Email $helperEmail,
        GuestToCustomer\Helper\ConfigAdmin $helperConfigAdmin,
        StoreManagerInterface $storeManager,
        Filter $filter,
        IndexerRegistry $indexer,
        SessionManagerInterface $coreSession,
        Context $context
    ) {
        $this->helperEmail = $helperEmail;
        $this->helperConfigAdmin = $helperConfigAdmin;
        $this->storeManager = $storeManager;
        $this->filter = $filter;
        $this->indexer = $indexer;
        $this->coreSession = $coreSession;
        parent::__construct($context);
    }

    /**
     * GetHelperEmail
     *
     * @return GuestToCustomer\Helper\Email
     */
    public function getHelperEmail()
    {
        return $this->helperEmail;
    }

    /**
     * GetHelperConfigAdmin
     *
     * @return GuestToCustomer\Helper\ConfigAdmin
     */
    public function getHelperConfigAdmin()
    {
        return $this->helperConfigAdmin;
    }

    /**
     * GetStoreManager
     *
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * GetLogger
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * GetFilter
     *
     * @return Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * GetIndexer
     *
     * @return IndexerRegistry
     */
    public function getIndexer()
    {
        return $this->indexer;
    }

    /**
     * GetCoreSession
     *
     * @return SessionManagerInterface
     */
    public function getCoreSession()
    {
        return $this->coreSession;
    }
}

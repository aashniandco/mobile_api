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
namespace Bss\GuestToCustomer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\Helper\Context;

class MassActionHelper extends AbstractHelper
{

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
     * MassActionHelper constructor.
     * @param Filter $filter
     * @param IndexerRegistry $indexer
     * @param SessionManagerInterface $coreSession
     * @param Context $context
     */
    public function __construct(
        Filter $filter,
        IndexerRegistry $indexer,
        SessionManagerInterface $coreSession,
        Context $context
    ) {
        $this->filter = $filter;
        $this->indexer = $indexer;
        $this->coreSession = $coreSession;
        parent::__construct($context);
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

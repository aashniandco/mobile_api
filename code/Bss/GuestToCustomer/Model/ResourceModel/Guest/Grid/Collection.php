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
namespace Bss\GuestToCustomer\Model\ResourceModel\Guest\Grid;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Search\AggregationInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Api\Search\SearchResultInterface;
use \Bss\GuestToCustomer\Model\ResourceModel;

class Collection extends ResourceModel\Guest\Collection implements SearchResultInterface
{
    /**
     * Event Prefix
     * @var AbstractDb
     */
    protected $eventPrefix;

    /**
     * Model
     * @var string
     */
    protected $model;

    /**
     * Event Object
     * @var string
     */
    protected $eventObject;

    /**
     * Aggregations
     *
     * @var AggregationInterface
     */
    private $aggregations;

    /**
     * Collection constructor.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param string $eventPrefix
     * @param string $eventObject
     * @param string $model
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     * @return void
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $eventPrefix,
        $eventObject,
        $model = Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface  $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->eventPrefix = $eventPrefix;
        $this->eventObject = $eventObject;
        $this->model = $model;
    }

    /**
     * Construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            Document::class,
            \Bss\GuestToCustomer\Model\ResourceModel\Guest::class
        );
    }

    /**
     * GetAggregations
     *
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * Set Aggregations
     *
     * @param AggregationInterface $aggregations
     * @return void
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Get search criteria.
     *
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}

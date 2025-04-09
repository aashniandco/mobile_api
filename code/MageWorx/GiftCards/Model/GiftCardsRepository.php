<?php
/**
 *
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model;

use MageWorx\GiftCards\Api\Data\GiftCardsInterface;
use MageWorx\GiftCards\Model\ResourceModel\GiftCards\Collection;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Api\SearchCriteriaInterface;
use MageWorx\GiftCards\Api\Data\GiftCardsSearchResultsInterfaceFactory;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class GiftCardsRepository implements \MageWorx\GiftCards\Api\GiftCardsRepositoryInterface
{
    /**
     * @var GiftCardsFactory
     */
    protected $giftCardsFactory;

    /**
     * @var GiftCards[]
     */
    protected $instances = [];

    /**
     * @var GiftCards[]
     */
    protected $instancesByCode = [];

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \MageWorx\GiftCards\Model\ResourceModel\GiftCards
     */
    protected $resourceModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var GiftCardsSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var Serialize
     */
    protected $serializer;

    /**
     * GiftCardsRepository constructor.
     *
     * @param GiftCardsFactory $giftCardsFactory
     * @param ResourceModel\GiftCards\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ResourceModel\GiftCards $resourceModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param EventManager $eventManager
     * @param GiftCardsSearchResultsInterfaceFactory $searchResultsFactory
     * @param Serialize $serializer
     */
    public function __construct(
        GiftCardsFactory $giftCardsFactory,
        \MageWorx\GiftCards\Model\ResourceModel\GiftCards\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \MageWorx\GiftCards\Model\ResourceModel\GiftCards $resourceModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        EventManager $eventManager,
        GiftCardsSearchResultsInterfaceFactory $searchResultsFactory,
        Serialize $serializer
    ) {
        $this->giftCardsFactory      = $giftCardsFactory;
        $this->collectionFactory     = $collectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resourceModel         = $resourceModel;
        $this->storeManager          = $storeManager;
        $this->filterBuilder         = $filterBuilder;
        $this->eventManager          = $eventManager;
        $this->searchResultsFactory  = $searchResultsFactory;
        $this->serializer            = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getByCode($giftCardCode, $editMode = false, $storeId = null, $forceReload = false)
    {
        $cacheKey = $this->getCacheKey([$editMode, $storeId]);
        if (!isset($this->instancesByCode[$giftCardCode][$cacheKey]) || $forceReload) {
            $giftCard = $this->giftCardsFactory->create();

            $giftCardId = $this->resourceModel->getIdByCardCode($giftCardCode);
            if (!$giftCardId) {
                throw new NoSuchEntityException(__('Requested Gift Card doesn\'t exist'));
            }
            if ($editMode) {
                $giftCard->setData('_edit_mode', true);
            }
            $giftCard->load($giftCardId);
            $this->instancesByCode[$giftCardCode][$cacheKey] = $giftCard;
            $this->instances[$giftCardId][$cacheKey]         = $giftCard;
        }

        return $this->instancesByCode[$giftCardCode][$cacheKey];
    }

    /**
     * {@inheritdoc}
     */
    public function get($giftCardId, $editMode = false, $storeId = null, $forceReload = false)
    {
        $cacheKey = $this->getCacheKey([$editMode, $storeId]);
        if (!isset($this->instances[$giftCardId][$cacheKey]) || $forceReload) {
            $giftCard = $this->giftCardsFactory->create();
            if ($editMode) {
                $giftCard->setData('_edit_mode', true);
            }
            $giftCard->load($giftCardId);
            if (!$giftCard->getId()) {
                throw new NoSuchEntityException(__('Requested Gift Card doesn\'t exist'));
            }
            $this->instances[$giftCardId][$cacheKey]                    = $giftCard;
            $this->instancesByCode[$giftCard->getCardCode()][$cacheKey] = $giftCard;
        }

        return $this->instances[$giftCardId][$cacheKey];
    }

    /**
     * Get key for cache
     *
     * @param array $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }

        return sha1($this->serializer->serialize($serializeData));
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(GiftCardsInterface $giftCard, $saveOptions = false)
    {
        try {
            unset($this->instances[$giftCard->getId()]);
            unset($this->instancesByCode[$giftCard->getCardCode()]);
            $this->resourceModel->save($giftCard);
        } catch (\Magento\Eav\Model\Entity\Attribute\Exception $exception) {
            throw \Magento\Framework\Exception\InputException::invalidFieldValue(
                $exception->getAttributeCode(),
                $giftCard->getData($exception->getAttributeCode()),
                $exception
            );
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save Gift Card'));
        }

        unset($this->instances[$giftCard->getId()]);
        unset($this->instancesByCode[$giftCard->getCardCode()]);

        if (!$giftCard->getId()) {
            return;
        } else {
            return $this->get($giftCard->getId());
        }
    }

    /**
     * @param GiftCardsInterface $giftCard
     * @return GiftCardsInterface
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     */
    public function createWithSpecifiedCode(GiftCardsInterface $giftCard)
    {
        $this->checkRequiredData($giftCard);
        $this->checkGiftCardByCode($giftCard->getCardCode());

        $this->setDefaultData($giftCard);

        $giftCard->setStoreviewIds($giftCard->getStoreviewIds());
        $giftCard->isObjectNew(true);
        $giftCard->setId(null);

        $this->eventManager->dispatch('mageworx_giftcards_prepare_save', ['giftcard' => $giftCard]);

        $this->save($giftCard);

        return $giftCard;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\MageWorx\GiftCards\Api\Data\GiftCardsInterface $giftCard)
    {
        $cardCode = $giftCard->getCardCode();
        $cardId   = $giftCard->getId();
        try {
            unset($this->instances[$giftCard->getId()]);
            unset($this->instancesByCode[$giftCard->getCardCode()]);
            $this->resourceModel->delete($giftCard);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove Gift Card %1', $cardCode)
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($giftCardId)
    {
        $giftCard = $this->get($giftCardId);

        return $this->delete($giftCard);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByCode($giftCardCode)
    {
        $giftCard = $this->getByCode($giftCardCode);

        return $this->delete($giftCard);
    }

    /**
     * Clean internal product cache
     *
     * @return void
     */
    public function cleanCache()
    {
        $this->instances = null;
    }

    /**
     * @param string $giftCardCode
     * @return float|null
     * @throws NoSuchEntityException
     */
    public function getBalanceByCode($giftCardCode)
    {
        $giftCard = $this->getByCode($giftCardCode);

        return $giftCard->getCardBalance();
    }

    /**
     * @param string $giftCardCode
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStatusByCode($giftCardCode)
    {
        $giftCard = $this->getByCode($giftCardCode);

        return $giftCard->getCardStatusLabel();
    }

    /**
     * @param string $giftCardCode
     * @return string
     * @throws NoSuchEntityException
     */
    public function getExpireDateByCode($giftCardCode)
    {
        $giftCard = $this->getByCode($giftCardCode);

        return $giftCard->getExpireDate();
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @param bool $returnRawObjects
     * @return \MageWorx\GiftCards\Api\Data\GiftCardsSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria, $returnRawObjects = false)
    {
        /** @var \MageWorx\GiftCards\Api\Data\GiftCardsSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        /** @var \MageWorx\GiftCards\Model\ResourceModel\GiftCards\Collection $collection */
        $collection = $this->collectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter(
                    $filter->getField(),
                    [
                        $condition => $filter->getValue()
                    ]
                );
            }
        }

        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());

        $items = $collection->load()->getItems();

        if (is_array($items)) {
            $searchResults->setItems($items);
        }

        return $searchResults;
    }

    /**
     * @param GiftCardsInterface $giftCard
     * @throws InputException
     */
    protected function checkRequiredData(GiftCardsInterface $giftCard)
    {
        $requiredDataError = [];

        if (empty($giftCard->getCardCode())) {
            $requiredDataError[] = GiftCardsInterface::CARD_CODE;
        }

        if (empty($giftCard->getCardAmount())) {
            $requiredDataError[] = GiftCardsInterface::CARD_AMOUNT;
        }

        $this->processRequiredDataError($requiredDataError);
    }

    /**
     * @param array $requiredData
     * @throws InputException
     */
    protected function processRequiredDataError($requiredDataError)
    {
        if (!empty($requiredDataError)) {
            $exception = new InputException();

            foreach ($requiredDataError as $property) {
                $exception->addError(__('%propertyName is a required property.', $property));
            }

            if ($exception->wasErrorAdded()) {
                throw $exception;
            }
        }
    }

    /**
     * @param GiftCardsInterface $giftCard
     */
    protected function setDefaultData(GiftCardsInterface $giftCard)
    {
        if (empty($giftCard->getCardType())) {
            $giftCard->setCardType(\MageWorx\GiftCards\Model\GiftCards::TYPE_PRINT);
        }

        if (is_null($giftCard->getCardStatus())) {
            $giftCard->setCardStatus(\MageWorx\GiftCards\Model\GiftCards::STATUS_INACTIVE);
        }
    }

    /**
     * @param string $giftCardCode
     * @param bool $isUpdateProcess
     * @throws InputException
     */
    protected function checkGiftCardByCode($giftCardCode, $isUpdateProcess = false)
    {
        $giftCardId = $this->resourceModel->getIdByCardCode($giftCardCode);

        if ($giftCardId && !$isUpdateProcess) {
            throw new InputException(__('A gift card with code %giftCardCode already exists.', $giftCardCode));
        }
    }
}

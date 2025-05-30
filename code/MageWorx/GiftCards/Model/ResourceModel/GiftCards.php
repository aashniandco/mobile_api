<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model\ResourceModel;

class GiftCards extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Store model
     *
     * @var null|\Magento\Store\Model\Store
     */
    protected $store = null;

    /**
     * @var array
     */
    protected $tables;

    /**
     * @var array
     */
    protected $date;

    /**
     * construct data
     */
    protected function _construct()
    {
        $this->_init('mageworx_giftcards_card', 'card_id');
        $this->date   = date('Y-m-d H:i:s', time());
        $this->tables = [
            'store_id'          => 'mageworx_giftcards_store',
            'customer_group_id' => 'mageworx_giftcards_customer_group'
        ];
    }

    /**
     * Process page data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = ['card_id = ?' => (int)$object->getId()];

        foreach ($this->tables as $id => $table) {
            $this->getConnection()->delete($this->getTable($table), $condition);
        }

        return parent::_beforeDelete($object);
    }

    /**
     * Assign page to store views
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        foreach ($this->tables as $id => $table) {
            $old = $this->lookupIds($object->getId(), $id, $table);
            $new = $object->getData($id);

            $table = $this->getTable($table);
            if ($new && is_array($old) && is_array($new)) {
                $insert = array_diff($new, $old);
                $delete = array_diff($old, $new);

                if ($delete) {
                    $where = ['card_id = ?' => (int)$object->getId(), $id . ' IN (?)' => $delete];

                    $this->getConnection()->delete($table, $where);
                }

                if ($insert) {
                    $data = [];

                    foreach ($insert as $dataId) {
                        $data[] = ['card_id' => (int)$object->getId(), $id => (int)$dataId];
                    }

                    $this->getConnection()->insertMultiple($table, $data);
                }
            }
        }

        return parent::_afterSave($object);
    }

    /**
     * Perform operations after object load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            foreach ($this->tables as $id => $table) {
                $items = $this->lookupIds($object->getId(), $id, $table);
                $object->setData($id, $items);
            }
        }

        return parent::_afterLoad($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isObjectNew() && !$object->hasCardCode()) {
            $object->setCardCode($this->_getUniqueCardCode());
        }

        if ($object->isObjectNew()) {
            if (!$object->getCardBalance()) {
                $object->setCardBalance($object->getCardAmount());
            }

            $object->setCreatedTime($this->date);
        }

        if (strlen((string)$object->getCardCurrency()) != 3) {
            $object->setCardCurrency('');
        }

        $object->setCardCurrency(strtoupper($object->getCardCurrency()));

        $object->setUpdatedAt($this->date);

        return parent::_beforeSave($object);
    }

    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = 'card_code';
        }
        return parent::load($object, $value, $field);
    }

    /**
     * Get Gift Card identifier by Gift Card Code
     *
     * @param string $giftCardCode
     * @return int|false
     */
    public function getIdByCardCode($giftCardCode)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable(), 'card_id')->where('card_code = :giftCardCode');

        $bind = [':giftCardCode' => (string)$giftCardCode];

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Cms\Model\Page $object
     * @return \Magento\Framework\DB\Select
     */
    /* protected function _getLoadSelect($field, $value, $object)
     {
         $select = parent::_getLoadSelect($field, $value, $object);

         foreach ($this->tables as $id => $table) {
                 $select->join(
                 [$table . '_table' => $this->getTable($table)],
                     $this->getMainTable() . '.card_id = ' . $table . '_table.card_id',
                     []
                 );
         }

         return $select;
     }*/

    /**
     * Get store ids to which specified item is assigned
     *
     * @param $cardId
     * @param $id
     * @param $table
     * @return array
     */
    public function lookupIds($cardId, $id, $table)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable($table),
            $id
        )->where(
            'card_id = ?',
            (int)$cardId
        );

        return $connection->fetchCol($select);
    }

    /**
     * Set store model
     *
     * @param \Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * Retrive load select with filter bu card_code and card_state
     *
     * @param $cardCode
     * @param null $state
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getLoadByCardCodeSelect($cardCode, $state = null)
    {
        $select = $this->getConnection()->select()
                       ->from(['giftcards' => $this->getMainTable()])
                       ->where('giftcards.card_code = ?', $cardCode);
        if ($state !== null) {
            $select->where('giftcards.card_state = ?', $state);
        }

        return $select;
    }

    public function loadCustomerCardsWithOrders($email)
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
                                 ->from(['giftcards' => $this->getMainTable()])
                                 ->where('giftcards.mail_to_email = ? and orders.discounted is not null', $email)
                                 ->joinLeft(
                                     ['orders' => $this->getTable('mageworx_giftcard_order')],
                                     'orders.giftcard_id = giftcards.card_id',
                                     [
                                         'apply_order_id'     => 'orders.order_id',
                                         'discounted'         => 'orders.discounted',
                                         'apply_created_time' => 'orders.created_time'
                                     ]
                                 );

        $data = $connection->fetchAll($select);

        return $data;
    }

    protected function _getUniqueCardCode()
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $mask       = '#####-#####-#####';

        $cardCode = $mask;
        while (strpos($cardCode, '#') !== false) {
            $cardCode = substr_replace(
                $cardCode,
                $characters[random_int(0, strlen($characters) - 1)],
                strpos($cardCode, '#'),
                1
            );
        }

        return $cardCode;
    }

    /**
     * @return array
     */
    public function getGiftCardsStores()
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
                                 ->from($this->getTable('mageworx_giftcards_store'));

        return $connection->fetchAll($select);
    }

    /**
     * @param array $updateData
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateGiftCards($updateData)
    {
        $connection = $this->getConnection();
        $tableName  = $this->getMainTable();

        foreach ($updateData as $datum) {
            $cardStoresIds = $this->getStoresIds($datum);
            $cardGroupsIds = $this->getGroupsIds($datum);

            //update table mageworx_giftcards_card
            $cardWhere = ['card_code = ?' => $datum['card_code']];
            $connection->update($tableName, $datum, $cardWhere);

            // delete all rows from mageworx_giftcards_store by card_id
            $deleteWhere = ['card_id = ?' => (int)$datum['card_id']];
            $connection->delete($this->getTable('mageworx_giftcards_store'), $deleteWhere);

            if (!empty($cardStoresIds)) {
                //insert table mageworx_giftcards_store
                $this->insertGiftCardsStores($cardStoresIds, $datum['card_id']);
            }

            // delete all rows from mageworx_giftcards_customer_group by card_id
            $deleteWhere = ['card_id = ?' => (int)$datum['card_id']];
            $connection->delete($this->getTable('mageworx_giftcards_customer_group'), $deleteWhere);

            if (!empty($cardGroupsIds)) {
                //insert table mageworx_giftcards_customer_group
                $this->insertGiftCardsGroups($cardGroupsIds, $datum['card_id']);
            }
        }
    }

    /**
     * @param $saveData
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveGiftCards($saveData)
    {
        $connection = $this->getConnection();
        $tableName  = $this->getMainTable();

        foreach ($saveData as $datum) {
            $cardStoresIds = $this->getStoresIds($datum);
            $cardGroupsIds = $this->getGroupsIds($datum);

            //insert table mageworx_giftcards_card
            $connection->insert($tableName, $datum);
            $giftCardsId = $connection->lastInsertId($tableName);

            if (!empty($cardStoresIds)) {
                // insert table mageworx_giftcards_store
                $this->insertGiftCardsStores($cardStoresIds, $giftCardsId);
            }

            if (!empty($cardGroupsIds)) {
                //insert table mageworx_giftcards_customer_group
                $this->insertGiftCardsGroups($cardGroupsIds, $giftCardsId);
            }
        }
    }

    /**
     * @param array $cardStoresIds
     * @param int $giftCardsId
     */
    protected function insertGiftCardsStores($cardStoresIds, $giftCardsId)
    {
        $data = [];
        foreach ($cardStoresIds as $cardStoreId) {
            $data[] = ['card_id' => (int)$giftCardsId, 'store_id' => (int)$cardStoreId];
        }

        $this->getConnection()->insertMultiple($this->getTable('mageworx_giftcards_store'), $data);
    }

    /**
     * @param array $cardGroupsIds
     * @param int $giftCardsId
     */
    protected function insertGiftCardsGroups($cardGroupsIds, $giftCardsId)
    {
        $data = [];
        foreach ($cardGroupsIds as $groupId) {
            $data[] = ['card_id' => (int)$giftCardsId, 'customer_group_id' => (int)$groupId];
        }

        $this->getConnection()->insertMultiple($this->getTable('mageworx_giftcards_customer_group'), $data);
    }

    /**
     * @param array $data
     * @return array|null
     */
    protected function getStoresIds(& $data)
    {
        if (array_key_exists('store_id', $data)) {
            $storeIds = $data['store_id'];
            unset($data['store_id']);

            return $storeIds;
        }

        return null;
    }

    /**
     * @param array $data
     * @return array|null
     */
    protected function getGroupsIds(& $data)
    {
        if (array_key_exists('group_id', $data)) {
            $groupIds = $data['group_id'];
            unset($data['group_id']);

            return $groupIds;
        }

        return null;
    }
}

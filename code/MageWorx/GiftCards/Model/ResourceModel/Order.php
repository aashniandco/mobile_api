<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\GiftCards\Model\ResourceModel;

class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var array
     */
    protected $date;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageworx_giftcard_order', 'entity_id');
        $this->date = date('Y-m-d H:i:s', time());
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isObjectNew() && !$object->hasCardCode()) {
            $object->setCreatedTime($this->date);
        }

        $object->setUpdatedAt($this->date);

        return parent::_beforeSave($object);
    }

    /**
     * @param array $giftCardIds
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGiftCardOrdersDataByGiftCardId($giftCardIds)
    {
        $connection = $this->getConnection();
        $select     = $this->getConnection()->select()
                           ->from(['giftcards_order' => $this->getMainTable()])
                           ->where('giftcards_order.giftcard_id IN(?)', $giftCardIds);

        return $connection->fetchAll($select);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        return parent::load($object, $value, $field);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        return $select;
    }
}

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
namespace Bss\GuestToCustomer\Model\ResourceModel;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Psr\Log\LoggerInterface;

class Guest extends AbstractDb
{

    /**
     * ResourceConnection
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resources;

    /**
     * AdapterInterface
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connections;

    /**
     * Log
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Message Manager
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Guest constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param ManagerInterface $messageManager
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        ManagerInterface $messageManager,
        $connectionName = null
    ) {
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->resources = $context->getResources();
        $this->connections = $this->resources->getConnection();
        parent::__construct($context, $connectionName);
    }

    /**
     * Construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('bss_guest_to_customer', 'guest_id');
    }

    /**
     * Exist Email Guest
     *
     * @param string $emailGuest
     * @return boolean
     */
    public function existEmailGuest($emailGuest)
    {
        $check = false;
        $sql = $this->connections->select()->from(
            [$this->getTable("bss_guest_to_customer")],
            [
                'email'
            ]
        )->where(
            "email = ?",
            $emailGuest
        );
        $guest = $this->connections->fetchRow($sql);

        if ($guest) {
            $check = true;
        }
        return $check;
    }

    /**
     * Update guest
     *
     * @param array $bind
     * @param array $where
     * @return void
     */
    public function updateGuest($bind = [], $where = [])
    {
        $table = $this->getTable('bss_guest_to_customer');
        if (is_array($bind) && is_array($where)) {
            try {
                $this->connections->update($table, $bind, $where);
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                $this->logger->error($exception->getMessage());
            }
        }
    }

    /**
     * Delete Guest
     *
     * @param array $where
     * @return void
     */
    public function deleteGuest($where = [])
    {
        $table = $this->getTable('bss_guest_to_customer');
        if (is_array($where)) {
            try {
                $this->connections->delete($table, $where);
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                $this->logger->error($exception->getMessage());
            }
        }
    }

    /**
     * Insert Address
     *
     * @param array $bind
     * @return int|null
     */
    public function insertAddress($bind = [])
    {
        $table = $this->getTable('customer_address_entity');

        if (isset($bind['telephone']) && !$bind['telephone']) {
            $bind['telephone'] = '';
        }

        if (isset($bind['lastname']) && !$bind['lastname']) {
            $bind['lastname'] = '';
        }

        try {
            $this->connections->insert($table, $bind);
            $idAddress = $this->getConnection()->lastInsertId($this->getTable('customer_address_entity'));
            return $idAddress;
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return null;
    }

    /**
     * Update Customer Addess Default
     *
     * @param int $idAddress
     * @param int $idCustomer
     * @param int $type
     * @return void
     */
    public function updateCustomerAddessDefault($idAddress, $idCustomer, $type)
    {
        $table = $this->getTable('customer_entity');
        if ($idAddress) {
            if ($type) {
                $this->connections->update(
                    $table,
                    [
                        'default_billing' => $idAddress
                    ],
                    [
                        'entity_id = ?' => $idCustomer
                    ]
                );
            } else {
                $this->connections->update(
                    $table,
                    [
                        'default_shipping' => $idAddress
                    ],
                    [
                        'entity_id = ?' => $idCustomer
                    ]
                );
            }
        }
    }

    /**
     * Update Customer Addess Default
     *
     * @param int $idAddress
     * @param int $idCustomer
     * @return void
     */
    public function updateCustomerBothAddessDefault($idAddress, $idCustomer)
    {

        $table = $this->getTable('customer_entity');
        if ($idAddress) {
            $this->connections->update(
                $table,
                [
                    'default_billing' => $idAddress,
                    'default_shipping' => $idAddress
                ],
                [
                    'entity_id = ?' => $idCustomer
                ]
            );

        }
    }

    /**
     * @param $data
     */
    public function importGuest($data)
    {

        $table = $this->getTable('bss_guest_to_customer');
        if (!empty($data)) {
            $this->connections->insertMultiple(
                $table,
                $data
            );
        }
    }

    /**
     * @param $emails
     * @param $from
     * @param $to
     * @return array
     */
    public function getOrdersListData($emails, $from, $to)
    {
        if (!$emails) {
            $emails = [0];
        }
        $main = $this->getTable('sales_order');
        $second = $this->getTable('sales_order_address');
        $third = $this->getTable('store');
        $query = $this->getConnection()->select()
            ->from(['main' => $main], ['entity_id', 'customer_email', 'store_id'])
            ->join(['second' => $second], 'main.entity_id = second.parent_id', [
                'customer_address_id',
                'quote_address_id',
                'region_id',
                'region',
                'postcode',
                'lastname',
                'street',
                'city',
                'email',
                'telephone',
                'firstname',
                'address_type',
                'prefix',
                'middlename',
                'suffix',
                'company',
                'vat_id',
                'country_id'
            ])
            ->join(['third' => $third], 'main.store_id = third.store_id', ['website_id'])
            ->where(
                'main.customer_email NOT IN(?)',
                $emails
            )
            ->where(
                'main.created_at >= (?)',
                $from
            )
            ->where(
                'main.created_at <= (?)',
                $to
            );
        return $this->getConnection()->fetchAll($query);
    }
}

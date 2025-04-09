<?php
namespace Fermion\NativeApp\Model\ResourceModel\NativeTokens;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {
    
    protected function _construct() {
        $this->_init(
            'Fermion\NativeApp\Model\NativeTokens',
            'Fermion\NativeApp\Model\ResourceModel\NativeTokens'
        );
    } 

    /**
     * Filter collection by token.
     *
     * @param int $customerId
     * @return $this
     */
    public function addTokenFilter($token)
    {
        $this->addFieldToFilter('token', $token);

        $dateTime = new \DateTime();
        $dateTime->modify('-5 minutes');
        $formattedDate = $dateTime->format('Y-m-d H:i:s');
        //$this->addFieldToFilter('created_at', ['gteq' => $formattedDate]);

        return $this;
    }
}
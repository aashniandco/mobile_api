<?php 
namespace Fermion\LoyaltyPoint\Model;
 
 
class removeLoyaltyPointsManagement {

	protected $store_manager;    
    protected $connection;
    private $objectManager;
    private $LoyaltypointHelper;
   
   
    public function __construct(        
        \Magento\Store\Model\StoreManagerInterface $storeManager,        
        \Magento\Framework\App\ResourceConnection $resourceConn,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
         \Fermion\LoyaltyPoint\Helper\LoyaltypointHelper $LoyaltypointHelper
        
    ) {        
        $this->store_manager = $storeManager;
        $this->connection = $resourceConn->getConnection();
        $this->objectManager = $objectmanager;
        $this->LoyaltypointHelper = $LoyaltypointHelper;
    }

	/**
	 * {@inheritdoc}
	 */
	public function removeLoyalty($customerdata)
	{
		$removeLoyaltyPointsData =$this->LoyaltypointHelper->removeLoyaltyDataByCustomer($customerdata);

        $resp = [];
        if ($removeLoyaltyPointsData) {
            $resp = ['status' => "true"];
        } else {
            $resp = ['status' => "false"];
        }

        $resp = json_encode($resp);
        return $resp;
    
	}
}
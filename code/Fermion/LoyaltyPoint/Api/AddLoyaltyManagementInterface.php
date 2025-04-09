<?php 
namespace Fermion\LoyaltyPoint\Api;
 
 
interface AddLoyaltyManagementInterface {


	/**
	 * Loyalty api
	 * @param anyType $customerdata
	 * @return anyType
	 */
	
	public function addLoyalty($customerdata);
}
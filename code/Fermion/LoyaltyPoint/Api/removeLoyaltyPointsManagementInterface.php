<?php 
namespace Fermion\LoyaltyPoint\Api;
 
 
interface removeLoyaltyPointsManagementInterface {


	/**
	 * GET for Post api
	 * @param anyType $customerdata
	 * @return anyType
	 */
	
	public function removeLoyalty($customerdata);
}
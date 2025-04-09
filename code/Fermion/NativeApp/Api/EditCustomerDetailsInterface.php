<?php

namespace Fermion\NativeApp\Api;

interface EditCustomerDetailsInterface{
    /**
     * Edit customer details through my account page on frontend
     *
     * @api
     * 
     * @param anyType $customerDetails
     * @return anyType
     */
	public function editCustomerDetails($customerDetails);
}
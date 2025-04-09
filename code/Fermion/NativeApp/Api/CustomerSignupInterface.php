<?php

namespace Fermion\NativeApp\Api;

interface CustomerSignupInterface{
    /**
     * Validate token on Forgot Password link.
     *
     * @api
     * 
     * @param anyType $customerDetails
     * @return anyType
     */
	public function registerCustomer($customerDetails);
}
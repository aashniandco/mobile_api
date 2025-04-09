<?php

namespace Fermion\NativeApp\Api;

interface ResetPasswordInterface{
    /**
     * Reset Customer password.
     *
     * @api
     * 
     * @param anyType $customerDetails
     * @return anyType
     */
	public function resetCustomerPassword($customerDetails);
}
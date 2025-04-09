<?php

namespace Fermion\NativeApp\Api;

interface FetchUserDetailsInterface{
    /**
     * Fetch User Details.
     *
     * @api
     * 
     * @param anyType $customerDetails
     * @return anyType
     */
	public function fetchDetails($customerDetails);
}
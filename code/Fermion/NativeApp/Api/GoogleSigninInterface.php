<?php

namespace Fermion\NativeApp\Api;

interface GoogleSigninInterface{
    /**
     * Google Sign in.
     *
     * @api
     * 
     * @param anyType $customerDetails
     * @return anyType
     */
	public function signInWithGoogle($customerDetails);
}
<?php

namespace Fermion\NativeApp\Api;

interface AppleSigninInterface{
    /**
     * Apple Sign in.
     *
     * @api
     * 
     * @param anyType $customerDetails
     * @return anyType
     */
	public function signInWithApple($customerDetails);
}
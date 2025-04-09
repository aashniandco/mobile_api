<?php

namespace Fermion\NativeApp\Api;

interface FacebookSigninInterface{
    /**
     * Facebook Sign in.
     *
     * @api
     * 
     * @param anyType $customerDetails
     * @return anyType
     */
	public function signInWithFacebook($customerDetails);
}
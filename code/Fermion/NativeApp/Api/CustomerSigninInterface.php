<?php

namespace Fermion\NativeApp\Api;

interface CustomerSigninInterface{
    /**
     * Validate token on Forgot Password link.
     *
     * @api
     * 
     * @param anyType $login
     * @return anyType
     */
	public function signinCustomer($login);
}
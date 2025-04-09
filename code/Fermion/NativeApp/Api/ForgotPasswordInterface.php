<?php

namespace Fermion\NativeApp\Api;

interface ForgotPasswordInterface{
    /**
     * Trigger link to Forgot password.
     *
     * @api
     * 
     * @param anyType $customerEmail
     * @return anyType
     */
	public function getForgotPasswordLink($customerEmail);
}
<?php

namespace Fermion\NativeApp\Api;

interface ForgotPassTokenValidationInterface{
    /**
     * Validate token on Forgot Password link.
     *
     * @api
     * 
     * @param anyType $token
     * @return anyType
     */
	public function validateTokenForgotPassword($token);
}
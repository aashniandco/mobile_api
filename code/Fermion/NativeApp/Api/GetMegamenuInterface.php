<?php

namespace Fermion\NativeApp\Api;

interface GetMegamenuInterface{
    /**
     * Get Megamenu Items.
     *
     * @api
     * 
     * @param anyType $ip
     * @return anyType
     */
	public function getMegamenu($ip);
}
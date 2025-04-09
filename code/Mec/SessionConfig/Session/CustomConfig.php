<?php

namespace Mec\SessionConfig\Session;

use Magento\Framework\Session\Config as DefaultConfig;

class CustomConfig extends DefaultConfig
{
    public function setCookiePath($path, $default = null)
    {   
        parent::setCookiePath($path, $default);

        $path = $this->getCookiePath();

        //check and update path of cookie
        if (!preg_match('/SameSite/', $path)) {
            $path .= '; SameSite=None';
            $this->setOption('session.cookie_path', $path);
        }

        return $this;
    }
}

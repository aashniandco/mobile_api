<?php
return [
    'backend' => [
        'frontName' => 'admin_backend'
    ],
    'crypt' => [
        'key' => 'f556b4b91dcdc219db9653241249cba1'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => '10.0.1.14',
                'dbname' => 'aashni_live',
                'username' => 'aashniuser',
                'password' => 'Aashn1@oc1#123',
                'active' => '1',
                'driver_options' => [

                ]
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'production',
    'session' => [
        'save' => 'db',
        'gc_probability' => 1,
        'gc_divisor' => 1000,
        'gc_maxlifetime' => 1440,
        'disable_locking' => '1'
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => '70a_'
            ],
            'page_cache' => [
                'id_prefix' => '70a_'
            ]
        ]
    ],
    'lock' => [
        'provider' => 'db',
        'config' => [
            'prefix' => null
        ]
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'google_product' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1,
        'vertex' => 1,
        'checkout' => 1,
        'amasty_shopby' => 1
    ],
    'downloadable_domains' => [
        '18.157.190.137'
    ],
    'install' => [
        'date' => 'Tue, 30 Jun 2020 16:47:44 +0000'
    ]
];

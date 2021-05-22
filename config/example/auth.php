<?php
// This is a PHP file, such that the web server does not accidentally dump the authorization tokens

return [
    // Mapping TOKEN => {stages}
    'tokens' => [
        'YOUR_TOKEN' => [
            'staging',
        ],
        'YOUR_PROD_TOKEN' => [
            'production'
        ],
    ],

    // in these stages, we allow sending the output to the client
    'allowOutput' => [
        'staging',
    ],
];

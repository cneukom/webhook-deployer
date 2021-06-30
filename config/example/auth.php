<?php
// This is a PHP file, such that the web server does not accidentally dump the authorization configuration.
// Note, however, that you might not want to put your actual authorization tokens under version control.
// Use env() in combination with a .env or an env.php file.

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

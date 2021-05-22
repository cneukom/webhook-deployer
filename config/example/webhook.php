<?php
// This is a PHP file, such that the web server does not accidentally dump the authorization tokens

return (new \Webhook\Webhook('echo "hello to stage "{stage}" from project "{project}'))
    ->addAuthorization('YOUR_TOKEN', 'staging', true);

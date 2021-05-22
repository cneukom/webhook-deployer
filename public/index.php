<?php

chdir(__DIR__ . '/..');

spl_autoload_register(function ($className) {
    $appDirectory = 'app/';

    $file = $appDirectory . str_replace('\\', '/', $className) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

if (getenv('APP_DEBUG') || getenv('SHOW_ERROR_MESSAGES')) {
    \Http\Request::showErrorMessages();
}

// We only provide one endpoint, so there's no need for a router. Just try to validate a webhook request.
$request = new \Http\WebhookRequest($_SERVER['REQUEST_METHOD'], $_SERVER['SCRIPT_NAME'], $_GET);

$request->validate();

$request->handle();

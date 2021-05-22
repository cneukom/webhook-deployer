<?php
$project = param('project');
$stage = param('stage');
$token = authToken();
$configDir = __DIR__ . '/../config/';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    abort('Method not allowed');
}

if (!is_dir($configDir . $project)) {
    abort('Unknown project ' . $project);
}
chdir($configDir . $project);

if (!is_file('auth.php')) {
    abort('No auth.php found for project');
}
$auth = require 'auth.php';

if (!isset($auth['tokens'][$token])) {
    abort('Invalid token');
}

if (!in_array($stage, $auth['tokens'][$token])) {
    abort('Stage not allowed for token');
}

list($status, $output) = wrapExec(__DIR__ . '/../vendor/bin/dep deploy ' . $stage);
if ($status !== 0) {
    header('HTTP/1.1 500 Internal Server Error');
}

if (isset($auth['allowOutput']) && in_array($stage, $auth['allowOutput'])) {
    echo $output;
} else if ($status === 0) {
    header('HTTP/1.1 204 No Content');
}


function authToken(): string
{
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return '';
    }

    $tokenPrefix = 'Bearer ';
    if (substr($_SERVER['HTTP_AUTHORIZATION'], 0, strlen($tokenPrefix)) !== $tokenPrefix) {
        abort('Invalid token');
    }

    return substr($_SERVER['HTTP_AUTHORIZATION'], strlen($tokenPrefix));
}

function abort($message)
{
    header('HTTP/1.1 400 Bad Request');
    echo $message;
    exit();
}

function param($key): string
{
    if (!isset($_GET[$key])) {
        abort('Undefined parameter: ' . $key);
    }

    if (!preg_match('/^[a-z0-9]+$/i', $_GET[$key])) {
        abort('Invalid parameter value: ' . $key);
    }

    return $_GET[$key];
}

function wrapExec($command): array
{
    exec($command, $output, $status);
    $output = join(PHP_EOL, $output);
    error_log('Exec (' . $status . '): ' . $command . PHP_EOL . $output);
    return [$status, $output];
}

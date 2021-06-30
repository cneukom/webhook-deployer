<?php
$project = param('project');
$stage = param('stage');
$token = authToken();
$configDir = __DIR__ . '/../config/';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    abort('Method not allowed', 405);
}

if (!is_dir($configDir . $project)) {
    abort('Unknown project ' . $project, 404);
}
chdir($configDir . $project);

if (!is_file('auth.php')) {
    abort('No auth.php found for project', 500);
}
$auth = require 'auth.php';

if (!isset($auth['tokens'][$token])) {
    abort('Invalid token', 403);
}

if (!in_array($stage, $auth['tokens'][$token])) {
    abort('Stage not allowed for token', 403);
}

list($status, $output) = wrapExec(__DIR__ . '/../vendor/bin/dep deploy ' . $stage);
if (isset($auth['allowOutput']) && in_array($stage, $auth['allowOutput'])) {
    abort($output, $status === 0 ? 200 : 500);
} else {
    abort(null, $status === 0 ? 204 : 500);
}


function authToken(): string
{
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return '';
    }

    $tokenPrefix = 'Bearer ';
    if (substr($_SERVER['HTTP_AUTHORIZATION'], 0, strlen($tokenPrefix)) !== $tokenPrefix) {
        abort('Invalid token', 401);
    }

    return substr($_SERVER['HTTP_AUTHORIZATION'], strlen($tokenPrefix));
}

function abort(?string $responseContent, $code = 400)
{
    $statusCodes = [
        200 => 'OK',
        204 => 'No Content',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Internal Server Error',
    ];

    header("HTTP/1.1 $code ${statusCodes[$code]}");
    if ($responseContent !== null) {
        echo $responseContent;
    }
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

/**
 * Get an environment variable value.
 *
 * This function checks if an environment variable $key exists and returns its value. If not, it checks if the key
 * exists in the `env.php` or `.env` file and returns that value. If $key does not exist as environment variable nor in
 * the `env.php` nor `.env` file, an error is returned to the client.
 *
 * If you use this function for hiding authorization tokens from version control in combination with the `.env` file,
 * make sure that you web server does not dump the `.env` file.
 *
 * @param $key
 * @return mixed
 */
function env($key)
{
    static $dotEnv = null;
    static $envPhp = null;
    if ($dotEnv === null) {
        if (file_exists(__DIR__ . '/../.env')) {
            $dotEnv = parse_ini_file(__DIR__ . '/../.env');
        }
    }
    if ($envPhp === null) {
        if (file_exists(__DIR__ . '/../env.php')) {
            $envPhp = require __DIR__ . '/../env.php';
        }
    }

    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    } else if (isset($envPhp[$key])) {
        return $envPhp[$key];
    } else if (isset($dotEnv[$key])) {
        return $dotEnv[$key];
    }

    abort('ENV ' . $key . ' not defined', 500);
}

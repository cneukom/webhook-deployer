<?php

namespace Http;

/**
 * Class Request
 *
 * Provides basic validation and respond logic.
 *
 * @package Http
 */
abstract class Request
{
    /** Define the path this request is valid for. */
    const PATH = null;

    /** Define the expected request method. */
    const METHOD = null;

    /** Define required parameters and a regex to validate their values. */
    const PARAMETERS = [];

    /** Use this to validate inputs you want to pass to a shell. */
    const ALLOWED_PARAMETER_VALUES = '#^[a-z0-9]+$#i';

    private static bool $showErrorMessages = false;

    private string $method;
    private string $path;
    protected array $parameters;

    public static function showErrorMessages(): void
    {
        self::$showErrorMessages = true;
    }

    public function __construct(string $method, string $path, array $parameters)
    {
        $this->method = $method;
        $this->path = $path;
        $this->parameters = $parameters;
    }

    /**
     * Validate request path, method and parameters.
     */
    public function validate(): void
    {
        if ($this->method !== static::METHOD) {
            $this->abort(400, 'Method ' . $this->method . ' not allowed');
        }

        if ($this->path !== static::PATH) {
            $this->abort(404, 'Wrong path; got ' . $this->path);
        }

        $expectParameters = array_keys(static::PARAMETERS);
        $hasParameters = array_keys($this->parameters);
        if ($hasParameters !== $expectParameters) {
            $this->abort(400, 'Expecting parameters [' . join(', ', $expectParameters) . '], got [' . join(', ', $hasParameters));
        }

        foreach (self::PARAMETERS as $parameter => $pattern) {
            if (!preg_match($pattern, $this->parameters[$parameter])) {
                $this->abort(400, $parameter . ' contains illegal value');
            }
        }
    }

    public function abort(int $code, ?string $message = null): void
    {
        if (!self::$showErrorMessages) {
            $message = null;
        }

        $this->respond($code, $message);
        exit();
    }

    public function respond(int $code, ?string $content = ''): void
    {
        $knownErrorCodes = [
            200 => 'Ok',
            204 => 'No Content',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
        ];

        if (!isset($knownErrorCodes[$code])) {
            $this->abort(500, 'Invalid error code ' . $code);
        }

        if ($content === null) {
            $content = $knownErrorCodes[$code];
        }

        header('HTTP/1.1 ' . $code . ' ' . $knownErrorCodes[$code]);
        echo $content;
    }

    public abstract function handle();
}

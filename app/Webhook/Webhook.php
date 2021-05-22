<?php

namespace Webhook;

/**
 * Class Webhook
 *
 * A Webhook is an action that can be triggered via web request, given the appropriate authorization.
 *
 * This class executes simple shell commands. Extend this class to implement more complex actions.
 *
 * @package Webhook
 */
class Webhook
{
    private string $action;

    /** @var Authorization[] */
    private array $authorizations = [];

    /**
     * Webhook constructor.
     * @param string $action
     */
    public function __construct(string $action)
    {
        $this->action = $action;
    }

    /**
     * @param string|string[] $tokens
     * @param string|string[] $stages
     * @param bool $canReadOutput
     * @return Webhook
     */
    public function addAuthorization($tokens, $stages, bool $canReadOutput = false): self
    {
        $this->authorizations[] = new Authorization($tokens, $stages, $canReadOutput);

        return $this;
    }

    /**
     * Finds an Authorization that matches $token and $stage. Returns null, if no such Authorization exists.
     *
     * @param string $token
     * @param string $stage
     * @return Authorization|null
     */
    public function findAuthorizationFor(string $token, string $stage): ?Authorization
    {
        foreach ($this->authorizations as $authorization) {
            if ($authorization->authorizes($token, $stage)) {
                return $authorization;
            }
        }
        return null;
    }

    /**
     * Executes the webhook action using a shell.
     *
     * Make sure that you only pass safe values in $parameters!
     *
     * @param string[] $parameters An array of named parameters.
     * @return string The output of the command.
     * @throws \Exception If the command fails.
     */
    public function execute(array $parameters): string
    {
        $command = str_replace(
            array_map(fn($key) => '{' . $key . '}', array_keys($parameters)),
            array_map(fn($value) => escapeshellarg($value), array_values($parameters)),
            $this->action,
        );

        error_log('Execute: ' . $command);
        exec($command, $output, $status);
        $output = join(PHP_EOL, $output);
        error_log($output);

        if ($status !== 0) {
            throw new \Exception('Command returned ' . $status . ' and printed:' . PHP_EOL . $output);
        }

        return $output;
    }
}

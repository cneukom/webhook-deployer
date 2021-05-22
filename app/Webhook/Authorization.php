<?php

namespace Webhook;

/**
 * Class Authorization
 *
 * An Authorization links authorization tokens with allowable stages and additional permissions.
 *
 * @package Webhook
 */
class Authorization
{
    /** @var string[] */
    private array $stages;

    /** @var string[] */
    private array $tokens;

    private bool $canReadOutput;

    /**
     * @param string|string[] $tokens The tokens this Authorization is valid for.
     * @param string|string[] $stages The stages this Authorization is valid for.
     * @param bool $canReadOutput Whether to send the output of the command to the webhook client.
     */
    public function __construct($tokens, $stages, bool $canReadOutput = false)
    {
        $this->canReadOutput = $canReadOutput;

        if (!is_array($tokens)) {
            $tokens = [$tokens];
        }

        if (!is_array($stages)) {
            $stages = [$stages];
        }

        foreach ($tokens as $token) {
            $this->addToken($token);
        }

        foreach ($stages as $stage) {
            $this->addStage($stage);
        }
    }

    public function addToken(string $token)
    {
        $this->tokens[] = $token;
    }

    public function addStage(string $stage)
    {
        $this->stages[] = $stage;
    }

    /**
     * Checks whether this Authorization is valid for the given token and the given stage.
     *
     * @param string $token
     * @param string $stage
     * @return bool
     */
    public function authorizes(string $token, string $stage): bool
    {
        return in_array($token, $this->tokens)
            && in_array($stage, $this->stages);
    }

    public function canReadOutput(): bool
    {
        return $this->canReadOutput;
    }
}

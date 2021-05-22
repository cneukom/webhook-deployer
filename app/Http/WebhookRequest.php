<?php

namespace Http;

use Webhook\Authorization;
use Webhook\Webhook;

class WebhookRequest extends Request
{
    const PATH = '/api/webhook';

    const METHOD = 'POST';

    const PARAMETERS = [
        'project' => self::ALLOWED_PARAMETER_VALUES,
        'stage' => self::ALLOWED_PARAMETER_VALUES,
    ];

    const CONFIG_DIRECTORY = 'config';

    /**
     * Find, authorize and execute the Webhook. If the Authorization allows it, print the output.
     */
    public function handle()
    {
        $webhook = $this->findWebhook();

        $authorization = $this->authorize($webhook);

        try {
            $output = $webhook->execute($this->parameters);
        } catch(\Exception $e) {
            $this->abort(500, $e->getMessage());
        }

        if ($authorization->canReadOutput()) {
            $this->respond(200, $output);
        } else {
            $this->respond(204);
        }
    }

    /**
     * Find the Webhook configuration for the given project.
     *
     * @return Webhook
     */
    protected function findWebhook(): Webhook
    {
        $configurationFile = static::CONFIG_DIRECTORY . '/' . $this->parameters['project'] . '/webhook.php';
        if (!file_exists($configurationFile)) {
            $this->abort(404, 'Unknown project');
        }

        return require $configurationFile;
    }

    /**
     * Try to authorize the request, using a Bearer token. Aborts the request, if authorization fails.
     *
     * @param Webhook $webhook
     * @return Authorization
     */
    protected function authorize(Webhook $webhook): Authorization
    {
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $this->abort(401, 'Authorization header missing');
        }

        if (substr($_SERVER['HTTP_AUTHORIZATION'], 0, 7) !== 'Bearer ') {
            $this->abort(401, 'Authorization header must contain a Bearer token');
        }
        $token = substr($_SERVER['HTTP_AUTHORIZATION'], 7);

        $authorization = $webhook->findAuthorizationFor($token, $this->parameters['stage']);
        if (!$authorization) {
            $this->abort(403, 'Failed to find authorization for stage ' . $this->parameters['stage'] . ' with this token');
        }

        return $authorization;
    }
}

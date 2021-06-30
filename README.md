# Simple Deploy Webhook

Webhooks for automatic deployment of non-Dockerized services with [Deployer](https://deployer.org).
If you don't want to give your CI pipelines SSH access to your production servers, use a simple webhook to trigger the local deployment on your trusted server.

## Getting Started

Fork or clone this repository.
The `config` directory can contain multiple deployment configurations (or projects), each in a separate directory.
For each deployment configuration, the `auth.php` defines the auth tokens and for which stages, they are allowed: 

```injectablephp
return [
    // Mapping TOKEN => {stages}
    'tokens' => [
        'YOUR_TOKEN' => [
            'staging',
        ],
    ],
];
```

If you want to send the output from the deployment command to the client for some stages, specify these stages in the `allowOutput` array.
You probably don't want to do this for your production stage.

Note, that you might not want to put your actual tokens under version control.
You can specify your tokens in a `.env` or an `env.php` file and use the `env()` helper to access them from your `auth.php`.

Then, let your CI trigger the deployment webhook, e.g. with `wget`:

```shell
wget -qO- --content-on-error --method=POST \
    https://deploy.your-project.org/webhook.php?project=example\&stage=staging \
    --header="Authorization: Bearer YOUR_TOKEN"
```

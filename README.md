# Simple Webhook Management for PHP

This tool facilitates the creation of simple webhooks, e.g. for automatic deployment of non-Dockerized services with [Deployer](https://deployer.org).
If you don't want to give your CI pipelines SSH access to your production servers, use a simple webhook to trigger the local deployment on your trusted server.

Create a `webhook.php` to run the deployment process:

```injectablephp
return (new \Webhook\Webhook('dep deploy {stage}'))
    ->addAuthorization('YOUR_TOKEN', ['staging', 'production']);
```

And let your CI trigger it, e.g. with `curl`:

```shell
curl -d "" https://deploy.your-project.org/api/webhook?project=example\&stage=staging -H "Authorization: Bearer YOUR_TOKEN"
```

## Getting Started

There are no dependencies (so you can read the entire source code within a few minutes).
Just fork this repository and add your webhook configuration in `config/yourProject/webhook.php`.

You might want to define multiple access tokens with different permissions.
Here's a complete example for a simple webhook:

```injectablephp
return (new \Webhook\Webhook('echo Hello to stage {stage} of project {project}')) // executes the command on a shell

    // The PRODUCTION_TOKEN can trigger the command for the production stage.
    // The endpoint will not return the output of the command.
    ->addAuthorization('PRODUCTION_TOKEN', 'production') 
    
    // The STAGING_TOKEN can trigger the command for both, the testing and the staging stage.
    // Also, the endpoint will return the output of the command.
    ->addAuthorization('STAGING_TOKEN', ['testing', 'staging'], true)
    
    // You can define the same token multiple times, the first Authorization matching both, the token and the stage, will be used.
    ->addAuthorization(['PRODUCTION_TOKEN', 'STAGING_TOKEN'], 'dummy', true);
```

## Custom Webhooks

You can extend from `Webhook` and write your own webhooks by overriding the `execute()` method:

```injectablephp
class CustomWebhook extends Webhook {
    public function __construct() {
        // Don't need the $action argument
    }

    public function execute(array $parameters): string
    {
        return 'Hello from CustomWebhook';
    }
}
```

Then, simply return a `CustomWebhook` from your `webhook.php`.

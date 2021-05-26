<?php

namespace Deployer;

require 'recipe/common.php';

set('repository', 'git@github.com:cneukom/webhook-deployer');

localhost('staging')
    ->setDeployPath('/tmp/deploy/staging');

localhost('production')
    ->setDeployPath('/tmp/deploy/production');

/**
 * Main deploy task.
 */
desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'deploy:publish',
]);

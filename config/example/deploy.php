<?php

namespace Deployer;

require 'recipe/laravel.php';

set('repository', 'git@github.com:laravel/laravel');

localhost('staging')
    ->setDeployPath('/tmp/deploy/staging');

localhost('production')
    ->setDeployPath('/tmp/deploy/production');

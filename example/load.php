<?php

use Mds\Mmb\Kernel\Kernel;
use Mds\Mmb\Provider\Provider;

include __DIR__ . '/vendor/autoload.php';


// Load providers
config()->applyFile(__DIR__ . '/Configs/providers.php', 'providers');
Provider::loadProviders(config('providers'));


// Bootstrap
Kernel::bootstrap();

<?php

use Mmb\Kernel\Kernel;
use Providers\UpdProvider;

include __DIR__ . '/load.php';






Kernel::handleUpdate(
    app(UpdProvider::class)
);

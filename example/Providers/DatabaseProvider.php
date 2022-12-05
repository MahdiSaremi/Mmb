<?php

namespace Providers; #auto
use Mmb\Provider\Provider;

class DatabaseProvider extends Provider
{

    public function register()
    {
        $this->loadConfigFrom(__DIR__ . '/../Configs/database.php', 'database');

        $driver = config('database.driver');
        $driver::setAsDefault();

        $this->onInstance('db', function() {
            return \Mmb\Db\Driver::defaultStatic();
        });
    }

    public function boot()
    {
        
    }
    
}

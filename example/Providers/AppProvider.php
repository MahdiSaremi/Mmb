<?php

namespace Providers; #auto
use Mds\Mmb\Mmb;
use Mds\Mmb\Provider\Provider;

class AppProvider extends Provider
{

    public function register()
    {
        $this->loadConfigFrom(__DIR__ . '/../Configs/app.php', 'app');
        $this->loadConfigFrom(__DIR__ . '/../Configs/bot.php', 'bot');

        Mmb::$this = new Mmb(config('bot.token'));
        $this->onInstance('mmb', function() {
            return Mmb::$this;
        });

        $this->setStoragePath(config('app.storage'));
    }

    public function boot()
    {
        
    }
    
}

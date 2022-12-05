<?php

namespace Mds\Mmb\Provider; #auto

use Mds\Mmb\Kernel\Instance;
use Mds\Mmb\Kernel\Kernel;
use Mds\Mmb\Listeners\HasListeners;
use Mds\Mmb\Storage\Storage;
use Mds\Mmb\Tools\SaveInstances;

class Provider
{
    use HasListeners, SaveInstances;

    // Static
    
    public static function registerAll()
    {
        self::invokeAllObjects('register');
    }

    public static function bootAll()
    {
        self::invokeAllObjects('boot');
    }

    public static function invokeAllListens($method, array $args)
    {
        foreach(self::getAllObjects() as $object)
        {
            $object->invokeListen($method, $args);
        }
    }

    public static function loadProviders(array $providers)
    {
        foreach($providers as $provider)
        {
            Instance::get($provider);
        }
    }

    public static function setStoragePath($path)
    {
        Storage::$storagePath = $path;
    }

    public static $updateCanceled = false;


    // Class object

    // public abstract function register();
    // public abstract function boot();

    public function loadConfigFrom($file, $name)
    {
        config()->applyFile($file, $name);
    }

    public function onInstance($name, $callback)
    {
        Instance::setOn($name, $callback);
    }

    public function booted($callback)
    {
        $this->listen(__FUNCTION__, $callback);
    }

    public function update($callback)
    {
        $this->listen(__FUNCTION__, $callback);
    }

    public function updateHandled($callback)
    {
        $this->listen(__FUNCTION__, $callback);
    }

    public function cancelUpdate()
    {
        self::$updateCanceled = true;
    }
    
}

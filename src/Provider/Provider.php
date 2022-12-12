<?php

namespace Mmb\Provider; #auto

use Mmb\Assets\Assets;
use Mmb\Kernel\Instance;
use Mmb\Kernel\Kernel;
use Mmb\Listeners\HasListeners;
use Mmb\Storage\Storage;
use Mmb\Tools\SaveInstances;
use Mmb\Guard\Guard;
use Mmb\Lang\Lang;

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

    /**
     * بارگزاری کردن پرووایدر ها
     * 
     * @param array $providers
     * @return void
     */
    public static function loadProviders(array $providers)
    {
        foreach($providers as $provider)
        {
            Instance::get($provider);
        }
    }

    public static $updateCanceled = false;


    // Class object

    // public abstract function register();
    // public abstract function boot();

    /**
     * بارگزاری کردن کانفیگ از فایل
     * 
     * @param string $file
     * @param string $name
     * @return void
     */
    public function loadConfigFrom($file, $name)
    {
        config()->applyFile($file, $name);
    }

    /**
     * لود کردن پوشه زبان ها
     * 
     * @param string $path
     * @return void
     */
    public function loadLangFrom($path)
    {
        Lang::loadLangFrom($path);
    }

    /**
     * تنظیم مسیر استوریج
     * 
     * @param string $path
     * @return void
     */
    public function setStoragePath($path)
    {
        Storage::$storagePath = $path;
    }

    /**
     * تنظیم مسیر استز
     * 
     * @param string $path
     * @return void
     */
    public function setAssetsPath($path)
    {
        Assets::setPath($path);
    }

    /**
     * تعریف می کند زمانی که این نام صدا شود، مقدار مورد نظر برگردانده شود
     * 
     * @param string $name
     * @param \Closure $callback
     * @return void
     */
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

    /**
     * تعریف سطح دسترسی جدید
     * 
     * @param string $name
     * @param \Closure $callback
     * @return void
     */
    public function defineGuard($name, \Closure $callback)
    {
        app(Guard::class)->define($name, $callback);
    }

    /**
     * تعریف پلیسی جدید
     * 
     * @param string|object $policy
     * @return void
     */
    public function registerPolicy($policy)
    {
        if (is_object($policy))
            $policy = get_class($policy);

        app(Guard::class)->definePolicy($policy);
    }

    /**
     * تنظیم می کند زمانی که دسترسی غیر مجاز است (در شرایط خاص) چه عملی انجام شود
     * 
     * @param \Closure $callback
     * @return void
     */
    public function notAllowed(\Closure $callback)
    {
        app(Guard::class)->notAllowed($callback);
    }
    
}

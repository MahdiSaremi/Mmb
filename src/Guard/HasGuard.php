<?php
#auto-name
namespace Mmb\Guard;

use Mmb\Controller\StepHandler\Handlable;

trait HasGuard
{

    private $_needTo = [];

    /**
     * تعریف الزامی بودن دسترسی مورد نظر برای تابع های کنترلر
     * 
     * این تابع را تنها در قسمت بوت صدا بزنید
     * 
     * @param string $guardPolicy
     * @param mixed ...$args
     * @return void
     */
    public function needTo($guardPolicy, ...$args)
    {
        $this->_needTo[] = [$guardPolicy, $args];
    }

    public function resetAllowCache()
    {
        $this->_allowed_cache = null;
    }
    
    protected $_allowed_cache = null;
    /**
     * بررسی می کند دسترسی های مورد نیاز که در بوت تعریف شده اند را را داراست
     * 
     * @return bool
     */
    public function allowed()
    {
        // Cache
        if(isset($this->_allowed_cache))
        {
            return $this->_allowed_cache;
        }

        // Class name allowed
        if(!app(Guard::class)->allowClass(static::class))
        {
            return $this->_allowed_cache = false;
        }
        
        // Class object allowed
        foreach($this->_needTo as $need)
        {
            $name = $need[0];
            $args = $need[1];
            if(!$this->allow($name, ...$args))
            {
                return $this->_allowed_cache = false;
            }
        }
        
        return $this->_allowed_cache = true;
    }

    /**
     * بررسی می کند دسترسی های مورد نیاز که در بوت تعریف شده اند را داراست
     * 
     * این تابع نسخه استاتیک است و تنها برای کلاس هایی که از آن پشتیبانی می کنند کار می کند
     *
     * @return boolean
     */
    public static function isAllowed()
    {
        return app(static::class)->allowed();
    }
    
    /**
     * بررسی وجود دسترسی
     * 
     * @param string $name
     * @param mixed ...$args
     * @return bool
     */
    public static function allow($name, ...$args)
    {
        return app(Guard::class)->allow($name, ...$args);
    }

    /**
     * این تابع زمانی که دسترسی غیر مجاز است صدا زده می شود
     * 
     * فقط دسترسی هایی که با تابع needTo تعریف شده اند محسوب می شوند
     * 
     * @return Handlable|null
     */
    public function notAllowed()
    {
        return app(Guard::class)->invokeNotAllowed();
    }

    /**
     * بررسی می کند دسترسی های مورد نیاز را دارد و در صورت عدم وجود دسترسی، خطایی ارسال می کند
     *
     * @throws AccessDaniedException
     * @return void
     */
    public function requiredPermissions()
    {
        if(!$this->allowed())
        {
            throw new AccessDaniedException([ $this, 'notAllowed' ]);
        }
    }

}

<?php

namespace Mmb\Controller\Handler; #auto

use Closure;
use Mmb\Controller\StepHandler\Handlable;
use Mmb\Guard\Guard;
use Mmb\Listeners\Listeners;
use Mmb\Tools\Staticable;

abstract class Handler
{
    use Staticable;

    /**
     * بررسی اجرا
     * @return bool
     */
    public function check()
    {
        return $this->forceRun || !self::$requireStop;
    }

    /**
     * اجرای هندلر
     * @return Handlable|null
     */
    public final function runHandle()
    {
        if ($this->check())
        {
            if ($this->break)
            {
                $this->stop();
            }
            return $this->handle();
        }
    }

    /**
     * مدیریت آپدیت
     * 
     * @return Handlable|null
     */
    public abstract function handle();

    /**
     * اجرای تابع
     *
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    public function invoke($method, ...$args)
    {
        return Listeners::callMethod([ $this, $method ], $args);
    }

    /**
     * درخواست توقف اجرای هندلر ها
     * 
     * @return $this
     */
    protected function stop()
    {
        self::$requireStop = true;
        return $this;
    }

    /**
     * غیرفعال کردن درخواست توقف اجرای هندلر ها
     * 
     * @return $this
     */
    protected function cancelStop()
    {
        self::$requireStop = false;
        return $this;
    }

    
    /**
     * @var bool
     */
    public $break = false;
    
    /**
     * متوقف کردن خودکار بعد از موفق بودن بررسی برای اجرا
     * 
     * اگر فعال شود، در صورت اجرای هندلر، هندلر های دیگر اجرا نمی شوند
     * 
     * @param bool $value
     * @return $this
     */
    public function break($value = true)
    {
        $this->break = $value;
        return $this;
    }

    /**
     * غیرفعال کردن توقف خودکار
     * 
     * اگر فعال شود، در صورت اجرای هندلر، هندلر های دیگر نیز اجرا می شوند. مگر دستی متوقف شوند
     * 
     * @return $this
     */
    public function noBreak()
    {
        $this->break = false;
        return $this;
    }


    /**
     * @var bool
     */
    public $forceRun = false;
    
    /**
     * اجبار اجرای هندلر بدون در نظر گرفتن موانع
     *
     * @return $this
     */
    public function forceRun()
    {
        $this->forceRun = true;
        return $this;
    }



    // Static functions

    public static $requireStop = false;

    public static function group(Closure|array $group)
    {
        return new HandlerIf(null, $group);
    }

    public static function groupIf(Closure $if, Closure|array $group)
    {
        return new HandlerIf($if, $group);
    }

    public static function groupIfAllowed(string|array $name_args, Closure|array $group)
    {
        if(is_string($name_args))
        {
            $name_args = [ $name_args ];
        }
        return new HandlerIf(fn() => Guard::allowTo(...$name_args), $group);
    }

}

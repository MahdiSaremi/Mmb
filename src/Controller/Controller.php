<?php

namespace Mmb\Controller; #auto

use Mmb\Controller\Handler\Command;
use Mmb\Controller\StepHandler\Handlable;
use Mmb\Controller\StepHandler\NextRun;
use Mmb\Listeners\InvokeEvent;
use Mmb\Listeners\Listeners;
use Mmb\Tools\Staticable;
use Mmb\Update\Message\Data\Poll;
use Mmb\Guard\Guard;

class Controller implements InvokeEvent
{
    use Staticable;

    /**
     * اجرای تابع
     *
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    public static function invoke($method, ...$args)
    {
        return Listeners::callMethod([ static::instance(), $method ], $args);
    }

    /**
     * گرفتن متد
     * 
     * @param string $method
     * @return array
     */
    public static function method($method)
    {
        return [ static::class, $method ];
    }

    public function __invoke($method, ...$args)
    {
        return self::invoke($method, ...$args);
    }

	/**
	 * @param mixed $name
	 * @param array $args
	 * @return true|void
	 */
	public function eventInvoke($name, array $args, &$result)
    {
        if(!$this->allowed())
        {
            $result = $this->notAllowed();
            return true;
        }
	}

    /**
     * بررسی می کند دسترسی های مورد نیاز که در بوت تعریف شده اند را را داراست
     * 
     * @return bool
     */
    public static function allowed()
    {
        $controller = static::instance();
        foreach($controller->_needTo as $need)
        {
            $name = $need[0];
            $args = $need[1];
            if(!$controller->allow($name, ...$args))
            {
                return false;
            }
        }
        return true;
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

    public function __construct()
    {
        $this->boot();
    }

    public function boot()
    {
        
    }

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
    
    public function __get($name)
    {
        if(!method_exists($this, $name))
        {
            return null;
        }

        return $this->$name = $this->invoke($name);
    }

    
    /**
     * ساخت کلید با پاسخی از این کنترلر
     * 
     * @param string $text
     * @param string $method
     * @param mixed ...$args
     * @return array
     */
    public static function key($text, $method, ...$args)
    {
        return [
            'text' => $text,
            'method' => [ static::class, $method ],
            'args' => $args,
        ];
    }

    /**
     * ساخت کلید اشتراک مخاطب با پاسخی از این کنترلر
     * 
     * @param string $text
     * @param string $method
     * @param mixed ...$args
     * @return array
     */
    public static function keyContact($text, $method, ...$args)
    {
        return [
            'text' => $text,
            'contact' => true,
            'method' => [ static::class, $method ],
            'args' => $args,
        ];
    }

    /**
     * ساخت کلید ارسال موقعیت مکانی با پاسخی از این کنترلر
     * 
     * @param string $text
     * @param string $method
     * @param mixed ...$args
     * @return array
     */
    public static function keyLocation($text, $method, ...$args)
    {
        return [
            'text' => $text,
            'location' => true,
            'method' => [ static::class, $method ],
            'args' => $args,
        ];
    }

    /**
     * ساخت کلید اشتراک دلخواه با پاسخی از این کنترلر
     * 
     * @param string $text
     * @param string $method
     * @param mixed ...$args
     * @return array
     */
    public static function keyType($text, $require, $method, ...$args)
    {
        return [
            'text' => $text,
            $require => true,
            'method' => [ static::class, $method ],
            'args' => $args,
        ];
    }

    /**
     * ساخت کلید ارسال نظرسنجی با پاسخی از این کنترلر
     * 
     * @param string $text
     * @param string $method
     * @param mixed ...$args
     * @return array
     */
    public static function keyPoll($text, $method, ...$args)
    {
        return [
            'text' => $text,
            'poll' => [ 'type' => Poll::TYPE_REGULAR ],
            'method' => [ static::class, $method ],
            'args' => $args,
        ];
    }

    /**
     * ساخت کلید ارسال نظرسنجی سوالی با پاسخی از این کنترلر
     * 
     * @param string $text
     * @param string $method
     * @param mixed ...$args
     * @return array
     */
    public static function keyPollQuiz($text, $method, ...$args)
    {
        return [
            'text' => $text,
            'poll' => [ 'type' => Poll::TYPE_QUIZ ],
            'method' => [ static::class, $method ],
            'args' => $args,
        ];
    }

    /**
     * ساخت مدیریت کننده برای کامند جدید
     * 
     * @param string|array $command
     * @param string $method
     * @return Command
     */
    public static function command($command, $method)
    {
        return Command::command($command, static::class, $method);
    }

    /**
     * این مقدار پاسخ را برگردانید تا متد مورد نظر شما در پاسخ کاربر اجرا شود
     * 
     * @param string $method
     * @param mixed $args
     * @return NextRun
     */
    public static function nextRun($method, ...$args)
    {
        return new NextRun([ static::class, $method ], ...$args);
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

}

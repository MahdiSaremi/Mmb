<?php

namespace Mds\Mmb\Controller; #auto

use Mds\Mmb\Controller\Handler\Command;
use Mds\Mmb\Listeners\Listeners;
use Mds\Mmb\Tools\Staticable;
use Mds\Mmb\Update\Message\Data\Poll;

class Controller 
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

    public static function method($method)
    {
        return [ static::instance(), $method ];
    }

    public function __invoke($method, ...$args)
    {
        return self::invoke($method, ...$args);
    }

    public function __construct()
    {
        $this->boot();
    }

    public function boot()
    {
        
    }
    
    public function __get($name)
    {
        if(!method_exists($this, $name))
        {
            return null;
        }

        return $this->$name = $this->invoke($name);
    }
 
    
    
    public static function key($text, $method, ...$args)
    {
        return [
            'text' => $text,
            'method' => [ static::class, $method ],
            'args' => $args,
        ];
    }

    public static function keyContact($text, $method, ...$args)
    {
        return [
            'text' => $text,
            'contact' => true,
            'method' => [ static::class, $method ],
            'args' => $args,
        ];
    }

    public static function keyLocation($text, $method, ...$args)
    {
        return [
            'text' => $text,
            'location' => true,
            'method' => [ static::class, $method ],
            'args' => $args,
        ];
    }

    public static function keyType($text, $require, $method, ...$args)
    {
        return [
            'text' => $text,
            $require => true,
            'method' => [ static::class, $method ],
            'args' => $args,
        ];
    }

    public static function keyPoll($text, $method, ...$args)
    {
        return [
            'text' => $text,
            'poll' => [ 'type' => Poll::TYPE_REGULAR ],
            'method' => [ static::class, $method ],
            'args' => $args,
        ];
    }

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
     * @param string|array $command
     * @param string $method
     * @return Command
     */
    public static function command($command, $method)
    {
        return Command::command($command, static::class, $method);
    }


}

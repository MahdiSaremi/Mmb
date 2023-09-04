<?php
#auto-name
namespace Mmb\Controller\InlineMenu;

use Closure;
use Exception;
use Mmb\Listeners\HasListeners;
use Mmb\Tools\ATool;
use Mmb\Update\Message\Msg;

class InlineMenu
{

    public function __construct()
    {
        
    }

    private $args = [];

    public function addArgs(array $args)
    {
        $this->args = array_replace($this->args, $args);
        return $this;
    }

    public function setArg(string $name, $value)
    {
        $this->args[$name] = $value;
        return $this;
    }

    public function getArgs(array $append = [])
    {
        return $append + $this->args;
    }

    private $key;

    /**
     * کلید ها را تنظیم می کند
     *
     * @param array|Closure $key
     * @return $this
     */
    public function setKey(array|Closure $key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * گرفتن کلید ها
     *
     * @return array|null
     */
    public function getKey()
    {
        if($this->key instanceof Closure)
        {
            $callback = $this->key;
            return ATool::toArray($callback());
        }

        return $this->key;
    }

    use HasListeners;

    /**
     * قبل از نمایش منو صدا زده می شود
     * 
     * می توانید برای تنظیم متن و دکمه ها استفاده کنید
     *
     * @param Closure $callback `fn(InlineMenu $menu)`
     * @return $this
     */
    public function requesting(Closure $callback)
    {
        $this->listen('requesting', $callback);
        return $this;
    }

    /**
     * بعد از نمایش منو صدا زده می شود
     *
     * @param Closure $callback `fn(InlineMenu $menu, Msg $msg)`
     * @return $this
     */
    public function requested(Closure $callback)
    {
        $this->listen('requested', $callback);
        return $this;
    }

    private $message;

    /**
     * پیغام را تنظیم می کند
     *
     * @param array|string|Closure $message
     * @return $this
     */
    public function request(array|string|Closure $message)
    {
        $this->message = $message;
        return $this;
    }
    /**
     * گرفتن پیغام
     *
     * @return array|string|null
     */
    public function getRequest()
    {
        if($this->message instanceof Closure)
        {
            $callback = $this->message;
            return $callback();
        }

        return $this->message;
    }

    
    /**
     * منو را نمایش می دهد
     * 
     * این تابع در صورت وجود کالبک، پیام را ویرایش می کند و در غیر این صورت پیام جدیدی ارسال می کند
     *
     * @param array|string|null|null $message
     * @return Msg|null
     */
    public function show(array|string|null $message = null)
    {
        if(callback())
        {
            return $this->edit($message);
        }
        else
        {
            return $this->send($message);
        }
    }
    
    /**
     * منو را ارسال می کند
     *
     * @param mixed $message
     * @return Msg|null
     */
    public function send(array|string|null $message = null, $method = 'response', ?Msg $on = null)
    {
        $this->invokeListeners('requesting', [ $this ]);

        if(is_null($message))
        {
            $message = $this->getRequest();
            if(is_null($message))
            {
                return;
            }
        }

        if(!is_array($message))
        {
            $message = [ 'text' => $message ];
        }

        if($on)
        {
            $method = $on->$method(...);
        }

        $result = $method($this->getArgs($message + [
            'key' => $this->getKey(),
        ]));

        if($result instanceof Msg)
        {
            $this->invokeListeners('requested', [ $this, $result ]);
        }
        return $result;
    }
    
    /**
     * پیام فعلی را ویرایش می کند و منو را نمایش می دهد
     *
     * @param array|string|null|null $message
     * @return Msg|null
     */
    public function edit(array|string|null $message = null, ?Msg $on = null)
    {
        $this->invokeListeners('requesting', [ $this ]);

        if(is_null($on))
        {
            $on = msg();
        }

        if(!msg())
        {
            return;
        }
        
        if(is_null($message))
        {
            $message = $this->getRequest();
        }

        if(!is_null($message) && !is_array($message))
        {
            $message = [ 'text' => $message ];
        }

        try
        {
            if(is_null($message))
            {
                $result = $on->editKey($this->getKey());
            }
            else
            {
                $result = $on->editText($this->getArgs($message + [
                    'key' => $this->getKey(),
                ]));
            }
        }
        catch(Exception $e)
        {
            if(str_contains($e->getMessage(), 'same as a current content'))
            {
                $result = $on;
            }
            else
            {
                throw $e;
            }
        }

        if($result instanceof Msg)
        {
            $this->invokeListeners('requested', [ $this, $result ]);
        }
        return $result;
    }
    
}

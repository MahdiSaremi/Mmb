<?php
#auto-name
namespace Mmb\Controller\InlineMenu;

use Closure;
use Exception;
use Mmb\Tools\ATool;
use Mmb\Update\Message\Msg;

class InlineMenu
{

    public function __construct()
    {
        
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
     * @param array|string|null|null $message
     * @return Msg|null
     */
    public function send(array|string|null $message = null, string $method = 'response')
    {
        if(!is_null($message))
        {
            $message = $this->getRequest();
            if(!is_null($message))
            {
                return;
            }
        }

        return $method($message, [
            'key' => $this->getKey(),
        ]);
    }
    
    /**
     * پیام فعلی را ویرایش می کند و منو را نمایش می دهد
     *
     * @param array|string|null|null $message
     * @return Msg|null
     */
    public function edit(array|string|null $message = null)
    {
        if(!msg())
        {
            return;
        }
        
        if(!is_null($message))
        {
            $message = $this->getRequest();
        }

        try
        {
            if(is_null($message))
            {
                return msg()->editKey($this->getKey());
            }
            else
            {
                return msg()->editText($message, [
                    'key' => $this->getKey(),
                ]);
            }
        }
        catch(Exception $e)
        {
            if(str_contains($e->getMessage(), 'same as a current content'))
            {
                return msg();
            }
            throw $e;
        }
    }
    
}

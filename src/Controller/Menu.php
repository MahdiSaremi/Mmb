<?php

namespace Mmb\Controller; #auto

use Closure;
use Mmb\Controller\StepHandler\Handlable;
use Mmb\Controller\StepHandler\MenuHandler;
use Mmb\Exceptions\MmbException;
use Mmb\Tools\ATool;
use TypeError;

class Menu extends MenuBase
{

    /** @var MenuHandler */
    private $handler;
    private $key;
    private $mainMessage;
    private $isFixed = false;

    public function __construct()
    {
        $this->handler = new MenuHandler;
    }
    
	public function getHandler()
    {
        return $this->handler;
	}

    /**
     * ساخت منوی جدید
     * @param array|Closure|null $keys
     * @param string|array|Closure $message
     * @return static
     */
    public static function new($keys = null, $message = null)
    {
        $object = new static;

        if($keys !== null)
        {
            $object->keys($keys, $message);
        }

        return $object;
    }

    /**
     * ساخت منوی فیکس جدید
     *
     * بجای اینکه منو در دیتابیس ذخیره شود، منو را هر بار از این تابع لود می کند!
     * این کار بعضی اوقات بهینگی را بالا می برد
     *
     * `function menu() { return Menu::newFix($keys, static::class, 'menu'); }`
     * 
     * `function menu() { return Menu::newFix($keys, $this->method('menu')); }`
     * 
     * @param array|Closure $keys
     * @param string|array $class
     * @param string $method
     * @return static
     */
    public static function newFix($keys, $class, $method = 'menu')
    {
        $object = new static;

        $object->keys($keys);
        $object->fixOn($class, $method);

        return $object;
    }

    /**
     * تنظیم دکمه ها
     *
     * @param array|Closure $keys
     * @param string|array|Closure $message
     * @return $this
     */
    public function keys($keys, $message = null)
    {
        $this->key = $keys;
        if($message)
            $this->mainMessage = $message;
        return $this;
    }

    public $inlines = [];
    private $inlineMessages = [];

    /**
     * افزودن دکمه های شیشه ای
     *
     * توجه کنید که دکمه های شیشه ای تنها در زمانی که کاربر در این مرحله باشد کار می کند
     * همچنین باید نام دکمه ها متفاوت و یکتا باشد
     * 
     * @param string $name
     * @param array|Closure $keys
     * @param string|array|Closure $message
     * @return $this
     */
    public function inline($name, $keys, $message = null)
    {
        $this->inlines[$name] = $keys;
        if($message)
            $this->inlineMessages[$name] = $message;
        return $this;
    }

    public $isInline = false;

    /**
     * فعال کردن حالت اینلاین برای کلید های اصلی منو
     * 
     * توجه کنید که دکمه های شیشه ای تنها در زمانی که کاربر در این مرحله باشد کار می کند
     * همچنین باید نام دکمه ها متفاوت و یکتا باشد
     *
     * @return $this
     */
    public function inlineMode()
    {
        $this->isInline = true;
        return $this;
    }

    /**
     * فیکس بودن تعریف منو
     * 
     * بجای اینکه منو در دیتابیس ذخیره شود، منو را هر بار از این تابع لود می کند!
     * این کار بعضی اوقات بهینگی را بالا می برد
     *
     * `function menu() { return Menu::new($keys)->fixOn(static::class, 'menu'); }`
     * 
     * `function menu() { return Menu::new($keys)->fixOn($this->method('menu')); }`
     * 
     * همچنین این تابع، تارگت را نیز تنظیم می کند
     * 
     * @param string|array $class
     * @param string $method
     * @return $this
     */
    public function fixOn($class, $method = 'menu')
    {
        $this->handler->keys = null;
        if(!is_array($class))
            $class = [ $class, $method ];
        $this->isFixed = true;
        $this->handler->target = $class[0];
        $this->handler->fix = $class[1];

        return $this;
    }

    /**
     * تابعی که زمانی که هیچکدام از گزینه ها انتخاب نشد اجرا می شود
     * 
     * این تابع از کلاس تارگت فراخوانی می شود
     * 
     * @param string $method
     * @param mixed ...$args
     * @return $this
     */
    public function other($method, ...$args)
    {
        $this->handler->other = $method;
        $this->handler->other_args = $args;

        return $this;
    }

    /**
     * تنظیم کلاس تارگت
     *
     * @param string $class
     * @return $this
     */
    public function target($class)
    {
        $this->handler->target = $class;
        return $this;
    }

    protected $with = [];
    /**
     * استفاده از متغیر هایی در کلاس تارگت
     * 
     * با تعریف کردن نام متغیر ها، آن ها ذخیره می شوند و در زمان کلیک روی دکمه ای، آنها دوباره تعریف می شوند
     * 
     * از این ویژگی در فیکس منو ها نیز می توان استفاده کرد! بنا بر این می تواند راه خوبی برای ذخیره متغیر ها باشد
     *
     * @param string ...$name
     * @return $this
     */
    public function with(...$name)
    {
        array_push($this->with, ...$name);
        return $this;
    }



    /**
     * اجرای اجباری منوی فیکس
     *
     * @return Handlable|null
     */
    public function forceRunFixedHandler(array $with_values = [])
    {
        $this->handler->with = $with_values;
        $this->handler->fix = null;
        $this->handler->loadWiths();
        $this->handler->setKeys($this->key, $this->isInline, $this->inlines);
        return $this->handler->handle();
    }

    /**
     * تنظیم پیام پیشفرض منوی اصلی
     *
     * @param string|array|Closure|null $message
     * @return $this
     */
    public function setMainMessage($message)
    {
        $this->mainMessage = $message;
        return $this;
    }

    /**
     * تنظیم پیام پیشفرض منو
     *
     * @param string|null $name
     * @param string|array|Closure|null $message
     * @return $this
     */
    public function setMessage($name, $message)
    {
        if($name)
        {
            $this->inlineMessages[$name] = $message;
        }
        else
        {
            $this->mainMessage = $message;
        }
        return $this;
    }

    /**
     * گرفتن پیام منو
     *
     * @param string|null $name
     * @return array|null
     */
    public function getMessage($name = null)
    {
        $message = $name ? ($this->inlineMessages[$name] ?? null) : $this->mainMessage;

        if($message instanceof Closure)
        {
            $message = $message();
        }

        if($message && !is_array($message))
        {
            $message = [
                'type' => 'text',
                'text' => $message,
            ];
        }

        return $message;
    }

    /**
     * گرفتن لیست نام منو هایی که حاوی پیام تنظیم شده هستند
     *
     * @return array
     */
    public function getAllHasMessage()
    {
        $all = [];
        if($this->mainMessage)
            $all[] = null;

        foreach($this->inlineMessages as $name => $message)
        {
            if($message)
                $all[] = $name;
        }

        return $all;
    }

    /**
     * گرفتن لیست نام منو های اینلاین
     *
     * @return array
     */
    public function getInlineNames()
    {
        $all = array_keys($this->inlines);

        if($this->isInline)
            ATool::insert($all, 0, null);

        return $all;
    }

    /**
     * گرفتن لیست نام منو ها
     *
     * @return array
     */
    public function getAllNames()
    {
        $all = array_keys($this->inlines);
        ATool::insert($all, 0, null);

        return $all;
    }

    /**
     * گرفتن دکمه ها برای نمایش
     *
     * @return array
     */
    public function getMenuKey()
    {
        return $this->getKey();
    }

    /**
     * گرفتن دکمه ها برای نمایش
     *
     * @return array
     */
    public function getKey($name = '')
    {
        if(!$this->isFixed && (!$this->handler->keys && !$this->handler->inlines))
            $this->handler->setKeys($this->key, $this->isInline, $this->inlines);

        if($this->with)
            $this->handler->setWiths($this->with);

        $inline = $name ? true : $this->isInline;

        $res = [];
        $key = $name ? $this->inlines[$name] : $this->key;
        if($key instanceof Closure)
            $key = $key();
        foreach($key as $row)
        {
            $keyr = [];

            if($row)
            foreach($row as $btn)
            {
                if($btn && @$btn['visible'] !== false)
                    $keyr[] = $this->getSingleKey($btn, $inline);
            }

            if($keyr)
                $res[] = $keyr;
        }

        return $res;
    }

    /**
     * گرفتن و فیلتر یک دکمه تنها
     *
     * @param array $key
     * @return array
     */
    protected function getSingleKey($key, $inline)
    {
        if(!is_array($key))
        {
            throw new TypeError("Invalid key format, maybe you forgot to write the keys in different rows, like: expected [[A,B],[C]], but wrote [A,B,C]");
        }

        unset($key['method']);
        unset($key['args']);
        unset($key['visible']);

        if($inline)
            $key['data'] = "MENU:" . $key['text'];

        return $key;
    }

    public function __get($name)
    {
        if(!isset($this->inlines[$name]))
            throw new MmbException("Inline key '$name' is not defined!");

        return new MenuSubInline($this, $name);
    }

}

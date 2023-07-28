<?php

namespace Mmb\Controller; #auto

use BadMethodCallException;
use Closure;
use InvalidArgumentException;
use Mmb\Calling\DynCall;
use Mmb\Controller\FormV2\Fix\FixedForm2;
use Mmb\Controller\FormV2\Inputs\ConfirmInput;
use Mmb\Controller\Handler\Command;
use Mmb\Controller\Handler\MenuGlobHandler;
use Mmb\Controller\InlineMenu\InlineMenu;
use Mmb\Controller\StepHandler\NextRun;
use Mmb\ExtraThrow\ExtraErrorCallback;
use Mmb\ExtraThrow\ExtraErrorMessage;
use Mmb\Guard\HasGuard;
use Mmb\Listeners\InvokeEvent;
use Mmb\Listeners\Listeners;
use Mmb\Tools\Staticable;
use Mmb\Update\Message\Data\Poll;
use Mmb\Guard\Guard;
use Mmb\Tools\ATool;

class Controller implements InvokeEvent
{
    use Staticable;

    /**
     * اجرای تابع
     * 
     * در این نوع صدا زدن، دسترسی ها نیز بررسی می شوند
     *
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    public static function invoke($method = 'main', ...$args)
    {
        return Listeners::callMethod([ static::instance(), $method ], $args);
    }

    /**
     * اجرای تابع به همراه پیغام
     * 
     * در این نوع صدا زدن، دسترسی ها نیز بررسی می شوند
     * 
     * `Home::invokeWith("عملیات لغو شد", 'main');`
     * 
     * @param mixed $message
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    public static function invokeWith($message, $method = 'main', ...$args)
    {
        setMessage($message);
        return Listeners::callMethod([ static::instance(), $method ], $args);
    }

    /**
     * تابع مورد نظر را بدون بررسی دسترسی ها و شنونده ها صدا می زند
     *
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    public static function invokeSilent($method = 'main', ...$args)
    {
        return Listeners::callMethod([ static::instance(), $method ], $args, true);
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

    public function __invoke($method = 'main', ...$args)
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
        $this->requiredPermissions();
        // if(!$this->allowed())
        // {
        //     throw new AccessDaniedException([ $this, 'notAllowed' ]);
        //     // $result = $this->notAllowed();
        //     // return true;
        // }
	}

    use HasGuard
    {
        allowed as private _allowed;
    }

    /**
     * بررسی می کند دسترسی های مورد نیاز که در بوت تعریف شده اند را را داراست
     * 
     * @return bool
     */
    public static function allowed()
    {
        return static::instance()->_allowed();
    }

    public function __construct()
    {
        $this->boot();
    }

    public function boot()
    {
        
    }

    use DynCall;

    
    /**
     * ساخت کلید با پاسخی از این کنترلر
     * 
     * روش دوم استفاده: `static::keyMethod(Text, ...Args)`
     * 
     * @param string $text
     * @param string $method
     * @param mixed ...$args
     * @return array
     */
    public static function key($text, $method, ...$args)
    {
        if(!method_exists(static::class, $method))
            throw new BadMethodCallException("Initialize key with undefined method '$method' on '" . static::class . "', require to define: public function $method()");

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
        if(!method_exists(static::class, $method))
            throw new BadMethodCallException("Initialize key with undefined method '$method' on '" . static::class . "', require to define: public function $method()");

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
        if(!method_exists(static::class, $method))
            throw new BadMethodCallException("Initialize key with undefined method '$method' on '" . static::class . "', require to define: public function $method()");

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
        if(!method_exists(static::class, $method))
            throw new BadMethodCallException("Initialize key with undefined method '$method' on '" . static::class . "', require to define: public function $method()");

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
        if(!method_exists(static::class, $method))
            throw new BadMethodCallException("Initialize key with undefined method '$method' on '" . static::class . "', require to define: public function $method()");

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
        if(!method_exists(static::class, $method))
            throw new BadMethodCallException("Initialize key with undefined method '$method' on '" . static::class . "', require to define: public function $method()");

        return [
            'text' => $text,
            'poll' => [ 'type' => Poll::TYPE_QUIZ ],
            'method' => [ static::class, $method ],
            'args' => $args,
        ];
    }

    public static $_if_tree_stop;

    /**
     * ساخت کلید با پاسخی از این کنترلر
     * 
     * در ورودی اول تنظیم می کنید که قرار است دکمه برای کاربر قابل مشاهده باشد یا خیر
     * 
     * اگر دکمه را مخفی کنید، همچنان کاربر با ارسال این پیام، می تواند از متد آن استفاده کند!
     * 
     * روش دوم استفاده: `static::keyMethodIf(Condition, Text, ...Args)`
     * 
     * @param boolean $visible
     * @param string $text
     * @param string $method
     * @param mixed ...$args
     * @return array
     */
    public static function keyIf($visible, $text, $method, ...$args)
    {
        static::$_if_tree_stop = boolval($visible);
        if($visible)
            return static::key($text, $method, ...$args);
        else
            return static::key($text, $method, ...$args) + [ 'visible' => false ];
    }

    /**
     * ساخت کلید با پاسخی از این کنترلر
     * 
     * در ورودی اول تنظیم می کنید که قرار است دکمه برای کاربر قابل مشاهده باشد یا خیر
     * این شرط وابسته به آخرین شرطی ست که بصورت کلی استفاده کرده اید
     * 
     * اگر دکمه را مخفی کنید، همچنان کاربر با ارسال این پیام، می تواند از متد آن استفاده کند!
     * 
     * روش دوم استفاده: `static::keyMethodElseIf(Condition, Text, ...Args)`
     * 
     * @param boolean $visible
     * @param string $text
     * @param string $method
     * @param mixed ...$args
     * @return array
     */
    public static function keyElseIf($visible, $text, $method, ...$args)
    {
        if(static::$_if_tree_stop)
            $visible = false;

        if($visible)
        {
            static::$_if_tree_stop = true;
            return static::key($text, $method, ...$args);
        }
        else
            return static::key($text, $method, ...$args) + [ 'visible' => false ];
    }

    /**
     * ساخت کلید با پاسخی از این کنترلر
     * 
     * اگر شرط های زنجیره ای قبلی شما درست نباشد، این دکمه نمایش داده می شود
     * 
     * اگر دکمه را مخفی کنید، همچنان کاربر با ارسال این پیام، می تواند از متد آن استفاده کند!
     * 
     * روش دوم استفاده: `static::keyMethodElse(Text, ...Args)`
     * 
     * @param string $text
     * @param string $method
     * @param mixed ...$args
     * @return array
     */
    public static function keyElse($text, $method, ...$args)
    {
        $visible = !static::$_if_tree_stop;
        static::$_if_tree_stop = true;

        if($visible)
            return static::key($text, $method, ...$args);
        else
            return static::key($text, $method, ...$args) + [ 'visible' => false ];
    }

    /**
     * ساخت کلید با پاسخی از این کنترلر
     * 
     * این دکمه تنها زمانی قابل مشاهده ست که به این کلاس دسترسی وجود داشته باشد
     * 
     * اگر دکمه را مخفی کنید، همچنان کاربر با ارسال این پیام، می تواند از متد آن استفاده کند!
     * 
     * روش دوم استفاده: `static::keyMethodIfAllowed(Text, ...Args)`
     * 
     * @param string $text
     * @param string $method
     * @param mixed ...$args
     * @return array
     */
    public static function keyIfAllowed($text, $method, ...$args)
    {
        return static::keyIf(static::allowed(), $text, $method, ...$args);
    }

    /**
     * در صورتی که شرط اول برقرار نباشد، مجموعه ای از دکمه ها را غیذ قابل مشاهده می کند
     * 
     * دکمه هایی که قابل مشاهده نیستند، همچنان اگر کاربر پیام آن دکمه را بفرستد، متد آن صدا زده می شود
     * 
     * `static::visibleIf($this->allow('edit', $post), [ static::key("عملیات اول", 'first), static::key("عملیات دوم", 'second') ])`
     * 
     * توجه: فرقی نمی کند به چه شکل و در کجای آرایه دکمه ها از این تابع استفاده می کنید، این تابع دکمه ها را خودکار پیدا می کند
     *
     * @param boolean $condition
     * @param array $keys
     * @return array
     */
    public static function visibleIf($condition, array $keys)
    {
        if($condition)
        {
            return $keys;
        }

        if(isset($keys['text']))
        {
            $keys['visible'] = false;
        }
        else
        {
            foreach($keys as $i => $key)
            {
                if(is_array($key))
                {
                    $keys[$i] = static::visibleIf($condition, $key);
                }
            }
        }
        return $keys;
    }

    /**
     * در صورتی که به این کلاس دسترسی وجود داشته باشد، مجموعه ای از دکمه ها را غیذ قابل مشاهده می کند
     * 
     * دکمه هایی که قابل مشاهده نیستند، همچنان اگر کاربر پیام آن دکمه را بفرستد، متد آن صدا زده می شود
     * 
     * `Panel::visibleIfAllowed([ static::key("عملیات اول", 'first), static::key("عملیات دوم", 'second') ])`
     * 
     * توجه: فرقی نمی کند به چه شکل و در کجای آرایه دکمه ها از این تابع استفاده می کنید، این تابع دکمه ها را خودکار پیدا می کند
     *
     * @param array $keys
     * @return array
     */
    public static function visibleIfAllowed(array $keys)
    {
        return static::visibleIf(static::allowed(), $keys);
    }

    /**
     * اجرای توابع استاتیک
     * 
     * @param string $name
     * @param array $arguments
     * @throws InvalidArgumentException
     * @throws BadMethodCallException
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if(startsWith($name, 'key', true))
        {
            $name = substr($name, 3);
            $method = 'key';

            // Visible condition
            if(endsWith($name, 'if', true))
            {
                $name = substr($name, 0, -2);
                if (count($arguments) == 0)
                    throw new InvalidArgumentException("Key method '$name' required condition parameter, like 'Home::keyHomeIf(Settings::get('visible_home'), \"Home\", 'main)'");
                $method .= 'if';
                @ATool::insert($arguments, 2, $name);
            }
            elseif(endsWith($name, 'elseif', true))
            {
                $name = substr($name, 0, -6);
                if (count($arguments) == 0)
                    throw new InvalidArgumentException("Key method '$name' required condition parameter, like 'Home::keyHomeElseIf(Settings::get('visible_home'), \"Home\", 'main)'");
                $method .= 'elseif';
                @ATool::insert($arguments, 2, $name);
            }
            elseif(endsWith($name, 'else', true))
            {
                $name = substr($name, 0, -4);
                $method .= 'else';
                @ATool::insert($arguments, 1, $name);
            }
            // Visible if allowed
            elseif(endsWith($name, 'ifAllowed', true))
            {
                $name = substr($name, 0, -9);
                $method .= 'ifAllowed';
                @ATool::insert($arguments, 1, $name);
            }
            // Normal
            else
            {
                @ATool::insert($arguments, 1, $name);
            }

            return static::$method(...$arguments);
        }

        if(startsWith($name, 'method', true))
        {
            $name = substr($name, 6);
            return static::method($name);
        }

        if(startsWith($name, 'invoke', true))
        {
            $name = substr($name, 6);
            return static::invoke($name);
        }

        throw new BadMethodCallException("Call to undefined static method '$name' on '" . static::class . "'");
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
     * ایجاد گروه بندی مدیریت کننده ها با نیاز به سطح دسترسی این کلاس
     *
     * @param array|Closure $group
     * @return Handler\HandlerIf
     */
    public static function handlerGroup(array|Closure $group)
    {
        return Handler\Handler::groupIf(fn() => static::allowed(), $group);
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
     * ساخت منوی جدید
     *
     * `function menu() { return $this->createMenu($keys); }`
     * 
     * @param array|Closure $keys
     * @param string|array|Closure $message
     * @return Menu
     */
    public static function createMenu($keys, $message = null)
    {
        return Menu::new($keys)->target(static::class)->setMainMessage($message);
    }

    /**
     * ساخت منوی فیکس جدید
     * 
     * بجای اینکه منو در دیتابیس ذخیره شود، منو را هر بار از این تابع لود می کند!
     * این کار بعضی اوقات بهینگی را بالا می برد
     *
     * `function menu() { return $this->createFixMenu('menu', $keys); }`
     * 
     * @param string $method
     * @param array|Closure $keys
     * @param string|array|Closure $message
     * @return Menu
     */
    public static function createFixMenu(string $method, $keys, $message = null)
    {
        return Menu::newFix($keys, static::class, $method)->setMainMessage($message);
    }

    /**
     * گرفتن هندلر منوی فیکس
     * 
     * با افزودن این مقدار به هندلر ها، کاربر در هر مرحله ای می تواند به منو دسترسی داشته باشد
     *
     * @param string $method
     * @return MenuGlobHandler
     */
    public static function globalFixMenu($method = 'menu')
    {
        return new MenuGlobHandler(static::method($method));
    }

    /**
     * ساخت فرم فیکس جدید
     * 
     * از این تابع برای ایجاد یک فرم در یک تابع از کنترلر خود استفاده کنید. باید این مقدار را در تابعی ریترن کنید و نام آن تابع را به عنوان ورودی به این تابع بدهید، در غیر این صورت با مشکل روبرو خواهید شد
     *
     * `function form() { return $this->createFixForm('form')->input('name', fn(Input $input) => $input->text()->request("نام خود را وارد کنید:"))->cancel(static::method('main'))->finish(fn(Form2 $form) => static::invokeWith("موفق")); }`
     * 
     * @param string $method
     * @return FixedForm2
     */
    public static function createFixForm(string $method, string $back = 'main')
    {
        return (new FixedForm2(static::method($method)))
                ->backMethod($back);
    }

    /**
     * ساخت فرم فیکس تایید جدید
     *
     * از این تابع برای ایجاد یک فرم در یک تابع از کنترلر خود استفاده کنید. باید این مقدار را در تابعی ریترن کنید و نام آن تابع را به عنوان ورودی به این تابع بدهید، در غیر این صورت با مشکل روبرو خواهید شد
     *
     * `function deleteForm() { return $this->createFixFormConfirm('deleteForm', "از حذف اطمینان دارید؟", "تایید و حذف")->back(fn($message, $form) => static::invokeWith($message, 'main'))->finish(fn(Form2 $form) => $form->back("حذف شد")); }`
     * 
     * @return FixedForm2
     */
    public static function createFixFormConfirm(string $method, string|array $request, string $confirm, string $back = 'main')
    {
        return static::createFixForm($method, $back)
                ->input('confirm', fn(ConfirmInput $input) => $input->settings($confirm)->request($request), 'confirm');
    }
    
    /**
     * ساخت فرم فیکس تایید جدید
     *
     * از این تابع برای ایجاد یک فرم در یک تابع از کنترلر خود استفاده کنید. باید این مقدار را در تابعی ریترن کنید و نام آن تابع را به عنوان ورودی به این تابع بدهید، در غیر این صورت با مشکل روبرو خواهید شد
     *
     * `function deleteForm() { return $this->createFixFormConfirm2('deleteForm', fn(ConfirmInput $input) => $input->settings("تایید")->request("حذف شود؟"))->back(fn($message, $form) => static::invokeWith($message, 'main'))->finish(fn(Form2 $form) => $form->back("حذف شد")); }`
     * 
     * @return FixedForm2
     */
    public static function createFixFormConfirm2(string $method, Closure $confirm, string $back = 'main')
    {
        return static::createFixForm($method, $back)
                ->input('confirm', $confirm, 'confirm');
    }
    
    /**
     * ساخت منوی اینلاین
     * 
     * این تابع تنها آبجکتی ایجاد می کند که کار با منوی اینلاین را راحت تر می کند
     * 
     * پیشنهاد می شود این تابع را در ریترن تابع دلخواهی بنویسید. می توانید به تابع خود ورودی های دلخواهی بدهید، زیرا تابع از سمت ام ام بی اجرا نخواهد شد
     * 
     * `function menu($id) { if($film = Film::findCache($id)) return $this->createInlineMenu([ [ static::keyInline("حذف", $id) ] ])->request("وارد منوی فیلم {$film->name} ها شدید"); return $this->emptyInlineMenu(); }`
     *
     * @param array|Closure|null|null $key
     * @return InlineMenu
     */
    public static function createInlineMenu(array|Closure|null $key = null)
    {
        $menu = new InlineMenu();

        if(!is_null($key))
        {
            $menu->setKey($key);
        }

        return $menu;
    }

    /**
     * یک منوی اینلاین خالی ایجاد می کند
     *
     * @return InlineMenu
     */
    public function emptyInlineMenu()
    {
        return $this->createInlineMenu();
    }

    /**
     * خطایی را ترو می کند که اگر مدیریت نشود به کاربر نمایش داده می شود
     *
     * @param string|array $message
     * @throws ExtraErrorCallback
     */
    public function error(string|array $message)
    {
        throw new ExtraErrorCallback(fn() => $this->displayError($message));
    }

    /**
     * هنگام نمایش خطا صدا زده می شود
     *
     * @param string|array $message
     * @return mixed
     */
    public function displayError($message)
    {
        responseIt($message);
    }

}

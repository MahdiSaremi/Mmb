<?php

namespace Mmb\Controller\Form; #auto

use Mmb\Calling\DynCall;
use Mmb\Controller\Controller;
use Mmb\Controller\StepHandler\Handlable;
use Mmb\Controller\StepHandler\StepHandler;
use Mmb\Exceptions\MmbException;
use Mmb\Guard\Guard;
use Mmb\Guard\GuardAllowTrait;

/**
 * @property array $key دکمه های مربوط به اینپوت فعلی
 * @property array $keyboard دکمه های مربوط به اینپوت فعلی
 */
abstract class Form implements Handlable
{

    /** @var FormStepHandler */
    protected $handler;

    public function __construct($handler)
    {
        $this->handler = $handler;
        $this->boot();
    }

    public function getHandler()
    {
        return $this->handler;
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
    
    protected $_allowed_cache;
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
     * بررسی وجود دسترسی
     * 
     * @param string $name
     * @param mixed ...$args
     * @return bool
     */
    public function allow($name, ...$args)
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

    use DynCall {
        __get as private __dyn_get;
        __set as private __dyn_set;
    }


    /**
     * شروع فرم
     * 
     * @return mixed
     */
    public final function _start()
    {
        $this->startForm();
        $this->go_next = true;
        return $this->_next();
    }

    /**
     * اجرای فرم
     * 
     * @return mixed
     */
    public function _next()
    {
        // Check permission
        if(!$this->allowed())
        {
            return $this->notAllowed();
        }

        $this->current_input = $this->handler->current;
        try {
            $this->stepBeforeForm();
            $this->form();
        }
        catch(FilterError $error)
        {
            $this->lastError = $error;
            return $this->onError($error->getMessage());
        }
        catch(FindingInputFinished $finished)
        {
            return $finished->result;
        }
        finally
        {
            $this->stepAfterForm();
            $this->saveInHandler();
        }
        return $this->_finish();
    }

    /**
     * بروزرسانی اطلاعات در هندلر
     *
     * @return void
     */
    public function saveInHandler()
    {
        foreach($this->inputs as $name => $inp)
        {
            $this->handler->inputs[$name] = $inp->value();
        }
        if($this->_key)
            $this->handler->key = $this->_key;
        // if (isset($this->keyboard))
        //     $this->handler->key = $this->keyboard;
        // if (isset($this->key))
        //     $this->handler->key = $this->key;
    }

    /**
     * پایان فرم
     * 
     * @return mixed
     */
    public function _finish()
    {
        $this->endForm();
        return $this->onFinish();
    }

    /**
     * ایجاد و شروع فرم
     * 
     * @return mixed
     */
    public static function request($inputs = [])
    {
        $handler = new FormStepHandler(static::class);
        $handler->inputs = $inputs;
        $step = $handler->startForm();
        StepHandler::set($step);
        return $step;
    }

    /**
     * ساخت دکمه ای که زمان کلیک فرم شروع می شود
     * 
     * @param string $text
     * @return array
     */
    public static function key($text)
    {
        return FormStarter::key($text, 'start', static::class);
    }

    /**
     * زمان مقداردهی فرم صدا زده می شود
     * 
     * در این تابع باید اینپوت ها را تعریف کنید
     * 
     * @return void
     */
    public abstract function form();

    /**
     * تابعی که زمان شروع فرم صدا زده می شود
     * @return void
     */
    public function startForm()
    {
    }

    /**
     * تابعی که زمان پایان فرم صدا زده می شود
     * @return void
     */
    public function endForm()
    {
    }

    /**
     * تابعی که قبل از اجرای فرم اجرا می شود
     * @return void
     */
    public function stepBeforeForm()
    {
    }

    /**
     * تابعی که بعد از اجرای فرم اجرا می شود
     * @return void
     */
    public function stepAfterForm()
    {
    }

    /**
     * تابعی که زمان لغو فرم صدا زده می شود
     * @return Handlable|null
     */
    public abstract function onCancel();

    /**
     * آخرین خطا
     *
     * @var FilterError
     */
    public $lastError;

    /**
     * تابعی که زمان خطای اینپوت ها صدا زده می شود
     * @param string $error
     * @return Handlable|null
     */
    public function onError($error)
    {
        response($error);
    }

    /**
     * تابعی که زمانی که یک اینپوت را با متد پیشفرض درخواست می کنید، صدا زده می شود
     * @param string|array $text
     * @return Handlable|null
     */
    public function onRequest($text)
    {
        if(is_array($text))
        {
            response(['key' => $this->key] + $text);
        }
        else
        {
            response($text, [
                'key' => $this->key,
            ]);
        }
    }

    /**
     * لغو کردن فرم
     * 
     * @throws FindingInputFinished 
     * @return never
     */
    public final function cancel()
    {
        $this->canceled = true;
        throw new FindingInputFinished($this->onCancel());
    }

    /**
     * لغو کردن فرم همراه با خطا
     * 
     * @throws FindingInputFinished 
     * @return never
     */
    public final function cancelWithError($error)
    {
        $this->onError($error);
        $this->cancel();
    }

    /**
     * چیدمان کیبورد فرم
     * @param FormKey $key
     * @return array
     */
    public function keyboard(FormKey $key)
    {
        return [
            $key->options(),
            [ $key->skip(lang('form_keys.skip') ?: "رد کردن") ],
            [ $key->cancel(lang('form_keys.cancel') ?: "لغو") ],
        ];
    }

    /**
     * زمان پایان فرم صدا زده می شود
     * 
     * می توانید با محتویات وارد شده فرم عملیات خود را انجام دهید
     * 
     * @return Handlable|null
     */
    public abstract function onFinish();

    /**
     * گرفتن آپشن ها
     * @return array
     */
    public function getOptions()
    {
        return optional($this->running_input)->getOptions() ?: [];
    }

    protected $_key;

    public function &__get($name)
    {
        if($name == 'keyboard' || $name == 'key')
        {
            if($this->_key)
                return $this->_key;

            $key = new FormKey($this);
            $res = $this->keyboard($key);
            $res = FormKey::parse($res);
            $this->_key = $res;
            $result = FormKey::toKey($res);
            return $result;
        }

        if(isset($this->inputs[$name]))
        {
            $result = $this->inputs[$name]->value();
            return $result;
        }

        if(isset($this->handler->inputs[$name]))
        {
            return $this->handler->inputs[$name];
        }

        // error_log("Undefined input '$name'", 0);
        return $this->__dyn_get($name);
    }

    public function __set($name, $value)
    {
        if(isset($this->inputs[$name]))
        {
            $this->inputs[$name]->value($value);
            return;
        }
        elseif(array_key_exists($name, $this->handler->inputs ?? []))
        {
            $this->handler->inputs[$name] = $value;
            return;
        }

        $this->__dyn_set($name, $value);
    }
    public function __set_proto($name, $value)
    {
        $this->handler->inputs[$name] = $value;
        return true;
    }

    public function get($name, $default = null)
    {
        if(isset($this->inputs[$name]))
        {
            return $this->inputs[$name]->value();
        }

        if(isset($this->handler->inputs[$name]))
        {
            return $this->handler->inputs[$name];
        }

        return $default;
    }

    public function set($name, $value)
    {
        if(isset($this->inputs[$name]))
        {
            $this->inputs[$name]->value($value);
            return;
        }

        $this->handler->inputs[$name] = $value;
    }
    
    /** @var FormInput[] */
    private $inputs = [];
    public $go_next = false;
    public $current_input = '';
    /** @var FormInput */
    public $running_input = null;

    /**
     * تعریف اینپوت اجباری جدید
     * 
     * کاربر نمی تواند از گزینه رد کردن استفاده کند
     * 
     * @param string $name
     * @return void
     */
    public function required($name, $type = FormInput::class)
    {
        $input = (new $type($this, $name))->required();
        $this->newInput($input);
    }

    /**
     * حلقه ای برای درخواست اینپوت ها
     * 
     * این اینپوت ها پشت سر هم گرفته می شوند و در انتها دوباره از اول شروع می شود
     * 
     * @param string ...$names
     * @return void
     */
    public function requiredLoop(...$names)
    {
        if(!$names) return;

        if(count($names) == 1)
        {
            // Required & Required again
            $this->required($names[0]);
            $this->requiredAgain($names[0]);
        }
        else
        {
            // Required all
            foreach($names as $name)
                $this->required($name);

            // Forgot and go back to first
            $this->forgot(...$names);
            $this->required($names[0]);
        }
    }

    /**
     * تعریف اینپوت اختیاری جدید
     * 
     * کاربر می تواند از گزینه رد کردن استفاده کند
     * 
     * @param string $name
     * @return void
     */
    public function optional($name)
    {
        $input = (new FormInput($this, $name))->optional();
        $this->newInput($input);
    }

    public function requiredAgain($name)
    {
        if($inp = $this->inputs[$name] ?? false)
        {
            $type = get_class($inp);
            $this->inputs[$name] = $inp =
                    new $type($this, $name);
            if($inp->skipable)  $inp->optional();
            else                $inp->required();

            $this->running_input = $inp;
            $inp->initialize();
            $this->go_next = false;
            $this->current_input = $inp;
            $this->handler->current = $name;

            throw new FindingInputFinished($inp->request());
        }
        else
        {
            throw new MmbException("Input '$name' not defined in requiredAgain()");
        }
    }

    /**
     * فراموش کردن اینپوت ها
     * 
     * @param string ...$input
     * @return void
     */
    public function forgot(...$input)
    {
        foreach($input as $inp)
        {
            if($this->current_input == $inp)
                $this->current_input = null;
            unset($this->inputs[$inp]);
        }
    }

    /**
     * فراموش کردن تمامی اینپوت ها
     * 
     * @return void
     */
    public function forgotAll()
    {
        $this->inputs = [];
        $this->current_input = null;
    }

    /**
     * درخواست بررسی و گرفتن تمامی اینپوت ها
     * 
     * تنها اگر ورودی اول ترو شود تمامی ورودی های قبلی فراموش می شود، وگرنه باید با تابع فورگات دستی فراموش کرد
     * 
     * `$this->required('name'); $this->required('again'); if($this->again) { $this->forgot('again', 'name'); $this->requestAgain(); }`
     * 
     * توجه کنید این تابع را تنها باید در تابع فرم اجرا کرد!
     * 
     * @param bool $forgotAll
     * @return void
     */
    public function requestAgain($forgotAll = false)
    {
        if ($forgotAll)
            $this->forgotAll();

        $this->form();
        throw new FindingInputFinished(null);
    }

    public $optionSelected = false;
    public $skipped = false;
    public $canceled = false;
    protected function newInput(FormInput $input)
    {
        try
        {
            $name = $input->name;
            $this->running_input = $input;
            $this->inputs[$name] = $input;

            $input->value($this->handler->inputs[$name] ?? null);

            // Current input
            if($this->current_input == $name)
            {
                $input->initialize();

                $value = $this->handler->getValue($input, $this->optionSelected, $this->skipped, $this->canceled);
                $this->fillInput($name, $value, $this->skipped, $this->optionSelected, $this->canceled);

                $this->go_next = true;
            }

            // Next input
            elseif($this->go_next)
            {
                $input->initialize();

                // Skip if 'request' not filled
                if ($input->request)
                {
                    $this->go_next = false;
                    $this->handler->current = $name;
                    throw new FindingInputFinished($input->request());
                }

            }
        }
        catch(FilterError $error)
        {
            if($input->error)
            {
                $this->lastError = $error;
                $err = $input->error;
                throw new FindingInputFinished($err($error->getMessage()));
            }
            else
            {
                throw $error;
            }
        }
    }

    public function fillInput($name, $value, $skipped = false, $optionSelected = false, $canceled = false)
    {
        $input = $this->inputs[$name] ?? null;
        if(!$input)
            throw new MmbException("Input '$name' is not defined");

        // Set value
        $input->value($value);

        // Skip
        if($skipped)
        {
            $input->value(null);
            $input->skip();
        }

        // Cancel
        elseif($canceled)
        {
            if ($input->cancel)
                throw new FindingInputFinished($input->cancel());
            else
                $this->cancel(); // Throw FindingInputFinished
        }

        // Next
        elseif(!$optionSelected)
        {
            $input->filled();
        }

        $input->then();
    }

}

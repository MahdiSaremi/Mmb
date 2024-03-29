<?php

namespace Mmb\Controller\Form; #auto

use Mmb\Controller\Controller;
use Mmb\Controller\StepHandler\Handlable;
use Mmb\Exceptions\MmbException;

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
    }

    public function getHandler()
    {
        return $this->handler;
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
        $this->current_input = $this->handler->current;
        try {
            $this->stepBeforeForm();
            $this->form();
        }
        catch(FilterError $error)
        {
            return $this->onError($error->getMessage());
        }
        catch(FindingInputFinished $finished)
        {
            return $finished->result;
        }
        finally
        {
            $this->stepAfterForm();
            // Save finally values
            foreach($this->inputs as $name => $inp)
            {
                $this->handler->inputs[$name] = $inp->value();
            }
            if (isset($this->keyboard))
                $this->handler->key = $this->keyboard;
            if (isset($this->key))
                $this->handler->key = $this->key;
        }
        return $this->_finish();
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
        return $handler->startForm();
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
     * تابعی که زمان خطای اینپوت ها صدا زده می شود
     * @param string $error
     * @return Handlable|null
     */
    public function onError($error)
    {
        replyText($error);
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
            replyText(['key' => $this->key] + $text);
        }
        else
        {
            replyText($text, [
                'key' => $this->key,
            ]);
        }
    }

    /**
     * لغو کردن فرم
     * @throws FindingInputFinished 
     * @return never
     */
    public final function cancel()
    {
        $this->canceled = true;
        throw new FindingInputFinished($this->onCancel());
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

    public function __get($name)
    {
        
        if($name == 'keyboard' || $name == 'key')
        {
            $key = new FormKey($this);
            $res = $this->keyboard($key);
            $res = FormKey::parse($res);
            $this->_key = $res;
            return FormKey::toKey($this->$name = $res);
        }

        if(isset($this->inputs[$name]))
        {
            return $this->inputs[$name]->value();
        }

        if(isset($this->handler->inputs[$name]))
        {
            return $this->handler->inputs[$name];
        }

        error_log("Undefined input '$name'", 0);

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
    public function required($name)
    {
        $input = (new FormInput($this, $name))->required();
        $this->newInput($input);
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
            $this->current_input = null;
            $this->go_next = true;

            $this->newInput($inp);
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
                $this->$name($input);
                
                // Set value
                $input->value($this->handler->getValue($input, $this->optionSelected, $this->skipped, $this->canceled));

                // Skip
                if($this->skipped)
                {
                    $input->value(null);
                    $input->skip();
                    $this->go_next = true;
                }

                // Cancel
                elseif($this->canceled)
                {
                    if ($input->cancel)
                        throw new FindingInputFinished($input->cancel());
                    else
                        $this->cancel(); // Throw FindingInputFinished
                }

                // Next
                else
                {
                    $input->filled();
                    $this->go_next = true;
                }

                $input->then();

            }

            // Next input
            elseif($this->go_next)
            {

                $this->$name($input);

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
                $err = $input->error;
                throw new FindingInputFinished($err($error->getMessage()));
            }
            else
            {
                throw $error;
            }

        }
    }

}

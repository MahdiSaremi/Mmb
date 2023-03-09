<?php

namespace Mmb\Controller\Form; #auto

use Mmb\Listeners\HasCustomMethod;
use Mmb\Update\Message\Data\Poll;
use Mmb\Update\Upd;

class FormInput
{

    use HasCustomMethod;

    /** @var Form */
    public $form;
    public $name;

    public function __construct(Form $form, $name)
    {
        $this->form = $form;
        $this->name = $name;
    }

    public $value;
    public $valueSet = false;
    /**
     * تنظیم یا گرفتن مقدار اینپوت
     * @param mixed $value
     * @return mixed|FormInput
     */
    public function value($value = null)
    {
        if(!func_get_args())
        {
            return $this->value;
        }

        $this->value = $value;
        $this->valueSet = true;
        return $this;
    }

    /**
     * بررسی می کند که مقداری برای این اینپوت تنظیم شده است یا خیر
     *  
     * @return bool
     */
    public function hasValue()
    {
        return $this->valueSet;
    }

    public $skipable = false;

    /**
     * اجباری بودن اینپوت
     * 
     * کاربر نمی تواند از گزینه رد کردن استفاده کند
     * 
     * @return $this
     */
    public function required()
    {
        $this->skipable = false;
        return $this;
    }

    /**
     * اختیاری بودن اینپوت
     * 
     * کاربر می تواند از گزینه رد کردن استفاده کند
     * 
     * @return $this
     */
    public function optional()
    {
        $this->skipable = true;
        return $this;
    }

    public $_options = [];

    /**
     * تعریف آپشن ها
     * @param array|\Closure $callback
     * @return $this
     */
    public function options($callback)
    {
        $this->_options = $callback;
        return $this;
    }

    public function option($text, $value = null)
    {

        if(count(func_get_args()) == 1)
        {
            return [ 'text' => $text ];
        }

        return [ 'text' => $text, 'value' => $value ];

    }

    public function optionContact($text)
    {
        return [
            'text' => $text,
            'contact' => true,
        ];
    }

    public function optionLocation($text)
    {
        return [
            'text' => $text,
            'location' => true,
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
    public static function optionPoll($text)
    {
        return [
            'text' => $text,
            'poll' => [ 'type' => Poll::TYPE_REGULAR ],
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
    public static function optionPollQuiz($text)
    {
        return [
            'text' => $text,
            'poll' => [ 'type' => Poll::TYPE_QUIZ ],
        ];
    }


    /**
     * گرفتن آپشن ها
     * @return array<array>
     */
    public function getOptions()
    {
        $op = $this->_options;

        if(!is_array($op))
        {
            $op = $op();
        }

        if(!is_array($op))
        {
            return [];
        }

        return $op;
    }



    use UpdateFilter {
        UpdateFilter::applyFilters as protected _applyFilters;
    }

    public function applyFilters(Upd $upd)
    {        
        if ($this->onlyOptions)
            throw new FilterError(lang('invalid.options') ?: "تنها می توانید از گزینه ها استفاده کنید");

        return $this->_applyFilters($upd);
    }

    public $onlyOptions = false;
    /**
     * تنها می توان از گزینه ها استفاده کرد
     * 
     * @return $this
     */
    public function onlyOptions()
    {
        $this->onlyOptions = true;
        return $this;
    }

    public $request = false;
    /**
     * درخواست پر کردن اینپوت
     * 
     * @param \Closure|string|array|null $callback
     * @return mixed|FormInput
     */
    public function request($callback = null)
    {
        if($callback === null)
        {
            $f = $this->request;
            if($f)
            {
                if($f instanceof \Closure)
                {
                    return $f();
                }
                else
                {
                    return $this->form->onRequest($f);
                }
            }
        }

        $this->request = $callback;
        return $this;
    }

    public $error = false;
    /**
     * خطای اینپوت
     * 
     * @param \Closure|string $callback
     * @throws FilterError 
     * @return FormInput
     */
    public function error($callback)
    {
        if(!($callback instanceof \Closure))
        {
            throw new FilterError($callback);
        }

        $this->error = $callback;
        return $this;
    }

    public $filled = [];
    /**
     * زمان پر شدن مقدار توسط کاربر اجرا می شود
     * @param \Closure|null $callback
     * @return mixed|FormInput
     */
    public function filled($callback = null)
    {
        if($callback === null)
        {
            foreach($this->filled as $f)
            {
                if(($value = $f()) !== null)
                    return $value;
            }
        }

        $this->filled[] = $callback;
        return $this;
    }

    public $then = [];
    /**
     * بعد از پر شدن مقدار اجرا می شود
     * 
     * @param \Closure|null $callback
     * @return mixed|FormInput
     */
    public function then($callback = null)
    {
        if($callback === null)
        {
            foreach($this->then as $f)
            {
                if(($value = $f()) !== null)
                    return $value;
            }
        }

        $this->then[] = $callback;
        return $this;
    }

    public $cancel = [];
    /**
     * زمان لغو شدن فرم اجرا می شود
     * 
     * @param \Closure|null $callback
     * @return mixed|FormInput
     */
    public function cancel($callback = null)
    {
        if($callback === null)
        {
            foreach($this->cancel as $f)
            {
                if(($value = $f()) !== null)
                    return $value;
            }
        }

        $this->cancel[] = $callback;
        return $this;
    }

    public $skip = [];
    /**
     * زمان رد کردن اینپوت اجرا می شود
     * 
     * @param \Closure|null $callback
     * @return mixed|FormInput
     */
    public function skip($callback = null)
    {
        if($callback === null)
        {
            foreach($this->skip as $f)
            {
                if(($value = $f()) !== null)
                    return $value;
            }
        }

        $this->skip[] = $callback;
        return $this;
    }

}

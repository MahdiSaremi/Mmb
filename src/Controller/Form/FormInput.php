<?php

namespace Mmb\Controller\Form; #auto

use Mmb\Update\Message\Data\Poll;
use Mmb\Update\Upd;

class FormInput
{

    public $name;

    public function __construct($name)
    {
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
            'contact' => true,
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
    

    public $min = false;
    /**
     * تنظیم حداقل طول/عدد مجاز
     * @param int $len
     * @return $this
     */
    public function min($len)
    {
        $this->min = $len;
        return $this;
    }

    public $max = false;
    /**
     * تنظیم حداکثر طول/عدد مجاز
     * @param int $len
     * @return $this
     */
    public function max($len)
    {
        $this->max = $len;
        return $this;
    }
    

    /**
     * نوع
     * @var string
     */
    public $type = 'text';

    /**
     * تنظیم نوع متن
     * @return $this
     */
    public function text()
    {
        $this->type = 'text';
        return $this;
    }

    /**
     * تنظیم نوع متن تک خطی
     * @return $this
     */
    public function textSingleLine()
    {
        $this->type = 'text_singleline';
        return $this;
    }

    /**
     * تنظیم نوع عدد صحیح مثبت
     * @return $this
     */
    public function unsignedInteger()
    {
        $this->type = 'int_us';
        return $this;
    }

    /**
     * تنظیم نوع عدد صحیح
     * @return $this
     */
    public function integer()
    {
        $this->type = 'int';
        return $this;
    }

    /**
     * تنظیم نوع عدد اعشاری مثبت
     * @return $this
     */
    public function unsignedFloat()
    {
        $this->type = 'float_us';
        return $this;
    }

    /**
     * تنظیم نوع عدد اعشاری
     * @return $this
     */
    public function float()
    {
        $this->type = 'float';
        return $this;
    }

    /**
     * تنظیم نوع عدد
     * @return $this
     */
    public function number()
    {
        $this->type = 'float';
        return $this;
    }

    /**
     * تنظیم نوع رسانه
     * @return $this
     */
    public function media()
    {
        $this->type = 'media';
        return $this;
    }

    /**
     * تنظیم نوع تصویر
     * @return $this
     */
    public function photo()
    {
        $this->type = 'photo';
        return $this;
    }

    /**
     * تنظیم نوع پیغام
     * @return $this
     */
    public function msg()
    {
        $this->type = 'msg';
        return $this;
    }

    /**
     * تنظیم نوع آیدی پیام ارسالی
     * @return $this
     */
    public function msgid()
    {
        $this->type = 'msgid';
        return $this;
    }

    /**
     * تنظیم نوع پارامتر های پیام
     * @return $this
     */
    public function msgArgs()
    {
        $this->type = 'msgArgs';
        return $this;
    }

    public $supportFa = false;
    /**
     * پشتیبانی از اعداد فارسی
     * @return $this
     */
    public function supportFaNumber()
    {
        $this->supportFa = true;
        return $this;
    }

    /**
     * اعمال فیلتر ها بر روی آپدیت
     * @param Upd $upd
     * @throws FilterError 
     * @return mixed
     */
    public function applyFilters(Upd $upd)
    {
        if ($this->onlyOptions)
            throw new FilterError(lang('invalid.options') ?: "تنها می توانید از گزینه ها استفاده کنید");

        $value = $this->matchType($upd);
        $this->matchFilters($value);
        return $value;
    }

    /**
     * گرفتن مقدار بر اساس نوع اینپوت
     * @param Upd $upd
     * @throws FilterError 
     * @return mixed
     */
    protected function matchType(Upd $upd)
    {
        switch($this->type)
        {
            case 'upd':
                return $upd;

            case 'text':
                if (optional($upd->msg)->type != 'text')
                    throw new FilterError(lang('invalid.text') ?: "تنها پیغام متنی قابل قبول است");
                return optional($upd->msg)->text;

            case 'text_singleline':
                if (optional($upd->msg)->type != 'text')
                    throw new FilterError(lang('invalid.text') ?: "تنها پیغام متنی قابل قبول است");
                $text = $upd->msg->text;
                if (strpos($text, "\n"))
                    throw new FilterError(lang('invalid.single_line') ?: "متن شما باید تک خطی باشد");
                return $text;

            case 'int':
                if (optional($upd->msg)->type != 'text')
                    throw new FilterError(lang('invalid.text') ?: "تنها پیغام متنی قابل قبول است");
                $text = optional($upd->msg)->text;
                if ($this->supportFa)
                    $text = tr_num($text);
                if (!is_numeric($text) || strpos($text, '.') !== false)
                    throw new FilterError(lang('invalid.int') ?: "تنها عدد غیر اعشاری قابل قبول است");
                return intval($text);

            case 'int_us':
                if (optional($upd->msg)->type != 'text')
                    throw new FilterError(lang('invalid.text') ?: "تنها پیغام متنی قابل قبول است");
                $text = optional($upd->msg)->text;
                if ($this->supportFa)
                    $text = tr_num($text);
                if (!is_numeric($text) || strpos($text, '.') !== false)
                    throw new FilterError(lang('invalid.int') ?: "تنها عدد غیر اعشاری قابل قبول است");
                $int = intval($text);
                if ($int < 0)
                    throw new FilterError(lang('invalid.unsigned') ?: "تنها عدد مثبت قابل قبول است");
                return $int;

            case 'float':
                if (optional($upd->msg)->type != 'text')
                    throw new FilterError(lang('invalid.text') ?: "تنها پیغام متنی قابل قبول است");
                $text = optional($upd->msg)->text;
                if ($this->supportFa)
                    $text = tr_num($text);
                if (!is_numeric($text))
                    throw new FilterError(lang('invalid.number') ?: "تنها عدد قابل قبول است");
                return floatval($text);

            case 'float_us':
                if (optional($upd->msg)->type != 'text')
                    throw new FilterError(lang('invalid.text') ?: "تنها پیغام متنی قابل قبول است");
                $text = optional($upd->msg)->text;
                if ($this->supportFa)
                    $text = tr_num($text);
                if (!is_numeric($text))
                    throw new FilterError(lang('invalid.number') ?: "تنها عدد قابل قبول است");
                $float = floatval($text);
                if ($float < 0)
                    throw new FilterError(lang('invalid.unsigned') ?: "تنها عدد مثبت قابل قبول است");
                return $float;

            case 'msg':
                if (!$upd->msg)
                    throw new FilterError(lang('invalid.msg') ?: "تنها پیام قابل قبول است");
                return $upd->msg;

            case 'msgid':
                if (!$upd->msg)
                    throw new FilterError(lang('invalid.msg') ?: "تنها پیام قابل قبول است");
                return $upd->msg->id;

            case 'media':
                if (!$upd->msg)
                    throw new FilterError(lang('invalid.msg') ?: "تنها پیام قابل قبول است");
                $media = $upd->msg->media;
                if (!$media)
                    throw new FilterError(lang('invalid.media') ?: "تنها پیام رسانه ای قابل قبول است");
                return $media;

            case 'photo':
                if (!$upd->msg)
                    throw new FilterError(lang('invalid.msg') ?: "تنها پیام قابل قبول است");
                $media = $upd->msg->photo;
                if (!$media)
                    throw new FilterError(lang('invalid.photo') ?: "تنها پیام تصویری قابل قبول است");
                return end($media);

            case 'msgArgs':
                if (!$upd->msg)
                    throw new FilterError(lang('invalid.msg') ?: "تنها پیام قابل قبول است");
                $args = $upd->msg->createArgs();
                if (!$args)
                    throw new FilterError(lang('invalid.msg_type') ?: "این نوع پیام پشتیبانی نمی شود");
                return $args;

        }

        return optional($upd->msg)->text;
    }

    /**
     * بررسی فیلتر ها بر روی مقدار اینپوت
     * @param mixed $value
     * @throws FilterError 
     * @return void
     */
    protected function matchFilters($value)
    {

        if($this->min !== false)
        {
            if(is_string($value))
            {
                if (mb_strlen($value) < $this->min)
                    throw new FilterError(lang('filter.min_text', [ 'min' => $this->min ]) ?: "طول متن شما باید حداقل {$this->min} باشد");
            }
            elseif(is_numeric($value))
            {
                if (mb_strlen($value) < $this->min)
                    throw new FilterError(lang('filter.min_number', [ 'min' => $this->min ]) ?: "عدد شما باید حداقل {$this->min} باشد");
            }
        }

        if($this->max !== false)
        {
            if(is_string($value))
            {
                if (mb_strlen($value) > $this->max)
                    throw new FilterError(lang('filter.max_text', [ 'max' => $this->max ]) ?: "طول متن شما باید حداکثر {$this->max} باشد");
            }
            elseif(is_numeric($value))
            {
                if (mb_strlen($value) > $this->max)
                    throw new FilterError(lang('filter.max_number', [ 'max' => $this->max ]) ?: "عدد شما باید حداکثر {$this->max} باشد");
            }
        }

        if($this->unique !== false)
        {
            $model = $this->unique[0];
            $column = $this->unique[1];

            if ($model::query()->where($column, $value)->exists())
                throw new FilterError(lang('filter.unique', [ 'name' => $this->name, 'column' => $column ]) ?: "این مقدار قبلا وجود داشته است");
        }

        if($this->exists !== false)
        {
            $model = $this->exists[0];
            $column = $this->exists[1];

            if (!$model::query()->where($column, $value)->exists())
                throw new FilterError(lang('filter.exists', [ 'name' => $this->name, 'column' => $column ]) ?: "این مقدار وجود ندارد");
        }

    }

    public $unique = false;
    /**
     * یکتا بودن مقدار در دیتابیس
     * 
     * @param string $model
     * @param string|null $column
     * @return $this
     */
    public function unique($model, $column = null)
    {
        if (!$column)
            $column = $this->name;

        $this->unique = [ $model, $column ];
        return $this;
    }

    public $exists = false;
    /**
     * وجود داشتن دیتا در دیتابیس
     * 
     * @param string $model
     * @param string|null $column
     * @return $this
     */
    public function exists($model, $column = null)
    {
        if (!$column)
            $column = $this->name;

        $this->exists = [ $model, $column ];
        return $this;
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
     * @param \Closure|null $callback
     * @return mixed|FormInput
     */
    public function request($callback = null)
    {
        if($callback === null)
        {
            $f = $this->request;
            return $f ? $f() : null;
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

    public $filled = false;
    /**
     * زمان پر شدن مقدار توسط کاربر اجرا می شود
     * @param \Closure|null $callback
     * @return mixed|FormInput
     */
    public function filled($callback = null)
    {
        if($callback === null)
        {
            $f = $this->filled;
            return $f ? $f() : null;
        }

        $this->filled = $callback;
        return $this;
    }

    public $then = false;
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
            $f = $this->then;
            return $f ? $f() : null;
        }

        $this->then = $callback;
        return $this;
    }

    public $cancel = false;
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
            $f = $this->cancel;
            return $f ? $f() : null;
        }

        $this->cancel = $callback;
        return $this;
    }

    public $skip = false;
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
            $f = $this->skip;
            return $f ? $f() : null;
        }

        $this->skip = $callback;
        return $this;
    }

}

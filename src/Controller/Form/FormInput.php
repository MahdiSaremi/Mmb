<?php

namespace Mmb\Controller\Form; #auto

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

    public function hasValue()
    {
        return $this->value;
    }

    public $skipable = false;

    public function required()
    {
        $this->skipable = false;
        return $this;
    }

    public function optional()
    {
        $this->skipable = true;
        return $this;
    }

    public $_options = [];

    public function options($callback)
    {
        $this->_options = $callback;
        return $this;
    }

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

        $res = [];

        foreach($op as $option)
        {
            if(is_array($option))
            {
                foreach($option as $text => $value)
                {
                    $res[] = [ 'text' => $text, 'value' => $value ];
                }
            }
            else
            {
                $res[] = [ 'text' => $option ];
            }
        }
        
        return $res;
    }
    

    public $min = false;
    public function min($len)
    {
        $this->min = $len;
        return $this;
    }
    public $max = false;
    public function max($len)
    {
        $this->max = $len;
        return $this;
    }
    

    public $type = 'text';
    public function text()
    {
        $this->type = 'text';
        return $this;
    }

    public function unsignedInteger()
    {
        $this->type = 'int_us';
        return $this;
    }

    public function integer()
    {
        $this->type = 'int';
        return $this;
    }

    public function unsignedFloat()
    {
        $this->type = 'float_us';
        return $this;
    }

    public function float()
    {
        $this->type = 'float';
        return $this;
    }

    public function number()
    {
        $this->type = 'float';
        return $this;
    }

    public function media()
    {
        $this->type = 'media';
        return $this;
    }

    public function photo()
    {
        $this->type = 'photo';
        return $this;
    }

    public function msg()
    {
        $this->type = 'msg';
        return $this;
    }

    public function msgid()
    {
        $this->type = 'msgid';
        return $this;
    }

    public function msgArgs()
    {
        $this->type = 'msgArgs';
        return $this;
    }

    public $supportFa = false;
    public function supportFaNumber()
    {
        $this->supportFa = true;
        return $this;
    }

    public function applyFilters(Upd $upd)
    {
        if ($this->onlyOptions)
            throw new FilterError("تنها می توانید از گزینه ها استفاده کنید");

        $value = $this->matchType($upd);
        $this->matchFilters($value);
        return $value;
    }

    protected function matchType(Upd $upd)
    {
        switch($this->type)
        {
            case 'upd':
                return $upd;

            case 'text':
                if (optional($upd->msg)->type != 'text')
                    throw new FilterError("تنها پیغام متنی قابل قبول است");
                return optional($upd->msg)->text;

            case 'int':
                if (optional($upd->msg)->type != 'text')
                    throw new FilterError("تنها پیغام متنی قابل قبول است");
                $text = optional($upd->msg)->text;
                if ($this->supportFa)
                    $text = tr_num($text);
                if (!is_numeric($text) || strpos($text, '.') !== false)
                    throw new FilterError("تنها عدد غیر اعشاری قابل قبول است");
                return intval($text);

            case 'int_us':
                if (optional($upd->msg)->type != 'text')
                    throw new FilterError("تنها پیغام متنی قابل قبول است");
                $text = optional($upd->msg)->text;
                if ($this->supportFa)
                    $text = tr_num($text);
                if (!is_numeric($text) || strpos($text, '.') !== false)
                    throw new FilterError("تنها عدد غیر اعشاری قابل قبول است");
                $int = intval($text);
                if ($int < 0)
                    throw new FilterError("تنها عدد مثبت قابل قبول است");
                return $int;

            case 'float':
                if (optional($upd->msg)->type != 'text')
                    throw new FilterError("تنها پیغام متنی قابل قبول است");
                $text = optional($upd->msg)->text;
                if ($this->supportFa)
                    $text = tr_num($text);
                if (!is_numeric($text))
                    throw new FilterError("تنها عدد قابل قبول است");
                return floatval($text);

            case 'float_us':
                if (optional($upd->msg)->type != 'text')
                    throw new FilterError("تنها پیغام متنی قابل قبول است");
                $text = optional($upd->msg)->text;
                if ($this->supportFa)
                    $text = tr_num($text);
                if (!is_numeric($text))
                    throw new FilterError("تنها عدد قابل قبول است");
                $float = floatval($text);
                if ($float < 0)
                    throw new FilterError("تنها عدد مثبت قابل قبول است");
                return $float;

            case 'msg':
                if (!$upd->msg)
                    throw new FilterError("تنها پیام قابل قبول است");
                return $upd->msg;

            case 'msgid':
                if (!$upd->msg)
                    throw new FilterError("تنها پیام قابل قبول است");
                return $upd->msg->id;

            case 'media':
                if (!$upd->msg)
                    throw new FilterError("تنها پیام قابل قبول است");
                $media = $upd->msg->media;
                if (!$media)
                    throw new FilterError("تنها پیام رسانه ای قابل قبول است");
                return $media;

            case 'photo':
                if (!$upd->msg)
                    throw new FilterError("تنها پیام قابل قبول است");
                $media = $upd->msg->photo;
                if (!$media)
                    throw new FilterError("تنها پیام تصویری قابل قبول است");
                return end($media);

            case 'msgArgs':
                if (!$upd->msg)
                    throw new FilterError("تنها پیام قابل قبول است");
                $args = $upd->msg->createArgs();
                if (!$args)
                    throw new FilterError("این نوع پیام پشتیبانی نمی شود");
                return $args;

        }

        return optional($upd->msg)->text;
    }

    protected function matchFilters($value)
    {

        if($this->min !== false)
        {
            if(is_string($value))
            {
                if (mb_strlen($value) < $this->min)
                    throw new FilterError("طول متن شما باید حداقل {$this->min} باشد");
            }
            elseif(is_numeric($value))
            {
                if (mb_strlen($value) < $this->min)
                    throw new FilterError("عدد شما باید حداقل {$this->min} باشد");
            }
        }

        if($this->max !== false)
        {
            if(is_string($value))
            {
                if (mb_strlen($value) > $this->max)
                    throw new FilterError("طول متن شما باید حداکثر {$this->max} باشد");
            }
            elseif(is_numeric($value))
            {
                if (mb_strlen($value) > $this->max)
                    throw new FilterError("عدد شما باید حداکثر {$this->max} باشد");
            }
        }

        if($this->unique !== false)
        {
            $model = $this->unique[0];
            $column = $this->unique[1];

            if ($model::query()->where($column, $value)->exists())
                throw new FilterError("این مقدار قبلا وجود داشته است");
        }

        if($this->exists !== false)
        {
            $model = $this->exists[0];
            $column = $this->exists[1];

            if (!$model::query()->where($column, $value)->exists())
                throw new FilterError("این مقدار وجود ندارد");
        }

    }

    public $unique = false;
    public function unique($model, $column = null)
    {
        if (!$column)
            $column = $this->name;
            
        $this->unique = [ $model, $column ];
        return $this;
    }

    public $exists = false;
    public function exists($model, $column)
    {
        $this->exists = [ $model, $column ];
        return $this;
    }

    public $onlyOptions = false;
    public function onlyOptions()
    {
        $this->onlyOptions = true;
        return $this;
    }

    public $request = false;
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

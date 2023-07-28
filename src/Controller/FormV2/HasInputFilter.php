<?php
#auto-name
namespace Mmb\Controller\FormV2;

use Closure;
use InvalidArgumentException;
use Mmb\Tools\ATool;
use Mmb\Update\Message\Msg;
use Mmb\Update\Upd;

trait HasInputFilter
{

    private const CHECK = 1;
    private const FILTER = 2;

    private function _error($message)
    {
        if($message instanceof Closure)
        {
            $message = $message();
        }

        $this->error($message);
    }
    
    #region Input filter
    
    private $input_filters = [];

    
    /**
     * بررسی ورودی - مرحله اول
     * 
     * `$input->checkInput(fn($upd) => $upd->msg?->type == 'text', "Required text");`
     * 
     * `$input->checkInput(fn($upd) => $upd->msg?->media == 'text', fn() => lang("error.media"));`
     *
     * @param Closure $check
     * @param string|array|Closure $error
     * @return $this
     */
    public function checkInput(Closure $check, $error)
    {
        $this->input_filters[] = [ self::CHECK, $check, $error ];
        return $this;
    }
    /**
     * جایگذاری ورودی - مرحله اول
     * 
     * `$input->filterInput(fn($upd) => $upd->msg, "Message required");`
     * 
     * `$input->filterInput(fn($msg) => $msg->text);`
     * 
     * توجه: در مرحله دوم نوع ورودی ها مورد بررسی قرار می گیرد، بنابر این اگر از توابع پیشفرض استفاده می کنید، مقدار ورودی را از آپدیت تغییر ندهید
     *
     * @param Closure $filter
     * @param null|string|array|Closure $error
     * @param bool $errorOnNull اگر ترو باشد، مقدار را دقیقا با نال مقایسه می کند
     * @return $this
     */
    public function filterInput(Closure $filter, $error = null, bool $errorOnNull = true)
    {
        $this->input_filters[] = [ self::FILTER, $filter, $error, $errorOnNull ];
        return $this;
    }

    #endregion

    #region Type filter

    private $type_filter = null;

    /**
     * جایگذاری برای نوع - مرحله ذوم
     * 
     * `$input->filterType(fn($upd) => $upd->msg?->text, "Text required");`
     * 
     * `$input->filterType(fn($msg) => $msg->text);`
     * 
     * توجه: تنها یک فیلتر نوع می تواند همزمان قرار بگیرد
     *
     * @param Closure $filter
     * @param null|string|array|Closure $error
     * @param bool $errorOnNull اگر ترو باشد، مقدار را دقیقا با نال مقایسه می کند
     * @return $this
     */
    public function filterType(Closure $filter, $error = null, bool $errorOnNull = true)
    {
        if($this->type_filter)
        {
            throw new InvalidArgumentException("Can't set two type filter in same input");
        }

        $this->type_filter = [ $filter, $error, $errorOnNull ];
        return $this;
    }

    /**
     * تنظیم نوع متنی
     *
     * @param string|array|Closure $error Default: `lang('form2.type.text')`
     * @return $this
     */
    public function text($error = null)
    {
        return $this->filterType(
            function(Upd $upd)
            {
                if($upd?->msg?->type != Msg::TYPE_TEXT)
                {
                    return null;
                }

                return $upd?->msg?->text;
            },
            fn() => $this->_error($error ?? lang('form2.type.text') ?: "تنها پیام متنی قابل قبول است")
        );
    }
    
    /**
     * تنظیم نوع متن تک خطی
     *
     * @param string|array|Closure $error Default: `lang('form2.type.text')`
     * @param string|array|Closure $errorSingleLine Default: `lang('form2.type.single_line')`
     * @return $this
     */
    public function textSingleLine($error = null, $errorSingleLine = null)
    {
        return $this->filterType(
            function(Upd $upd) use($errorSingleLine)
            {
                if($upd?->msg?->type != Msg::TYPE_TEXT)
                {
                    return null;
                }

                $text = $upd?->msg?->text;

                if(str_contains("$text", "\n"))
                {
                    $this->_error($errorSingleLine ?? lang('form2.type.single_line') ?: "تنها پیام تک خطی قابل قبول است");
                }

                return $text;
            },
            fn() => $this->_error($error ?? lang('form2.type.text') ?: "تنها پیام متنی قابل قبول است")
        );
    }
    
    /**
     * تنظیم نوع عدد صحیح
     *
     * @param string|array|Closure $error Default: `lang('form2.type.int')`
     * @return $this
     */
    public function int($error = null)
    {
        return $this->filterType(
            function(Upd $upd)
            {
                if($upd?->msg?->type != Msg::TYPE_TEXT)
                {
                    return null;
                }

                $text = $upd?->msg?->text;

                if(!is_numeric($text) || str_contains($text, "."))
                {
                    return null;
                }

                return intval($text);
            },
            fn() => $this->_error($error ?? lang('form2.type.int') ?: "تنها عدد صحیح قابل قبول است")
        );
    }
    
    
    /**
     * تنظیم نوع عدد صحیح
     *
     * @param string|array|Closure $error Default: `lang('form2.type.int')`
     * @return $this
     */
    public function integer($error = null)
    {
        return $this->int($error);
    }
    
    
    /**
     * تنظیم نوع عدد صحیح مثبت
     *
     * @param string|array|Closure $error Default: `lang('form2.type.int')`
     * @param string|array|Closure $errorUnsigned Default: `lang('form2.type.unsigned')`
     * @return $this
     */
    public function unsignedInt($error = null, $errorUnsigned = null)
    {
        return $this->filterType(
            function(Upd $upd) use($errorUnsigned)
            {
                if($upd?->msg?->type != Msg::TYPE_TEXT)
                {
                    return null;
                }

                $text = $upd?->msg?->text;

                if(!is_numeric($text) || str_contains($text, "."))
                {
                    return null;
                }

                $num = intval($text);
                if($num < 0)
                {
                    $this->_error($errorUnsigned ?? lang('form2.type.unsigned') ?: "تنها عدد مثبت قابل قبول است");
                }

                return $num;
            },
            fn() => $this->_error($error ?? lang('form2.type.int') ?: "تنها عدد صحیح قابل قبول است")
        );
    }
    
    
    /**
     * تنظیم نوع عدد صحیح مثبت
     *
     * @param string|array|Closure $error Default: `lang('form2.type.int')`
     * @param string|array|Closure $errorUnsigned Default: `lang('form2.type.unsigned')`
     * @return $this
     */
    public function unsignedInteger($error = null, $errorUnsigned = null)
    {
        return $this->unsignedInt($error, $errorUnsigned);
    }
    
    /**
     * تنظیم نوع عدد
     *
     * @param string|array|Closure $error Default: `lang('form2.type.int')`
     * @return $this
     */
    public function num($error = null)
    {
        return $this->filterType(
            function(Upd $upd)
            {
                if($upd?->msg?->type != Msg::TYPE_TEXT)
                {
                    return null;
                }

                $text = $upd?->msg?->text;

                if(!is_numeric($text))
                {
                    return null;
                }

                return +$text;
            },
            fn() => $this->_error($error ?? lang('form2.type.number') ?: "تنها عدد قابل قبول است")
        );
    }
    
    
    /**
     * تنظیم نوع عدد
     *
     * @param string|array|Closure $error Default: `lang('form2.type.number')`
     * @return $this
     */
    public function number($error = null)
    {
        return $this->num($error);
    }
    
    
    /**
     * تنظیم نوع عدد مثبت
     *
     * @param string|array|Closure $error Default: `lang('form2.type.int')`
     * @param string|array|Closure $errorUnsigned Default: `lang('form2.type.unsigned')`
     * @return $this
     */
    public function unsignedNum($error = null, $errorUnsigned = null)
    {
        return $this->filterType(
            function(Upd $upd) use($errorUnsigned)
            {
                if($upd?->msg?->type != Msg::TYPE_TEXT)
                {
                    return null;
                }

                $text = $upd?->msg?->text;

                if(!is_numeric($text))
                {
                    return null;
                }

                $num = +$text;
                if($num < 0)
                {
                    $this->_error($errorUnsigned ?? lang('form2.type.unsigned') ?: "تنها عدد مثبت قابل قبول است");
                }

                return $num;
            },
            fn() => $this->_error($error ?? lang('form2.type.number') ?: "تنها عدد قابل قبول است")
        );
    }
    
    
    /**
     * تنظیم نوع عدد مثبت
     *
     * @param string|array|Closure $error Default: `lang('form2.type.number')`
     * @param string|array|Closure $errorUnsigned Default: `lang('form2.type.unsigned')`
     * @return $this
     */
    public function unsignedNumber($error = null, $errorUnsigned = null)
    {
        return $this->num($error);
    }
    
    /**
     * تنظیم نوع پیام
     *
     * @param string|array|Closure $error Default: `lang('form2.type.msg')`
     * @return $this
     */
    public function msg($error = null)
    {
        return $this->filterType(
            fn(Upd $upd) => $upd->msg,
            fn() => $this->_error($error ?? lang('form2.type.msg') ?: "تنها پیام قابل قبول است")
        );
    }
    
    /**
     * تنظیم نوع آیدی پیام
     *
     * @param string|array|Closure $error Default: `lang('form2.type.msg')`
     * @return $this
     */
    public function msgid($error = null)
    {
        return $this->filterType(
            fn(Upd $upd) => $upd->msg?->id,
            fn() => $this->_error($error ?? lang('form2.type.msg') ?: "تنها پیام قابل قبول است")
        );
    }
    
    /**
     * تنظیم نوع دیتای پیام
     *
     * @param string|array|Closure $error Default: `lang('form2.type.msg_type')`
     * @return $this
     */
    public function msgArgs($error = null)
    {
        return $this->filterType(
            fn(Upd $upd) => $upd->msg?->createArgs(),
            fn() => $this->_error($error ?? lang('form2.type.msg_type') ?: "این پیام پشتیبانی نمی شود")
        );
    }
    
    /**
     * تنظیم نوع آیدی پیام
     *
     * @param string|array|Closure $error Default: `lang('form2.type.media')`
     * @return $this
     */
    public function media($error = null)
    {
        return $this->filterType(
            fn(Upd $upd) => $upd->msg?->media,
            fn() => $this->_error($error ?? lang('form2.type.media') ?: "این پیام پشتیبانی نمی شود")
        );
    }
    
    /**
     * تنظیم نوع آیدی پیام
     *
     * @param string|array|Closure $error Default: `lang('form2.type.photo')`
     * @return $this
     */
    public function photo($error = null)
    {
        return $this->filterType(
            fn(Upd $upd) => $upd->msg?->photo,
            fn() => $this->_error($error ?? lang('form2.type.photo') ?: "تنها تصویر قابل قبول است")
        );
    }

    /**
     * تنظیم تنها دکمه قابل قبول
     *
     * @param string|array|Closure $error Default: `lang('form2.type.options')`
     * @return $this
     */
    public function onlyOptions($error = null)
    {
        return $this->filterType(fn($upd) => $this->_error($error ?? lang('form2.type.options') ?: "تنها می توانید از گزینه ها استفاده کنید"));
    }

    #endregion

    #region Limit filter

    private $limit_filters = [];

    /**
     * بررسی مقدار نوع - مرحله سوم
     * 
     * `$input->checkResult(fn($text) => str_contains($text, "\n"), "Must be single line");`
     *
     * @param Closure $check
     * @param string|array|Closure $error
     * @return $this
     */
    public function checkLimit(Closure $check, $error)
    {
        $this->limit_filters[] = [ self::CHECK, $check, $error ];
        return $this;
    }
    /**
     * جایگذاری مقدار نوع - مرحله سوم
     * 
     * `$input->filterResult(fn($upd) => $upd->msg, "Message required");`
     * 
     * `$input->filterResult(fn($msg) => $msg->text);`
     *
     * @param Closure $filter
     * @param null|string|array|Closure $error
     * @param bool $errorOnNull اگر ترو باشد، مقدار را دقیقا با نال مقایسه می کند
     * @return $this
     */
    public function filterLimit(Closure $filter, $error = null, bool $errorOnNull = true)
    {
        $this->limit_filters[] = [ self::FILTER, $filter, $error, $errorOnNull ];
        return $this;
    }

    /**
     * محدود کردن عدد/طول رشته
     *
     * @param integer|null $min
     * @param integer|null $max
     * @param null|string|array|Closure $error Default: `lang('form2.filter.min_text OR min_number OR max_text OR max_number')`
     * @return $this
     */
    public function limit(?int $min = null, ?int $max = null, $error = null)
    {
        if(is_null($min) && is_null($max))
            return $this;

        if(!is_null($min) && !is_null($max))
        {
            return $this->checkLimit(
                function($value) use($min, $max, $error)
                {
                    if(is_int($value) || is_float($value))
                    {
                        if($value < $min)
                        {
                            $this->_error($error ?? lang('form2.filter.min_number', [ 'min' => $min ]) ?: "عدد شما باید بزرگتر از {$min} باشد");
                        }
                        if($value > $max)
                        {
                            $this->_error($error ?? lang('form2.filter.max_number', [ 'max' => $max ]) ?: "عدد شما باید کوچکتر از {$max} باشد");
                        }
                    }
                    else
                    {
                        $value = mb_strlen($value);
                        if($value < $min)
                        {
                            $this->_error($error ?? lang('form2.filter.min_text', [ 'min' => $min ]) ?: "طول متن شما باید بزرگتر از {$min} باشد");
                        }
                        if($value > $max)
                        {
                            $this->_error($error ?? lang('form2.filter.max_text', [ 'max' => $max ]) ?: "طول متن شما باید کوچکتر از {$max} باشد");
                        }
                    }
                    return true;
                },
                null
            );
        }
        elseif(!is_null($min))
        {
            return $this->min($min, $error);
        }
        else
        {
            return $this->max($max, $error);
        }
    }
    /**
     * محدود کردن حداقل عدد/طول رشته
     *
     * @param integer $min
     * @param null|string|array|Closure $error Default: `lang('form2.filter.min_text OR min_number')`
     * @return $this
     */
    public function min(int $min, $error = null)
    {
        return $this->checkLimit(
            function($value) use($min, $error)
            {
                if(is_int($value) || is_float($value))
                {
                    if($value < $min)
                    {
                        $this->_error($error ?? lang('form2.filter.min_number', [ 'min' => $min ]) ?: "عدد شما باید بزرگتر از {$min} باشد");
                    }
                }
                else
                {
                    $value = mb_strlen($value);
                    if($value < $min)
                    {
                        $this->_error($error ?? lang('form2.filter.min_text', [ 'min' => $min ]) ?: "طول متن شما باید بزرگتر از {$min} باشد");
                    }
                }
                return true;
            },
            null
        );
    }
    /**
     * محدود کردن حداکثر عدد/طول رشته
     *
     * @param integer $min
     * @param null|string|array|Closure $error Default: `lang('form2.filter.max_text OR max_number')`
     * @return $this
     */
    public function max(int $max, $error = null)
    {
        return $this->checkLimit(
            function($value) use($max, $error)
            {
                if(is_int($value) || is_float($value))
                {
                    if($value > $max)
                    {
                        $this->_error($error ?? lang('form2.filter.max_number', [ 'max' => $max ]) ?: "عدد شما باید کوچکتر از {$max} باشد");
                    }
                }
                else
                {
                    $value = mb_strlen($value);
                    if($value > $max)
                    {
                        $this->_error($error ?? lang('form2.filter.max_text', [ 'max' => $max ]) ?: "طول متن شما باید کوچکتر از {$max} باشد");
                    }
                }
                return true;
            },
            null
        );
    }

    #endregion

    #region Result filter

    private $result_filters = [];

    /**
     * بررسی نتیجه - مرحله آخر
     *
     * @param Closure $check
     * @param string|array|Closure $error
     * @return $this
     */
    public function checkResult(Closure $check, $error)
    {
        $this->result_filters[] = [ self::CHECK, $check, $error ];
        return $this;
    }
    /**
     * جایگذاری نتیجه - مرحله آخر
     *
     * @param Closure $filter
     * @param null|string|array|Closure $error
     * @param bool $errorOnNull اگر ترو باشد، مقدار را دقیقا با نال مقایسه می کند
     * @return $this
     */
    public function filterResult(Closure $filter, $error = null, bool $errorOnNull = true)
    {
        $this->result_filters[] = [ self::FILTER, $filter, $error, $errorOnNull ];
        return $this;
    }

    #endregion

    /**
     * اعمال تمامی فیلتر ها روی آپدیت
     *
     * @param Upd $upd
     * @return mixed
     */
    public function applyFilters(Upd $upd, ?Closure $bwCallback = null)
    {
        $this->skipType = false;
        $data = $upd;

        foreach($this->input_filters as $filter)
        {
            $data = $this->applySingleFilter($data, $filter);
        }
        if($bwCallback)
        {
            $data = $bwCallback($data);
        }
        if(!$this->skipType)
        {
            if($filter = $this->type_filter)
            {
                $data = $this->applySingleFilter($data, $filter, self::FILTER);
            }
            foreach($this->limit_filters as $filter)
            {
                $data = $this->applySingleFilter($data, $filter);
            }
        }
        foreach($this->result_filters as $filter)
        {
            $data = $this->applySingleFilter($data, $filter);
        }

        return $data;
    }

    /**
     * اعمال یک فیلتر
     *
     * @param mixed $data
     * @param array $filter
     * @param ?string $type
     * @return mixed
     */
    private function applySingleFilter($data, array $filter, $type = null)
    {
        if(!$type)
        {
            $type = $filter[0];
            ATool::remove($filter, 0);
        }

        if($type == self::CHECK)
        {
            list($callback, $error) = $filter;

            if(!$callback($data))
            {
                $this->_error($error);
            }
        }
        elseif($type == self::FILTER)
        {
            list($callback, $error, $errorOnNull) = $filter;

            $data = $callback($data);
            if(!is_null($error))
            {
                if($errorOnNull ? $data === null : !$data)
                {
                    $this->_error($error);
                }
            }
        }

        return $data;
    }

    private $skipType;
    /**
     * از مرحله بررسی نوع می گذرد
     *
     * @return void
     */
    public function skipType()
    {
        $this->skipType = true;
    }
    /**
     * بررسی رد شدن از مرحله نوع
     *
     * @return boolean
     */
    public function isSkipedType()
    {
        return $this->skipType;
    }

    /**
     * راهنما:
     * 
     * چهار مرحله برای فیلتر کردن وجود دارد
     * 
     * **Input**:
     * 
     * مرحله اول: مرحله ای که هنوز هیچ عملیاتی صورت نگرفته و آپدیت خام را در هر صورتی دریافت می کنید
     * 
     * **Type**:
     * 
     * مرحله دوم: این مرحله و مرحله سوم تنها زمانی اجرا می شوند که، مقدار های اجباری قرار نگرفته باشند. مقدار های اجباری مانند کلید ها می باشند. نکته این مرحله این است که تنها یک تابع فیلتر همزمان می تواند تعریف شود
     * 
     * **Limit**:
     * 
     * مرحله سوم: این مرحله همانند مرحله دوم تنها زمانی اجرا می شود که مقدار های اجباری قرار نگرفته باشند. در این مرحله می توانید فیلتر هایی را روی مقدار نوع ورودی اعمال کنید
     * 
     * **Result**:
     * 
     * مرحله چهارم: این مرحله مرحله پایانی است که می توانید فیلتر ها را چه روی مقدار های اجباری، و چه مقدار های رد شده از فیلتر دوم اعمال کنید
     * 
     * ----
     * 
     * در نهایت اکثر استفاده شما از مرحله دوم و سوم می باشد که توابع پیشفرض آن بدون هیچ پسوندی نام گذاری شده اند
     *
     * @return void
     */
    public function filterHelp()
    {
        // This is just document help!
    }

}

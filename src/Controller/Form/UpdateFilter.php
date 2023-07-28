<?php
#auto-update
namespace Mmb\Controller\Form;

use Closure;
use Mmb\Lang\Lang;
use Mmb\Tools\Text;
use Mmb\Update\Upd;
use Mmb\Update\User\UserInfo;

trait UpdateFilter
{


    #region Update checking
    
    public $_checks = [];

    /**
     * بررسی می کند مقدار مورد نظر در آپدیت وجود دارد
     * 
     * `$input->check('msg.text', "متن وجود ندارد");`
     * 
     * `$input->check(function() { return isset(upd()->msg->text); }, "متن وجود ندارد");`
     *
     * @param string|Closure $selector
     * @param string|array $error
     * @return $this
     */
    public function check($selector, $error)
    {
        $this->_checks[] = [ $error, $selector ];
        return $this;
    }

    /**
     * بررسی می کند مقدار مورد نظر در آپدیت برابر با ... است
     *
     * @param string $selector
     * @param mixed $value
     * @param string|array $error
     * @return $this
     */
    public function checkIs($selector, $value, $error)
    {
        $this->_checks[] = [ $error, $selector, $value ];
        return $this;
    }

    /**
     * بررسی می کند آپدیت پیام است و نوع پیام نیز مقدار ورودی ست
     *
     * @param ?string $type
     * @return $this
     */
    public function checkMsg($type = null, $error = null, $typeError = null)
    {
        $this->check('msg', $error ?: [ 'invalid.msg' ]);
        if($type)
            return $this->check($type, $typeError ?: [ 'invalid.msg_type' ]);

        return $this;
    }

    /**
     * بررسی می کند آپدیت پیام رسانه ایست
     *
     * @param ?string $error
     * @return $this
     */
    public function checkMedia($error = null)
    {
        return $this->check('msg.media', $error ?: [ 'invalid.media' ]);
    }

    #endregion


    #region Value checking

    public $_value_checkers = [];
    
    /**
     * تنظیم حداقل طول/عدد مجاز
     * 
     * @param int $len
     * @return $this
     */
    public function min($len, $error = null)
    {
        $this->_value_checkers[] = [ 'min', $len, $error ];
        return $this;
    }
    protected function _apply_min(&$value, $min, $error)
    {
        if(is_string($value))
        {
            if (mb_strlen($value) < $min)
                $this->filterErrorThrow('filter.min_text', $error, "طول متن شما باید حداقل %min% باشد", [ 'min' => $min ]);
        }
        elseif(is_numeric($value))
        {
            if ($value < $min)
                $this->filterErrorThrow('filter.min_number', $error, "عدد شما باید حداقل %min% باشد", [ 'min' => $min ]);
        }
    }

    /**
     * تنظیم حداکثر طول/عدد مجاز
     * 
     * @param int $len
     * @return $this
     */
    public function max($len, $error = null)
    {
        $this->_value_checkers[] = [ 'max', $len, $error ];
        return $this;
    }
    protected function _apply_max(&$value, $max, $error)
    {
        if(is_string($value))
        {
            if (mb_strlen($value) > $max)
                $this->filterErrorThrow('filter.max_text', $error, "طول متن شما باید حداکثر %max% باشد", [ 'max' => $max ]);
        }
        elseif(is_numeric($value))
        {
            if ($value > $max)
                $this->filterErrorThrow('filter.max_number', $error, "عدد شما باید حداکثر %max% باشد", [ 'max' => $max ]);
        }
    }
    
    /**
     * تنظیم حداقل و حداکثر طول/عدد مجاز
     * 
     * @param mixed $min
     * @param mixed $max
     * @return UpdateFilter
     */
    public function between($min, $max, $error = null)
    {
        $this->_value_checkers[] = [ 'between', $min, $max, $error ];
        return $this;
    }
    protected function _apply_between(&$value, $min, $max, $error)
    {
        if(is_string($value))
        {
            $len = mb_strlen($value);
            if ($len < $min || $len > $max)
                $this->filterErrorThrow('filter.between_text', $error, "طول متن شما باید حداقل %min% و حداکثر %max% باشد", [ 'min' => $min, 'max' => $max ]);
        }
        elseif(is_numeric($value))
        {
            if ($value < $min || $value > $max)
                $this->filterErrorThrow('filter.between_number', $error, "عدد شما باید حداقل %min% و حداکثر %max% باشد", [ 'min' => $min, 'max' => $max ]);
        }
    }
    

    /**
     * یکتا بودن مقدار در دیتابیس
     * 
     * @param string $model
     * @param string|null $column
     * @return $this
     */
    public function unique($model, $column = null, $error = null)
    {
        if (!$column)
            $column = Text::snake($this->name);

        $this->_value_checkers[] = [ 'unique', $model, $column, $error ];
        return $this;
    }
    protected function _apply_unique(&$value, $model, $column, $error)
    {
        if ($model::query()->where($column, $value)->exists())
            $this->filterErrorThrow('filter.unique', $error, "این مقدار قبلا وجود داشته است", [ 'name' => $this->name, 'column' => $column ]);
    }

    /**
     * وجود داشتن دیتا در دیتابیس
     * 
     * @param string $model
     * @param string|null $column
     * @return $this
     */
    public function exists($model, $column = null, $error = null)
    {
        if (!$column)
            $column = Text::snake($this->name);

        $this->_value_checkers[] = [ 'exists', $model, $column, $error ];
        return $this;
    }
    protected function _apply_exists(&$value, $model, $column, $error)
    {
        if (!$model::query()->where($column, $value)->exists())
            $this->filterErrorThrow('filter.exists', $error, "این مقدار وجود ندارد", [ 'name' => $this->name, 'column' => $column ]);
    }


    /**
     * فیلتر بررسی ریجکس
     * 
     * می توانید فرمت متن ارسالی کاربر را با شخصی سازی فیلتر کنید
     * 
     * `$input->integer()->min(1000)->regexCheck('/000$/', 'باید مضربی از 1000 باشد');`
     * 
     * `$input->text()->regexCheck('/^\//', 'برای ایجاد کامند، باید ابتدای متن خود / بگذارید');`
     * 
     * @param mixed $pattern
     * @param mixed $filterError
     * @return $this
     */
    public function regexCheck($pattern, $filterError = null)
    {
        $this->_value_checkers[] = [ 'regex', 'check', $pattern, 0, $filterError ];
        return $this;
    }

    /**
     * فیلتر مچ ریجکس
     * 
     * می توانید فرمت متن ارسالی کاربر را شخصی سازی کنید
     * 
     * پترنی را مشخص می کنید تا بصورت ریجکس اطلاعات آن استخراج شود و خروجی ریجکس به عنوان خروجی نهایی تنظیم کند. می توانید بخش خاصی از متن را برای خود جدا کنید
     * 
     * ===============================
     * 
     * `$input->regexMatch('/\d+/', 0, 'عددی یافت نشد')->filled(function() use($input) { replyText("عدد یافت شد: " . $input->value()); }); // "It's test 12 number" => 12`
     * 
     * ===============================
     * 
     * `$input->regexMatch('/(\d+):(\d+)/', NULL, 'ساعتی یافت نشد')->filled(function() use($input) { replyText("ساعتی یافت شد: ساعت " . $input->value()[1] . " و " . $input->value()[2] . " دقیقه"); }); // "It's 12:30 Clock" => ['12:30', '12', '30']`
     * 
     * ===============================
     * 
     * `$input->regexMatch('/(\d+)\/(\d+)\/(\d+)/', 2, 'تاریخی یافت نشد')->filled(function() use($input) { replyText("ماه تاریخ شما: " . $input->value()); }); // "Demo 1401/10/29 TEXT" => 10`
     * 
     * @param mixed $pattern
     * @param int|null $index ایندکس انتخابی - اختیاری
     * @param mixed $filterError
     * @return $this
     */
    public function regexMatch($pattern, $index = null, $filterError = null)
    {
        $this->_value_checkers[] = [ 'regex', 'match', $pattern, $index, $filterError ];
        return $this;
    }

    /**
     * فیلتر ریپلیس ریجکس
     * 
     * می توانید مقدار هایی از ورودی را تغییر دهید
     * 
     * `$input->regexReplace('/[A-Z]+/', function($match) { return strtolower($match[0]); })`
     * 
     * @param mixed $pattern
     * @param string|array|Closure $replacement
     * @return $this
     */
    public function regexReplace($pattern, $replacement)
    {
        $this->_value_checkers[] = [ 'regex', 'replace', $pattern, $replacement, null ];
        return $this;
    }

    /**
     * فیلتر مچ ریجکس ها
     * 
     * می توانید فرمت متن ارسالی کاربر را شخصی سازی کنید
     * 
     * پترنی را مشخص می کنید تا بصورت ریجکس اطلاعات آن استخراج شود و خروجی ریجکس به عنوان خروجی نهایی تنظیم کند. می توانید بخش خاصی از متن را برای خود جدا کنید
     * 
     * ===============================
     * 
     * `$input->integer()->min(0)->regexMatchAll('/\d/', 0)->filled(function() use($input) { replyText("ارقام عدد شما: " . join(', ', $input->value())); }); // "1425" => [1, 4, 2, 5]`
     * 
     * ===============================
     * 
     * `$input->regexMatchAll('/(\d+):(\d+)/')->filled(function() use($input) { $res = $input->value(); replyText("ساعت های شما: $v[1][0]:$v[2][0] و $v[1][1]:$v[2][1] و ..."); }); // "Clocks 1:2 and 6:7 and 11:12" => [ [1,6,11], [2,7,12] ]`
     * 
     * @param mixed $pattern
     * @param int|null $index ایندکس انتخابی - اختیاری
     * @param mixed $filterError
     * @return $this
     */
    public function regexMatchAll($pattern, $index = null, $filterError = null)
    {
        $this->_value_checkers[] = [ 'regex', 'matchall', $pattern, $index, $filterError ];
        return $this;
    }

    protected function _apply_regex(&$value, $type, $pattern, $index, $error)
    {
        if($type == 'check')
        {
            if(!preg_match($pattern, $value))
            {
                $this->filterErrorThrow('filter.match', $error, "این فرمت قابل قبول نیست");
            }
        }
        elseif($type == 'match')
        {
            if(!preg_match($pattern, $value, $value))
            {
                $this->filterErrorThrow('filter.match', $error, "این فرمت قابل قبول نیست");
            }
            if ($index !== null)
                $value = $value[$index];
        }
        elseif($type == 'matchall')
        {
            if(!preg_match_all($pattern, $value, $value))
            {
                $this->filterErrorThrow('filter.match', $error, "این فرمت قابل قبول نیست");
            }
            if ($index !== null)
                $value = $value[$index];
        }
        elseif($type == 'replace')
        {
            $replacement = $index;
            if($replacement instanceof Closure)
                $value = preg_replace_callback($pattern, $replacement, $value);
            else
                $value = preg_replace($pattern, $replacement, $value);
        }
    }

    #endregion


    #region Final value

    /**
     * نوع
     * 
     * @var string
     */
    public $type = 'text';

    /**
     * خطای نوع
     *
     * @var mixed
     */
    public $type_error = null;

    /**
     * دیتای نوع
     * 
     * @var mixed
     */
    public $type_data = null;

    /**
     * تنظیم نوع: متن
     * 
     * پ.ن: تنها یک مدل نوع را می توان تنظیم کرد! برای فیلتر کردن از توابع دیگری استفاده کنید
     * 
     * @param string|Closure|mixed $error
     * @return $this
     */
    public function text($error = null)
    {
        $this->type = 'text';
        $this->type_error = $error;
        return $this;
    }

    /**
     * تنظیم نوع: متن تک خطی
     * 
     * پ.ن: تنها یک مدل نوع را می توان تنظیم کرد! برای فیلتر کردن از توابع دیگری استفاده کنید
     * 
     * @param string|Closure|mixed $error
     * @return $this
     */
    public function textSingleLine($error = null)
    {
        $this->type = 'text_singleline';
        $this->type_error = $error;
        return $this;
    }

    /**
     * تنظیم نوع: عدد صحیح مثبت
     * 
     * پ.ن: تنها یک مدل نوع را می توان تنظیم کرد! برای فیلتر کردن از توابع دیگری استفاده کنید
     * 
     * @param string|Closure|mixed $error
     * @return $this
     */
    public function unsignedInteger($error = null)
    {
        $this->type = 'int_us';
        $this->type_error = $error;
        return $this;
    }

    /**
     * تنظیم نوع: عدد صحیح
     * 
     * پ.ن: تنها یک مدل نوع را می توان تنظیم کرد! برای فیلتر کردن از توابع دیگری استفاده کنید
     * 
     * @param string|Closure|mixed $error
     * @return $this
     */
    public function integer($error = null)
    {
        $this->type = 'int';
        $this->type_error = $error;
        return $this;
    }

    /**
     * تنظیم نوع: عدد اعشاری مثبت
     * 
     * پ.ن: تنها یک مدل نوع را می توان تنظیم کرد! برای فیلتر کردن از توابع دیگری استفاده کنید
     * 
     * @param string|Closure|mixed $error
     * @return $this
     */
    public function unsignedFloat($error = null)
    {
        $this->type = 'float_us';
        $this->type_error = $error;
        return $this;
    }

    /**
     * تنظیم نوع: عدد اعشاری
     * 
     * پ.ن: تنها یک مدل نوع را می توان تنظیم کرد! برای فیلتر کردن از توابع دیگری استفاده کنید
     * 
     * @param string|Closure|mixed $error
     * @return $this
     */
    public function float($error = null)
    {
        $this->type = 'float';
        $this->type_error = $error;
        return $this;
    }

    /**
     * تنظیم نوع: عدد
     * 
     * پ.ن: تنها یک مدل نوع را می توان تنظیم کرد! برای فیلتر کردن از توابع دیگری استفاده کنید
     * 
     * @param string|Closure|mixed $error
     * @return $this
     */
    public function number($error = null)
    {
        $this->type = 'float';
        $this->type_error = $error;
        return $this;
    }

    /**
     * تنظیم نوع: رسانه
     * 
     * پ.ن: تنها یک مدل نوع را می توان تنظیم کرد! برای فیلتر کردن از توابع دیگری استفاده کنید
     * 
     * @param string|Closure|mixed $error
     * @return $this
     */
    public function media($error = null)
    {
        $this->type = 'media';
        $this->type_error = $error;
        return $this;
    }

    /**
     * تنظیم نوع: تصویر
     * 
     * پ.ن: تنها یک مدل نوع را می توان تنظیم کرد! برای فیلتر کردن از توابع دیگری استفاده کنید
     * 
     * @param string|Closure|mixed $error
     * @return $this
     */
    public function photo($error = null)
    {
        $this->type = 'photo';
        $this->type_error = $error;
        return $this;
    }

    /**
     * تنظیم نوع: مخاطب
     * 
     * پ.ن: تنها یک مدل نوع را می توان تنظیم کرد! برای فیلتر کردن از توابع دیگری استفاده کنید
     * 
     * @param string|Closure|mixed $error
     * @return $this
     */
    public function contact($error = null)
    {
        $this->type = 'contact';
        $this->type_error = $error;
        return $this;
    }

    /**
     * تنظیم نوع: مخاطب - تنها مخاطب خود کاربر
     * 
     * از این گزینه برای دریافت شماره کاربر از طریق دکمه اشتراک گذاری استفاده کنید
     * 
     * پ.ن: تنها یک مدل نوع را می توان تنظیم کرد! برای فیلتر کردن از توابع دیگری استفاده کنید
     * 
     * @param string|Closure|mixed $error
     * @return $this
     */
    public function contactSelf($error = null)
    {
        $this->type = 'contact-self';
        $this->type_error = $error;
        return $this;
    }

    /**
     * تنظیم نوع: موقعیت
     * 
     * پ.ن: تنها یک مدل نوع را می توان تنظیم کرد! برای فیلتر کردن از توابع دیگری استفاده کنید
     * 
     * @param string|Closure|mixed $error
     * @return $this
     */
    public function location($error = null)
    {
        $this->type = 'location';
        $this->type_error = $error;
        return $this;
    }

    /**
     * تنظیم نوع: دلخواه از پیام
     * 
     * Example: photo, video, anim, text, ...
     * 
     * داده ای که ذخیره می شود طیق نوع پیام تعیین می شود
     * 
     * پ.ن: تنها یک مدل نوع را می توان تنظیم کرد! برای فیلتر کردن از توابع دیگری استفاده کنید
     * 
     * @param string $name
     * @param string|Closure|mixed $error
     * @return $this
     */
    public function msgTypeOf($name, $error = null)
    {
        $this->type = "msgTypeOf";
        $this->type_data = $name;
        $this->type_error = $error;
        return $this;
    }

    /**
     * تنظیم نوع: پیغام
     * 
     * پ.ن: تنها یک مدل نوع را می توان تنظیم کرد! برای فیلتر کردن از توابع دیگری استفاده کنید
     * 
     * @param string|Closure|mixed $error
     * @return $this
     */
    public function msg($error = null)
    {
        $this->type = 'msg';
        $this->type_error = $error;
        return $this;
    }

    /**
     * تنظیم نوع: آیدی پیام ارسالی
     * 
     * پ.ن: تنها یک مدل نوع را می توان تنظیم کرد! برای فیلتر کردن از توابع دیگری استفاده کنید
     * 
     * @param string|Closure|mixed $error
     * @return $this
     */
    public function msgid($error = null)
    {
        $this->type = 'msgid';
        $this->type_error = $error;
        return $this;
    }

    /**
     * تنظیم نوع: پارامتر های پیام
     * 
     * پ.ن: تنها یک مدل نوع را می توان تنظیم کرد! برای فیلتر کردن از توابع دیگری استفاده کنید
     * 
     * @param string|Closure|mixed $error
     * @return $this
     */
    public function msgArgs($error = null)
    {
        $this->type = 'msgArgs';
        $this->type_error = $error;
        return $this;
    }

    #endregion


    /**
     * اعمال فیلتر ها بر روی آپدیت
     * 
     * @param Upd $upd
     * @throws FilterError 
     * @return mixed
     */
    public function applyFilters(Upd $upd, $checkFilters)
    {
        if(!$checkFilters)
        {
            $this->matchUpdateChecking($upd);
        }
        $value = $this->matchType($upd);
        if(!$checkFilters)
        {
            $this->matchFilters($value);
        }

        return $value;
    }

    /**
     * آپدیت را بررسی و فیلتر می کند
     *
     * @param Upd $upd
     * @return void
     */
    protected function matchUpdateChecking(Upd $upd)
    {
        foreach($this->_checks as $check)
        {
            $selector = $check[1];

            if($selector instanceof Closure)
            {
                if($selector())
                    continue;
            }
            else
            {
                // Select
                $select = $upd;
                foreach(explode('.', $selector) as $sel)
                {
                    $select = $select->$sel ?? null;
                    if($select === null)
                    {
                        break;
                    }
                }

                // Check equals to
                if(count($check) >= 3)
                {
                    if($select = $check[2])
                        continue;
                }
                // Check boolean
                else
                {
                    if($select)
                        continue;
                }
            }

            // Error
            $error = $check[0];
            if(is_array($error))
            {
                $this->filterErrorThrow($error[0], null, null, $error);
                // $error = lang($error[0], $error);
            }
            $this->filterErrorThrow(null, $error, null);
        }
    }

    /**
     * گرفتن مقدار بر اساس نوع اینپوت
     * 
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
                    $this->filterErrorThrow('invalid.text', $this->type_error, "تنها پیغام متنی قابل قبول است");
                return optional($upd->msg)->text;

            case 'text_singleline':
                if (optional($upd->msg)->type != 'text')
                    $this->filterErrorThrow('invalid.text', $this->type_error, "تنها پیغام متنی قابل قبول است");
                $text = $upd->msg->text;
                if (strpos($text, "\n"))
                    $this->filterErrorThrow('invalid.single_line', $this->type_error, "متن شما باید تک خطی باشد");
                return $text;

            case 'int':
                if (optional($upd->msg)->type != 'text')
                    $this->filterErrorThrow('invalid.text', $this->type_error, "تنها پیغام متنی قابل قبول است");
                $text = optional($upd->msg)->text;
                if ($this->supportFa)
                    $text = tr_num($text);
                if (!is_numeric($text) || strpos($text, '.') !== false)
                    $this->filterErrorThrow('invalid.int', $this->type_error, "تنها عدد غیر اعشاری قابل قبول است");
                return intval($text);

            case 'int_us':
                if (optional($upd->msg)->type != 'text')
                    $this->filterErrorThrow('invalid.text', $this->type_error, "تنها پیغام متنی قابل قبول است");
                $text = optional($upd->msg)->text;
                if ($this->supportFa)
                    $text = tr_num($text);
                if (!is_numeric($text) || strpos($text, '.') !== false)
                    $this->filterErrorThrow('invalid.int', $this->type_error, "تنها عدد غیر اعشاری قابل قبول است");
                $int = intval($text);
                if ($int < 0)
                    $this->filterErrorThrow('invalid.unsigned', $this->type_error, "تنها عدد مثبت قابل قبول است");
                return $int;

            case 'float':
                if (optional($upd->msg)->type != 'text')
                    $this->filterErrorThrow('invalid.text', $this->type_error, "تنها پیغام متنی قابل قبول است");
                $text = optional($upd->msg)->text;
                if ($this->supportFa)
                    $text = tr_num($text);
                if (!is_numeric($text))
                    $this->filterErrorThrow('invalid.number', $this->type_error, "تنها عدد قابل قبول است");
                return floatval($text);

            case 'float_us':
                if (optional($upd->msg)->type != 'text')
                    $this->filterErrorThrow('invalid.text', $this->type_error, "تنها پیغام متنی قابل قبول است");
                $text = optional($upd->msg)->text;
                if ($this->supportFa)
                    $text = tr_num($text);
                if (!is_numeric($text))
                    $this->filterErrorThrow('invalid.number', $this->type_error, "تنها عدد قابل قبول است");
                $float = floatval($text);
                if ($float < 0)
                    $this->filterErrorThrow('invalid.unsigned', $this->type_error, "تنها عدد مثبت قابل قبول است");
                return $float;

            case 'msg':
                if (!$upd->msg)
                    $this->filterErrorThrow('invalid.msg', $this->type_error, "تنها پیام قابل قبول است");
                return $upd->msg;

            case 'msgTypeOf':
                $type = $this->type_data;
                if (!$upd->msg)
                    $this->filterErrorThrow('invalid.msg', $this->type_error, "تنها پیام قابل قبول است");
                if ($upd->msg->type != $type)
                {
                    $this->filterErrorThrow('invalid.msg_type', $this->type_error, "این نوع پیام پشتیبانی نمی شود");
                }
                return $upd->msg->$type;

            case 'msgid':
                if (!$upd->msg)
                    $this->filterErrorThrow('invalid.msg', $this->type_error, "تنها پیام قابل قبول است");
                return $upd->msg->id;

            case 'media':
                if (!$upd->msg)
                    $this->filterErrorThrow('invalid.msg', $this->type_error, "تنها پیام قابل قبول است");
                $media = $upd->msg->media;
                if (!$media)
                    $this->filterErrorThrow('invalid.media', $this->type_error, "تنها پیام رسانه ای قابل قبول است");
                return $media;

            case 'photo':
                if (!$upd->msg)
                    $this->filterErrorThrow('invalid.msg', $this->type_error, "تنها پیام قابل قبول است");
                $media = $upd->msg->photo;
                if (!$media)
                    $this->filterErrorThrow('invalid.photo', $this->type_error, "تنها پیام تصویری قابل قبول است");
                return end($media);

            case 'contact':
                if (!$upd->msg)
                    $this->filterErrorThrow('invalid.msg', $this->type_error, "تنها پیام قابل قبول است");
                $contact = $upd->msg->contact;
                if (!$contact)
                    $this->filterErrorThrow('invalid.contact', $this->type_error, "تنها مخاطب قابل قبول است");
                return $contact;

            case 'contact-self':
                if (!$upd->msg)
                    $this->filterErrorThrow('invalid.msg', $this->type_error, "تنها پیام قابل قبول است");
                $contact = $upd->msg->contact;
                if (!$contact)
                    $this->filterErrorThrow('invalid.contact', $this->type_error, "تنها مخاطب قابل قبول است");
                if ($contact->userId != $upd->msg->from->id)
                    $this->filterErrorThrow('invalid.contact_self', $this->type_error, "نمی توانید مخاطب شخص دیگری را ارسال کنید");
                return $contact;

            case 'location':
                if (!$upd->msg)
                    $this->filterErrorThrow('invalid.msg', $this->type_error, "تنها پیام قابل قبول است");
                $location = $upd->msg->location;
                if (!$location)
                    $this->filterErrorThrow('invalid.location', $this->type_error, "تنها موقعیت مکانی قابل قبول است");
                return $location;

            case 'msgArgs':
                if (!$upd->msg)
                    $this->filterErrorThrow('invalid.msg', $this->type_error, "تنها پیام قابل قبول است");
                $args = $upd->msg->createArgs();
                if (!$args)
                    $this->filterErrorThrow('invalid.msg_type', $this->type_error, "این نوع پیام پشتیبانی نمی شود");
                return $args;

        }

        return optional($upd->msg)->text;
    }

    /**
     * بررسی فیلتر ها بر روی مقدار اینپوت
     * 
     * @param mixed &$value
     * @throws FilterError 
     * @return void
     */
    protected function matchFilters(&$value)
    {
        foreach($this->_value_checkers as $check)
        {
            $name = $check[0];
            unset($check[0]);
            $method = '_apply_' . $name;
            $this->$method($value, ...$check);
        }
    }


    public $supportFa = false;
    /**
     * پشتیبانی از اعداد فارسی
     * 
     * @return $this
     */
    public function supportFaNumber()
    {
        $this->supportFa = true;
        return $this;
    }

    /**
     * ترو کردن خطای فیلتر
     *
     * @param string $name
     * @param string|Closure|mixed $customError
     * @param string|Closure|mixed $defaultError
     * @param array $args
     * @throws FilterError
     * @return never
     */
    public function filterErrorThrow($name, $customError, $defaultError, array $args = [])
    {
        $args['error'] = $name;
        if($customError)
        {
            $customError = Lang::convertFromText($customError, Lang::getLang(), $args);
            throw new FilterError($customError, $name, $args);
        }

        if($error = tryLang($name, $args))
        {
            throw new FilterError($error, $name, $args);
        }

        $defaultError = Lang::convertFromText($defaultError, Lang::getLang(), $args);
        throw new FilterError($defaultError, $name, $args);
    }
    
}

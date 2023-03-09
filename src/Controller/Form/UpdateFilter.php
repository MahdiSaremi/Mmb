<?php
#auto-update
namespace Mmb\Controller\Form;

use Mmb\Update\Upd;
use Mmb\Update\User\UserInfo;

trait UpdateFilter
{

    
    public $min = false;
    /**
     * تنظیم حداقل طول/عدد مجاز
     * 
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
     * 
     * @param int $len
     * @return $this
     */
    public function max($len)
    {
        $this->max = $len;
        return $this;
    }
    
    /**
     * تنظیم حداقل و حداکثر طول/عدد مجاز
     * 
     * @param mixed $min
     * @param mixed $max
     * @return UpdateFilter
     */
    public function between($min, $max)
    {
        $this->min = $min;
        $this->max = $max;
        return $this;
    }

    /**
     * نوع
     * 
     * @var string
     */
    public $type = 'text';

    /**
     * دیتای نوع
     * 
     * @var mixed
     */
    public $type_data = null;

    /**
     * تنظیم نوع متن
     * 
     * @return $this
     */
    public function text()
    {
        $this->type = 'text';
        return $this;
    }

    /**
     * تنظیم نوع متن تک خطی
     * 
     * @return $this
     */
    public function textSingleLine()
    {
        $this->type = 'text_singleline';
        return $this;
    }

    /**
     * تنظیم نوع عدد صحیح مثبت
     * 
     * @return $this
     */
    public function unsignedInteger()
    {
        $this->type = 'int_us';
        return $this;
    }

    /**
     * تنظیم نوع عدد صحیح
     * 
     * @return $this
     */
    public function integer()
    {
        $this->type = 'int';
        return $this;
    }

    /**
     * تنظیم نوع عدد اعشاری مثبت
     * 
     * @return $this
     */
    public function unsignedFloat()
    {
        $this->type = 'float_us';
        return $this;
    }

    /**
     * تنظیم نوع عدد اعشاری
     * 
     * @return $this
     */
    public function float()
    {
        $this->type = 'float';
        return $this;
    }

    /**
     * تنظیم نوع عدد
     * 
     * @return $this
     */
    public function number()
    {
        $this->type = 'float';
        return $this;
    }

    /**
     * تنظیم نوع رسانه
     * 
     * @return $this
     */
    public function media()
    {
        $this->type = 'media';
        return $this;
    }

    /**
     * تنظیم نوع تصویر
     * 
     * @return $this
     */
    public function photo()
    {
        $this->type = 'photo';
        return $this;
    }

    /**
     * تنظیم نوع مخاطب
     * 
     * @return $this
     */
    public function contact()
    {
        $this->type = 'contact';
        return $this;
    }

    /**
     * تنظیم نوع مخاطب - تنها مخاطب خود کاربر
     * 
     * از این گزینه برای دریافت شماره کاربر از طریق دکمه اشتراک گذاری استفاده کنید
     * 
     * @return $this
     */
    public function contactSelf()
    {
        $this->type = 'contact-self';
        return $this;
    }

    /**
     * تنظیم نوع موقعیت
     * 
     * @return $this
     */
    public function location()
    {
        $this->type = 'location';
        return $this;
    }

    /**
     * تنظیم نوع دلخواه از پیام
     * 
     * Example: photo, video, anim, text, ...
     * 
     * داده ای که ذخیره می شود طیق نوع پیام تعیین می شود
     * 
     * @return $this
     */
    public function msgTypeOf($name, $filterError = null)
    {
        $this->type = "msgTypeOf";
        $this->type_data = [ $name, $filterError ];
        return $this;
    }

    /**
     * تنظیم نوع پیغام
     * 
     * @return $this
     */
    public function msg()
    {
        $this->type = 'msg';
        return $this;
    }

    /**
     * تنظیم نوع آیدی پیام ارسالی
     * 
     * @return $this
     */
    public function msgid()
    {
        $this->type = 'msgid';
        return $this;
    }

    /**
     * تنظیم نوع پارامتر های پیام
     * 
     * @return $this
     */
    public function msgArgs()
    {
        $this->type = 'msgArgs';
        return $this;
    }



    /**
     * اعمال فیلتر ها بر روی آپدیت
     * 
     * @param Upd $upd
     * @throws FilterError 
     * @return mixed
     */
    public function applyFilters(Upd $upd)
    {
        $value = $this->matchType($upd);
        $this->matchFilters($value);
        return $value;
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

            case 'msgTypeOf':
                [$type, $filterError] = $this->type_data;
                if (!$upd->msg)
                    throw new FilterError(lang('invalid.msg') ?: "تنها پیام قابل قبول است");
                if ($upd->msg->type != $type)
                {
                    if ($filterError)
                        throw new FilterError($filterError);
                    else
                        throw new FilterError(lang('invalid.msg_type') ?: "این نوع پیام پشتیبانی نمی شود");
                }
                return $upd->msg->$type;

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

            case 'contact':
                if (!$upd->msg)
                    throw new FilterError(lang('invalid.msg') ?: "تنها پیام قابل قبول است");
                $contact = $upd->msg->contact;
                if (!$contact)
                    throw new FilterError(lang('invalid.contact') ?: "تنها مخاطب قابل قبول است");
                return $contact;

            case 'contact-self':
                if (!$upd->msg)
                    throw new FilterError(lang('invalid.msg') ?: "تنها پیام قابل قبول است");
                $contact = $upd->msg->contact;
                if (!$contact)
                    throw new FilterError(lang('invalid.contact') ?: "تنها مخاطب قابل قبول است");
                if ($contact->userID != $upd->msg->from->id)
                    throw new FilterError(lang('invalid.contact_self') ?: "نمی توانید مخاطب شخص دیگری را ارسال کنید");
                return $contact;

            case 'location':
                if (!$upd->msg)
                    throw new FilterError(lang('invalid.msg') ?: "تنها پیام قابل قبول است");
                $location = $upd->msg->location;
                if (!$location)
                    throw new FilterError(lang('invalid.location') ?: "تنها موقعیت مکانی قابل قبول است");
                return $location;

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
     * 
     * @param mixed &$value
     * @throws FilterError 
     * @return void
     */
    protected function matchFilters(&$value)
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
                if ($value < $this->min)
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
                if ($value > $this->max)
                    throw new FilterError(lang('filter.max_number', [ 'max' => $this->max ]) ?: "عدد شما باید حداکثر {$this->max} باشد");
            }
        }

        if($this->matchRegex)
        {
            foreach($this->matchRegex as $match)
            {
                if($match[0] == 'check')
                {
                    if(!preg_match($match[1], $value))
                    {
                        if ($match[2])
                            throw new FilterError($match[2]);
                        else
                            throw new FilterError(lang('filter.match') ?: "این فرمت قابل قبول نیست");
                    }
                }
                elseif($match[0] == 'match')
                {
                    if(!preg_match($match[1], $value, $value))
                    {
                        if ($match[3])
                            throw new FilterError($match[3]);
                        else
                            throw new FilterError(lang('filter.match') ?: "این فرمت قابل قبول نیست");
                    }
                    if ($match[2] !== null)
                        $value = $value[$match[2]];
                }
                elseif($match[0] == 'matchall')
                {
                    if(!preg_match_all($match[1], $value, $value))
                    {
                        if ($match[3])
                            throw new FilterError($match[3]);
                        else
                            throw new FilterError(lang('filter.match') ?: "این فرمت قابل قبول نیست");
                    }
                    if ($match[2] !== null)
                        $value = $value[$match[2]];
                }
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
    

    public $matchRegex = [];

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
        $this->matchRegex[] = ['check', $pattern, $filterError];
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
        $this->matchRegex[] = ['match', $pattern, $index, $filterError];
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
        $this->matchRegex[] = ['matchall', $pattern, $index, $filterError];
        return $this;
    }

}

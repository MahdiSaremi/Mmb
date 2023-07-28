<?php

namespace Mmb\Db; #auto
use Mmb\Listeners\HasCustomMethod;

class SingleCol {

    use Key\On;
    use HasCustomMethod;

    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }


    /**
     * نام ستون
     *
     * @var string
     */
    public $name = '';

    /**
     * تنظیم نام ستون
     *
     * @param string $name
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }


    /**
     * نوع
     *
     * @var string
     */
    public $type = '';

    /**
     * تنظیم نوع ستون
     *
     * @param string $type
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * طول
     *
     * @var int|null
     */
    public $len = null;

    /**
     * طول
     *
     * @param int $len
     * @return $this
     */
    public function len($len)
    {
        $this->len = $len;
        return $this;
    }


    /**
     * می تواند نال باشد
     *
     * @var boolean
     */
    public $nullable = false;

    /**
     * نمی تواند نال باشد
     *
     * @return $this
     */
    public function noNull()
    {
        $this->nullable = false;
        return $this;
    }

    /**
     * می تواند نال باشد
     *
     * @return $this
     */
    public function nullable()
    {
        $this->nullable = true;
        return $this;
    }


    /**
     * مقدار پیشفرض
     *
     * @var mixed
     */
    public $default;
    /**
     * پیشفرض بصورت کد است
     *
     * @var boolean
     */
    public $defaultRaw = false;

    /**
     * تنظیم پیشفرض
     *
     * @param string $default
     * @return $this
     */
    public function default($default)
    {
        $this->default = $default;
        $this->defaultRaw = false;
        return $this;
    }

    /**
     * تنظیم پیشفرض بصورت کد
     *
     * @param string $default
     * @return $this
     */
    public function defaultRaw($default)
    {
        $this->default = $default;
        $this->defaultRaw = true;
        return $this;
    }


    /**
     * خودکار پر شدن
     *
     * @var boolean
     */
    public $autoIncrement = false;
    /**
     * خودکار پر شدن
     * 
     * این ستون کلید اصلی نیز خواهد شد
     *
     * @return $this
     */
    public function autoIncrement()
    {
        $this->autoIncrement = true;
        $this->primaryKey = true;
        return $this;
    }


    /**
     * کلید اصلی
     *
     * @var boolean
     */
    public $primaryKey = false;
    /**
     * کلید اصلی
     *
     * @return $this
     */
    public function primaryKey()
    {
        $this->primaryKey = true;
        return $this;
    }

    /**
     * عدد طبیعی بودن
     *
     * @var boolean
     */
    public $unsigned = false;
    /**
     * عدد طبیعی بودن
     *
     * @return $this
     */
    public function unsigned()
    {
        $this->unsigned = true;
        return $this;
    }

    /**
     * یکتا بودن
     *
     * @var boolean
     */
    public $unique = false;
    /**
     * یکتا بودن
     *
     * @return $this
     */
    public function unique()
    {
        $this->unique = true;
        return $this;
    }

    /**
     * ساخته شدن بعد از ستون
     *
     * @var string
     */
    public $after = null;
    /**
     * ساخته شدن بعد از ستون
     *
     * @param string $col
     * @return $this
     */
    public function after($col)
    {
        $this->after = $col;
        return $this;
    }

    /**
     * ساخته شدن در اولین ستون
     *
     * @var boolean
     */
    public $first = null;
    /**
     * ساخته شدن در اولین ستون
     *
     * @return $this
     */
    public function first()
    {
        $this->first = true;
        return $this;
    }

    /**
     * رابطه
     *
     * @var Key\Foreign
     */
    public $foreign_key;

    /**
     * تنظیم ارتباط با جدول دیگر
     *
     * @param string $table_name نام جدول
     * @param string $column_name نام ستون
     * @param string|null $constraint
     * @return Key\Foreign
     */
    public function foreignKey($table_name, $column_name = 'id', $constraint = null)
    {
        // if($constraint === null)
        //     $constraint = $this->name . '__fk';
            
        return ($this->foreign_key = new Key\Foreign($table_name, $column_name, $constraint));
    }

    /**
     * تنظیم ارتباط با جدول دیگر با استفاده از کلاس مدل
     *
     * @param string $model کلاس مورد نظر
     * @param string $column_name نام ستون
     * @param string|null $constraint
     * @return Key\Foreign
     */
    public function foreign($model, $column_name = null, $constraint = null)
    {
        $table_name = $model::getTableName();
        if($column_name === null)
            $column_name = $model::getPrimaryKey();
        return $this->foreignKey($table_name, $column_name, $constraint);
    }


    private $modify_in = [];
    /**
     * تنظیم مدیریت کننده وارد شدن این مقدار از دیتابیس
     * 
     * `$table->text('json')->modifyIn('json_decode')->modifyOut('json_encode');`
     * 
     * `$table->text('number')->modifyIn('floatval');`
     * 
     * `$table->text('custom')->modifyIn(function($custom, $model) { return unserialize($custom); }, true);`
     *
     * @param \Closure|string|array $callablle
     * @param boolean $passModel اگر ترو شود، شی مدل نیز به تابع داده می شودs
     * @return $this
     */
    public function modifyIn($callablle, $passModel = false)
    {
        $this->modify_in[] = [$callablle, $passModel];

        return $this;
    }


    private $modify_out = [];
    /**
     * تنظیم مدیریت کننده وارد شدن این مقدار به دیتابیس
     * 
     * `$table->text('json')->modifyIn('json_decode')->modifyOut('json_encode');`
     *
     * `$table->text('custom')->modifyOut(function($custom) { return serialize($custom); });`
     * 
     * @param \Closure|string|array $callablle
     * @param boolean $passModel اگر ترو شود، شی مدل نیز به تابع داده می شود
     * @return $this
     */
    public function modifyOut($callablle, $passModel = false)
    {
        $this->modify_out[] = [$callablle, $passModel];

        return $this;
    }

    /**
     * تبدیل مقدار با توجه به مدیریت کننده ها
     *
     * @param mixed $data
     * @return mixed
     */
    public function dataIn($data, $model)
    {
        foreach($this->modify_in as $in)
        {
            $callback = $in[0];
            $passModel = $in[1] ?? false;
            if($passModel)
                $data = $callback($data, $model);
            else
                $data = $callback($data);
        }

        return $data;
    }

    /**
     * تبدیل مقدار با توجه به مدیریت کننده ها
     *
     * @param mixed $data
     * @return mixed
     */
    public function dataOut($data, $model)
    {
        foreach($this->modify_out as $out)
        {
            $callback = $out[0];
            $passModel = $out[1] ?? false;
            if($passModel)
                $data = $callback($data, $model);
            else
                $data = $callback($data);
        }

        return $data;
    }

    /**
     * آیا مدیریت کننده خروجی دارد
     *
     * @return boolean
     */
    public function hasOutModifier()
    {
        return $this->modify_out ? true : false;
    }

    /**
     * همیشه ذخیره شود
     *
     * @var boolean
     */
    public $always_save = false;

    /**
     * تنظیم میکنید که این مقدار همیشه در متد سیو ذخیره شود
     *
     * @return $this
     */
    public function alwaysSave()
    {
        $this->always_save = true;
        return $this;
    }

}

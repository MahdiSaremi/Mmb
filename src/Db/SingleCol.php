<?php

namespace Mmb\Db; #auto

class SingleCol {

    use Key\On;

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
    public function name($name) {
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
    public function type($type) {
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
    public function len($len) {
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
    public function noNull() {
        $this->nullable = false;
        return $this;
    }

    /**
     * می تواند نال باشد
     *
     * @return void
     */
    public function nullable() {
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
    public function default($default) {
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
    public function defaultRaw($default) {
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
    public function autoIncrement() {
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
    public function primaryKey() {
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
    public function unsigned() {
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
    public function unique() {
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
    public function after($col) {
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
    public function first() {
        $this->first = true;
        return $this;
    }

}

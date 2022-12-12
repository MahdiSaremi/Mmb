<?php

namespace Mmb\Guard; #auto

class Role
{

    private static $roles = [];
    private static $default = '';

    public static function setRoles(array $roles)
    {
        self::$roles = $roles;
    }

    public static function setDefault($name)
    {
        self::$default = $name;
    }

    public static function modifyIn(&$role)
    {
        $name = strstr($role, ":", true) ?: $role;
        $result = static::role($name);

        if($attrs = strstr($role, ":"))
        {
            $attrs = substr($attrs, 1);
            if($attrs = @json_decode($attrs, true))
            {
                $result->setAttrs($attrs);
            }
        }

        $role = $result;
    }

    public static function modifyOut(&$role)
    {
        if($role instanceof Role)
        {
            $result = $role->name;

            if($role->setValues)
            {
                $result .= ":" . json_encode($role->setValues);
            }

            $role = $result;
        }
    }

    /**
     * گرفتن یک رول
     *
     * @param string $name
     * @return static
     */
    public static function role($name)
    {
        return new static($name);
    }

    /**
     * گرفتن رول پیشفرض
     *
     * @return static
     */
    public static function roleDefault()
    {
        return new static(static::$default);
    }


    public $name;
    private $setValues = [];
    public function __construct($name)
    {
        if(!isset(self::$roles[$name]))
        {
            $name = static::$default;
        }
        $this->name = $name;
    }

    /**
     * تنظیم مقدار
     *
     * @param string $attribute
     * @param bool $value
     * @return void
     */
    public function set($attribute, $value)
    {
        $this->setValues[$attribute] = $value;
    }

    /**
     * تنظیم مقدار ها
     *
     * @param array $values
     * @return void
     */
    public function setAttrs(array $values)
    {
        $this->setValues = $values;
    }

    /**
     * گرفتن مقدار
     *
     * @param string $attribute
     * @return bool
     */
    public function get($attribute)
    {
        return $this->setValues[$attribute] ?? self::$roles[$this->name][$attribute] ?? false;
    }

    /**
     * حذف مقدار
     *
     * @param string $attribute
     * @return void
     */
    public function unset($attribute)
    {
        unset($this->setValues[$attribute]);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __unset($name)
    {
        $this->unset($name);
    }
    
}

<?php

namespace Mmb\Guard; #auto

use Mmb\Tools\ATool;

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
            if($role->advNames)
                $result .= '|' . join('|', $role->advNames);

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

    private static $constants = [];

    /**
     * تنظیم نقش های ثابت کاربران
     * 
     * @param int|array $id
     * @param string $role
     * @return void
     */
    public static function constant($id, $role = null)
    {
        
        if(is_array($id))
        {
            foreach($id as $id0 => $role)
            {
                self::$constants[$id0] = $role;
            }
            return;
        }

        self::$constants[$id] = $role;

    }

    /**
     * بررسی وجود نقش ثابت کاربر
     * 
     * @param int $id
     * @return bool
     */
    public static function issetConstant($id)
    {
        return isset(self::$constants[$id]);
    }

    /**
     * گرفتن نقش ثابت کاربر
     * 
     * @param int $id
     * @return string|bool
     */
    public static function getConstantOf($id)
    {
        $role = self::$constants[$id] ?? false;
        if($role)
            $role = explode('|', $role)[0];

        return $role;
    }

    /**
     * گرفتن مقدار اصلی نقش ثابت کاربر
     * 
     * @param int $id
     * @return string|bool
     */
    public static function getFullConstantOf($id)
    {
        $role = self::$constants[$id] ?? false;

        return $role;
    }

    /**
     * گرفتن کاربری که این نقش ثابت را دارد
     * 
     * `Role::getConstantFor('developer')`
     * 
     * `Role::getConstantFor('developer|manager|test') // Role=developer AdvRoles=manager,test`
     * 
     * @param string $role
     * @return int|bool
     */
    public static function getConstantFor($role)
    {
        $exp = explode('|', $role);
        $expc = count($exp);
        // Search for role
        if($expc == 1)
        {
            foreach(self::$constants as $id => $rl)
            {
                if($role == $rl)
                    return $id;
                elseif(($pos = strpos($rl, '|')) && substr($rl, 0, $pos) == $role)
                    return $id;
            }
        }
        // Search for role & 
        else
        {
            foreach(self::$constants as $id => $rl)
            {
                $exp2 = explode('|', $rl);
                if($expc <= count($exp2) && $exp2[0] == $exp[0])
                {
                    $ok = true;
                    for($i = 1; $i < $expc; $i++)
                    {
                        if(!array_search($exp[$i], $exp2))
                        {
                            $ok = false;
                            break;
                        }
                    }
                    if($ok)
                        return $id;
                }
            }
        }

        return false;
    }



    public $name;
    public $advNames = [];
    private $setValues = [];
    public function __construct($name)
    {
        $advNames = explode('|', $name);
        $name = $advNames[0];
        ATool::remove($advNames, 0);

        if(!isset(self::$roles[$name]))
        {
            $name = static::$default;
            $advNames = [];
        }
        $this->name = $name;
        $this->advNames = $advNames;
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
     * تنظیم کل مقدار ها
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
        if(($val = $this->setValues[$attribute] ?? null) !== null)
            return $val;

        foreach($this->advNames as $name)
        {
            if(($val = self::$roles[$name][$attribute] ?? null) !== null)
                return $val;
        }
    
        if(($val = self::$roles[$this->name][$attribute] ?? null) !== null)
            return $val;
            
        return false;
    }

    /**
     * افزودن نقش
     *
     * @param string $name
     * @return boolean
     */
    public function addRole($name)
    {
        if($name != $this->name && !in_array($name, $this->advNames))
        {  
            $this->advNames[] = $name;
            return true;
        }

        return false;
    }

    /**
     * حذف نقش
     *
     * @param string $name
     * @return boolean
     */
    public function removeRole($name)
    {
        if(in_array($name, $this->advNames) && $name != $this->name)
        {  
            ATool::remove($this->advNames, array_search($name, $this->advNames));
            return true;
        }

        return false;
    }

    /**
     * بررسی می کند نقشی را دارد یا خیر
     *
     * @param string $name
     * @return boolean
     */
    public function hasRole($name)
    {
        return $name == $this->name || in_array($name, $this->advNames);
    }

    /**
     * بررسی می کند نقش اصلی آن این مقدار است یا خیر
     *
     * @param string $name
     * @return boolean
     */
    public function isRole($name)
    {
        return $name == $this->name;
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

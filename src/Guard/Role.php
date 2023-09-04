<?php

namespace Mmb\Guard; #auto

use Mmb\Db\Db;
use Mmb\Db\Table\Table;
use Mmb\Mapping\Arr;
use Mmb\Mapping\Arrayable;
use Mmb\Tools\ATool;
use Stringable;

class Role implements Arrayable, Stringable
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
     * گرفتن کاربری که این نقش را شامی می شوند
     * 
     * `Role::getConstantFor('developer')`
     * 
     * `Role::getConstantFor('debugger|admin')` // در واقع باید هر دو نقش را دارا باشد
     * 
     * `Role::getConstantFor('debugger|admin', true)` // به ترتیب بررسی می کند و اگر اولین نقش را کسی نداشت، سراغ دومین نقش می رود! این بدین معنیست که اگر دیباگر وجود داشته باشد، صد در صد دیباگر به شما بر می گردد
     * 
     * @param string $role
     * @return int|string|bool
     */
    public static function getConstantFor(string $role, bool $orOperator = false)
    {
        $exp = explode('|', $role);

        if($orOperator)
        {
            foreach($exp as $subRole)
            {
                foreach(self::$constants as $id => $crole)
                {
                    if(static::stringExtends($crole, [$subRole]))
                    {
                        return $id;
                    }
                }
            }
        }
        else
        {
            foreach(self::$constants as $id => $crole)
            {
                if(static::stringExtends($crole, $exp))
                {
                    return $id;
                }
            }
        }

        return false;
    }

    /**
     * گرفتن کاربرانی که این نقش را شامی می شوند
     * 
     * `Role::getConstantsFor('developer')`
     * 
     * `Role::getConstantsFor('debugger|admin')` // در واقع باید هر دو نقش را دارا باشد
     * 
     * `Role::getConstantsFor('debugger|admin', true)` // کافیست یکی از این نقش ها را داشته باشد
     * 
     * @param string $role
     * @return array<int|string>
     */
    public static function getConstantsFor(string $role, bool $orOperator = false)
    {
        $result = [];
        $exp = explode('|', $role);
        
        if($orOperator)
        {
            foreach($exp as $subRole)
            {
                foreach(self::$constants as $id => $crole)
                {
                    if(static::stringExtends($crole, [$subRole]))
                    {
                        return $id;
                    }
                }
            }
        }
        else
        {
            foreach(self::$constants as $id => $crole)
            {
                if(static::stringExtends($crole, $exp))
                {
                    $result[] = $id;
                }
            }
        }
        

        return $result;
    }

    /**
     * گرفتن کاربری که این ویژگی را در نقش خود دارد
     * 
     * `Role::getConstantHas('access_panel')`
     *
     * @param string $attribute
     * @return int|string|bool
     */
    public static function getConstantHas(string $attribute)
    {
        foreach(self::$constants as $id => $role)
        {
            if((new static($role))->get($attribute))
            {
                return $id;
            }
        }

        return false;
    }

    /**
     * گرفتن کاربرانی که این ویژگی را در نقش خود دارد
     * 
     * `Role::getConstantsHas('access_panel')`
     *
     * @param string $attribute
     * @return array<int|string>
     */
    public static function getConstantsHas(string $attribute)
    {
        $result = [];

        foreach(self::$constants as $id => $role)
        {
            if((new static($role))->get($attribute))
            {
                $result[] = $id;
            }
        }

        return $result;
    }

    /**
     * بررسی می کند یک نقش شامل تمامی نقش های دومی می باشد
     *
     * @param string|array $base
     * @param string|array $role
     * @return bool
     */
    public static function stringExtends(string|array $base, string|array $role)
    {
        $baseRoles = is_string($base) ? explode('|', $base) : $base;
        foreach(is_string($role) ? explode('|', $role) : $role as $subRole)
        {
            if(!in_array($subRole, $baseRoles))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * پیدا کردن کاربری که این نقش را دارد
     * 
     * با این کار هم در مقدار های ثابت دنبال می گردد و هم در دیتابیس
     *
     * @template R
     * 
     * @param class-string<R>|null $model
     * @return int|string|Table|R|false
     */
    public static function find(string $role, string $model = null, bool $getAsModel = false, bool $orOperator = false)
    {
        $model ??= Db::getDefaultUserClass();

        if(($resultId = static::getConstantFor($role, $orOperator)) !== false)
        {
            if($getAsModel)
            {
                return $model::findCache($resultId);
            }
            else
            {
                return $resultId;
            }
        }

        if($orOperator)
        {
            return $model::query()->whereHasRole($role)->get();
        }
        else
        {
            return $model::query()->whereIsRole($role)->get();
        }
    }

    /**
     * پیدا کردن کاربرانی که این نقش را دارد
     * 
     * با این کار هم در مقدار های ثابت دنبال می گردد و هم در دیتابیس
     *
     * @template R
     * 
     * @param class-string<R>|null $model
     * @return Arr<int|string|Table|R>
     */
    public static function findAll(string $role, string $model = null, bool $getAsModel = false, bool $orOperator = false)
    {
        $model ??= Db::getDefaultUserClass();

        $result = [];

        foreach(static::getConstantsFor($role, $orOperator) as $resultId)
        {
            if($getAsModel)
            {
                $result[] = $model::findCache($resultId);
            }
            else
            {
                $result[] = $resultId;
            }
        }

        if($orOperator)
        {
            array_push($result, ...$model::query()->whereHasRole($role)->all());
        }
        else
        {
            array_push($result, ...$model::query()->whereIsRole($role)->all());
        }

        return arr($result);
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
     * @return mixed
     */
    public function get($attribute, $default = false)
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
            
        return value($default);
    }

    /**
     * افزودن نقش
     *
     * @param string $name
     * @return boolean
     */
    public function addRole(string $name)
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
    public function removeRole(string $name)
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
    public function hasRole(string $name)
    {
        return $name == $this->name || in_array($name, $this->advNames);
    }

    /**
     * بررسی می کند نقش اصلی آن این مقدار است یا خیر
     *
     * @param string $name
     * @return boolean
     */
    public function isRole(string $name)
    {
        return $name == $this->name;
    }

    /**
     * بررسی می کند تمامی نقش ها را دارد
     *
     * @param string|array|Role $role
     * @return boolean
     */
    public function isExtends(string|array|Role $role)
    {
        return static::stringExtends($this->toArray(), $role->toArray());
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

    public function toArray()
    {
        return [ $this->name, ...$this->advNames ];
    }

    public function __toString()
    {
        return $this->name . ($this->advNames ? '|' . implode('|', $this->advNames) : '');
    }
    
}

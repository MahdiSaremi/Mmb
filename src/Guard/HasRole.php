<?php

namespace Mmb\Guard; #auto

/**
 * با یوز کردن این تریت، قابلیت هایی مناسب با جدول دارای نقش اضافه می شود
 * 
 * توجه کنید که شما باید در جنریت خود ستونی برای نقش بسازید
 * 
 * `$table->role();`
 * 
 * و اگر از نام متفاوتی استفاده می کنید، تابع زیر را در کلاس تعریف کنید:
 * 
 * `static function getRoleColumn() { return 'custom_role'; }`
 */
trait HasRole
{

    public static function getRoleColumn()
    {
        return 'role';
    }

    /**
     * تنظیم نقش
     * 
     * @param string|Role $role
     * @return void
     */
    public function setRole($role)
    {
        $column = static::getRoleColumn();
        if($role instanceof Role)
        {
            $this->$column = $role;
            return;
        }

        $this->$column = Role::role($role);
    }

    public static function whereRole($role)
    {
        $column = static::getRoleColumn();
        return static::query()->whereHasRole($column, $role);
    }
    
    public static function findRole($role)
    {
        return static::whereRole($role)->get();
    }

    public static function findRoles($role)
    {
        return static::whereRole($role)->all();
    }

}

<?php

namespace Mmb\Guard; #auto

trait HasRole
{

    public function modifyRoleIn(&$data)
    {
        if($role = Role::getConstantOf(@$data['id']))
        {
            $data['role'] = $role;
        }

        Role::modifyIn($data['role']);
    }

    public function modifyRoleOut(&$data)
    {
        if(Role::issetConstant(@$data['id']))
        {
            $data['role'] = null;
            return;
        }

        Role::modifyOut($data['role']);
    }

    /**
     * تنظیم نقش
     * 
     * @param string|Role $role
     * @return void
     */
    public function setRole($role)
    {
        if($role instanceof Role)
        {
            $this->role = $role;
            return;
        }

        $this->role = Role::role($role);
    }

    public static function roleColumn(\Mmb\Db\QueryCol $table)
    {
        $table->text('role')->nullable();
    }

    public static function whereRole($role)
    {
        return static::query()->whereRaw("(`role` = ? OR `role` LIKE ?)", $role, "$role:%");
    }
    
}

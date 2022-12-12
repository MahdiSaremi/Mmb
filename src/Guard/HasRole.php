<?php

namespace Mmb\Guard; #auto

trait HasRole
{

    public function modifyRoleIn(&$data)
    {
        Role::modifyIn($data['role']);
    }

    public function modifyRoleOut(&$data)
    {
        Role::modifyOut($data['role']);
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

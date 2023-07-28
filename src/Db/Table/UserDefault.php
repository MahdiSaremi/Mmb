<?php

namespace Mmb\Db\Table; #auto

use Mmb\Db\QueryCol;
use Handler\Defaultable;
use Mmb\Update\User\UserInfo;

/**
 * کلاسی آماده برای مدل کاربر
 * 
 * @property int $id آیدی عددی کاربر
 * @property string $step مرحله کاربر
 * @property mixed $data دیتای مرحله کاربر
 */
class UserDefault extends Table
    implements Stepable {

    use Defaultable;


    /**
     * کاربر فعلی
     * 
     * @var static
     */
    public static $this;
    public static function this()
    {
        return static::$this;
    }


    public static function getTable()
    {
        return 'users';
    }


    /**
     * گرفتن مقدار های پیشفرض برای ساخت کاربر جدید
     *
     * @return array
     */
    public static function default() {

        return [
            'step' => "",
            'data' => "",
        ];

    }

    public static function generate(QueryCol $query)
    {
        
        $query->bigint('id')->primaryKey();
        
        $query->text('step');
        $query->text('data');

    }

    /**
     * ساخت دیتای کاربر جدید
     *
     * @param mixed $id
     * @return static
     */
    public static function createUser($id)
    {
        
        $data = static::default();
        $data[static::getPrimaryKey()] = $id;

        return static::create($data);

    }

    /**
     * ساخت دیتای این کاربر
     *
     * @return static
     */
    public static function createThisUser() {
        
        return static::$this = static::createUser(UserInfo::$this->id);

    }

    /**
     * گرفتن دیتای این کاربر - اگر قبلا گرفته شده بود همان را بر میگرداند
     *
     * @return static|false
     */
    public static function getThis() {

        if(static::$this)
            return static::$this;

        return static::$this = static::find(UserInfo::$this->id);

    }


    public function modifyDataIn(array &$data)
    {
        parent::modifyDataIn($data);

        if(isset($data['data']))
            $data['data'] = @json_decode($data['data'], true);
        
    }

    public function modifyDataOut(array &$data)
    {
        
        if(isset($data['data']))
            $data['data'] = json_encode($data['data']);

        parent::modifyDataOut($data);
    }


    public function getStep()
    {
        return $this->step;
    }

    public function setStep($step)
    {
        $this->step = $step;
    }

    public function getStepData()
    {
        return $this->data;
    }

    public function setStepData($data)
    {
        $this->data = $data;
    }

    public function stepSave()
    {
        $this->save();
    }

}

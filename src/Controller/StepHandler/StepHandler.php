<?php

namespace Mmb\Controller\StepHandler; #auto

use Mmb\Controller\Handler\Handler;
use Mmb\Db\Table\Table;
use Mmb\Listeners\Listeners;

abstract class StepHandler implements Handlable
{

    // Methods

    public function getHandler()
    {
        return $this;
    }

    /**
     * مدیریت آپدیت
     * 
     * @return Handlable|null
     */
    public abstract function handle();


    // Protected methods

    /**
     * گرفتن نام متغیر هایی که نال نیستند
     *
     * @return string[]
     */
    protected function getSleepNotNull()
    {
        $result = [];
        foreach(get_object_vars($this) as $var => $value)
        {
            if($value !== null)
                $result[] = $var;
        }
        return $result;
    }

    /**
     * گرفتن نام متغیر هایی که به شکل دیفالت خود نیستند
     *
     * @param array $defaultVars
     * @return string[]
     */
    protected function getSleepNotIs(array $defaultVars)
    {
        $result = [];
        foreach($defaultVars as $var => $value)
        {
            if($value !== $this->$var)
                $result[] = $var;
        }
        return $result;
    }
    

    // Static methods
    
    /**
     * @var StepHandler|null
     */
    private static $_step = null;

    /**
     * @return StepHandler|null
     */
    public static function get()
    {
        return self::$_step;
    }

    /**
     * @param StepHandler|Handlable|null $handler
     * @return void
     */
    public static function set(?Handlable $handler)
    {
        self::$_step = $handler ? $handler->getHandler() : null;
    }

    public static function modifyIn(&$step)
    {
        if(!$step)
            return;

        $res = @unserialize($step);

        if($res instanceof StepHandler)
        {
            $step = $res;
            self::set($step);
        }
    }

    public static function modifyOut(&$output)
    {
        $output = serialize(self::get());
    }

    public static function modifyInModel(Table $model)
    {
        $step = $model->step;
        static::modifyIn($step);
        // $model->step = $step;
    }
    
    public static function modifyOutModel(Table $model)
    {
        // $step = $model->step;
        static::modifyOut($step);
        $model->step = $step;
    }

    /**
     * افزودن ستون استپ
     *
     * @param \Mmb\Db\QueryCol $table
     * @param string $column
     * @return void
     */
    public static function column(\Mmb\Db\QueryCol $table, $column)
    {
        $table->text($column)->nullable()->alwaysSave();
    }
    
}

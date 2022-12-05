<?php

namespace Mds\Mmb\Controller\StepHandler; #auto

use Mds\Mmb\Controller\Handler\Handler;
use Mds\Mmb\Listeners\Listeners;

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
     * @param StepHandler|null $handler
     * @return void
     */
    public static function set($handler)
    {
        self::$_step = $handler;
    }

    public static function modifyIn(&$step)
    {
        if(!$step)
            return;

        $res = @unserialize($step);

        if($res instanceof StepHandler)
        {
            $step = $res;
        }
    }

    public static function modifyOut(&$output)
    {
        $output = serialize(self::get());
    }

    /**
     * افزودن ستون استپ
     *
     * @param \Mds\Mmb\Db\QueryCol $table
     * @param string $column
     * @return void
     */
    public static function column(\Mds\Mmb\Db\QueryCol $table, $column)
    {
        $table->text($column)->nullable();
    }
    
}

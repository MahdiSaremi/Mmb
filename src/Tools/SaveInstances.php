<?php

namespace Mds\Mmb\Tools; #auto

use Mds\Mmb\Listeners\Listeners;

trait SaveInstances
{

    private static $instanceObjects = [];

    public final function __construct()
    {
        self::$instanceObjects[] = $this;
    }

    /**
     * @return self[]
     */
    public static function getAllObjects()
    {
        return self::$instanceObjects;
    }

    public static function invokeAllObjects($method, array $args = [])
    {
        foreach(self::$instanceObjects as $object)
        {
            if(method_exists($object, $method))
            {
                Listeners::callMethod([$object, $method], $args);
            }
        }
    }
    
}

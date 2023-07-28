<?php
#auto-name
namespace Mmb\Storage;

use Closure;
use Mmb\Exceptions\MmbException;

class FrameStore
{

    /**
     * نام کلاس استوریج را بر می گرداند
     *
     * @return string
     */
    public static function getStorage()
    {
        return Globals::class;
        // throw new MmbException("Class " . static::class . " must implements getStorage() method");
    }

    /**
     * لود کردن دیتا
     *
     * @return static
     */
    public static function data()
    {
        return new static;
    }

    public function __construct()
    {
        $storage = static::getStorage();
        $data = $storage::getBase();
        foreach(get_object_vars($this) as $name => $value)
        {
            if(array_key_exists($name, $data))
            {
                $this->set($name, $data[$name], true);
            }
            elseif($value instanceof Closure)
            {
                $this->$name = $value();
            }
        }
    }

    public function save()
    {
        Storage::editBase(function(&$data)
        {
            foreach(get_object_vars($this) as $name => $value)
            {
                $data[$name] = $this->get($name, true);
            }
        });
    }

    /**
     * تنظیم مقدار
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function set(string $name, $value, bool $fromLoad = false)
    {
        if(method_exists($this, "set$name"))
        {
            $callback = "set$name";
            $callback($value, $fromLoad);
            return $this;
        }
        
        $this->$name = $value;
        return $this;
    }
    
    /**
     * گرفتن مقدار
     *
     * @param string $name
     * @return mixed
     */
    public function get(string $name, bool $forSave = false)
    {
        if(method_exists($this, "get$name"))
        {
            $callback = "get$name";
            return $callback($forSave);
        }
        
        return $this->$name;
    }

}

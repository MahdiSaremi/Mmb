<?php
#auto-name
namespace Mmb\Storage;

use Closure;
use InvalidArgumentException;
use Mmb\Exceptions\MmbException;
use Mmb\Mapping\Arr;
use Mmb\Mapping\Arrayable;

class FrameArrayStore implements Arrayable
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
     * آدرس سلکتوری که در آن ذخیره می شود را بر می گرداند
     * 
     * به عنوان مثال اگر `settings.channels` وارد شود، وارد آن آدرس می شود و از اطلاعات آنجا استفاده می کند
     *
     * @return ?string
     */
    public static function getAddress()
    {
        return null;
    }

    private static $datas = [];

    /**
     * دیتا را بر می گرداند یا آن را لود می کند
     * 
     * این تابع تنها یک بار دیتا را لود می کند
     *
     * @return Arr<static>
     */
    public static function all()
    {
        if(!isset(static::$datas[static::class]))
        {
            return static::$datas[static::class] = static::load();
        }
        
        return static::$datas[static::class];
    }

    /**
     * دیتا را لود می کند و بر می گرداند
     *
     * @return Arr<static>
     */
    public static function load()
    {
        $all = [];

        $storage = static::getStorage();
        $address = static::getAddress();
        $data = is_null($address) ? $storage::getBase() : $storage::get($address, []);

        foreach($data as $index => $item)
        {
            if(is_array($item))
                $all[] = new static($item);
        }

        return arr($all);
    }

    public function __construct(array $data)
    {
        foreach(get_object_vars($this) as $name => $value)
        {
            if($name[0] == '_') continue;
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

    public function toArray()
    {
        $result = [];
        
        foreach(get_object_vars($this) as $name => $value)
        {
            if($name[0] == '_') continue;
            $result[$name] = $this->get($name, true);
        }

        return $result;
    }

    /**
     * دیتا ها را ذخیره می کند
     *
     * @return void
     */
    public static function saveAll(array|Arr $all)
    {
        $storage = static::getStorage();
        $address = static::getAddress();

        if($all instanceof Arr)
        {
            static::$datas[static::class] = $all;
        }

        $result = [];
        foreach($all as $item)
        {
            if($item instanceof FrameArrayStore)
            {
                $item = $item->toArray();
            }

            $result[] = $item;
        }

        if(is_null($address))
        {
            $storage::setBase($result);
        }
        else
        {
            $storage::set($address, $result);
        }
    }

    /**
     * دیتای این ایندکس را ذخیره می کند
     *
     * @return $this
     */
    public function save()
    {
        $storage = static::getStorage();
        $address = static::getAddress();

        $vars = $this->toArray();

        $index = static::all()->indexOf($this);
        if($index == -1) $index = '+';

        if(is_null($address))
        {
            $address = $index;
        }
        else
        {
            $address .= '.' . $index;
        }
        $storage::set($address, $vars);

        return $this;
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
        if($fromLoad && method_exists($this, "onLoad$name"))
        {
            $callback = "onLoad$name";
            $this->$callback($value);
            return $this;
        }

        if(method_exists($this, "set$name"))
        {
            $callback = "set$name";
            $this->$callback($value, $fromLoad);
            return $this;
        }
        
        $this->$name = $value;
        return $this;
    }

    /**
     * تنظیم چند مقدار همزمان
     *
     * @param array $values
     * @return $this
     */
    public function setAll(array $values)
    {
        foreach($values as $name => $value)
        {
            $this->set($name, $value);
        }

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
        if($forSave && method_exists($this, "onSave$name"))
        {
            $callback = "onSave$name";
            return $this->$callback();
        }

        if(method_exists($this, "get$name"))
        {
            $callback = "get$name";
            return $this->$callback($forSave);
        }
        
        return $this->$name;
    }

    /**
     * یک شی جدید می سازد
     *
     * @param array $data
     * @return static
     */
    public static function new(array $data)
    {
        return new static($data);
    }

    /**
     * به آزایه یک مقدار را اضافه می کند و ذخیره اش می کند
     *
     * @param array|FrameArrayStore $data
     * @return void
     */
    public static function add(array|FrameArrayStore $data)
    {
        $newList = static::all()->append(is_array($data) ? static::new($data) : $data);
        static::saveAll($newList);
    }

    /**
     * این شی را از لیست حذف می کند و دیتای جدید را ذخیره می کند
     *
     * @return $this
     */
    public function delete()
    {
        $all = static::all();
        $index = $all->indexOf($this);

        if($index == -1)
            return $this;

        static::saveAll($all->remove($index));

        return $this;
    }

    /**
     * پیدا کردن شی مورد نظر
     *
     * @param mixed $id
     * @param string $findBy
     * @return static|false
     */
    public static function find($id, string $findBy = 'id')
    {
        return static::all()->where($findBy, $id)->first() ?? false;
    }

}

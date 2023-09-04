<?php
#auto-name
namespace Mmb\Db\Relation;

use Mmb\Db\QueryBuilder;
use Mmb\Exceptions\MmbException;
use Mmb\Mapping\Arr;
use UnitEnum;

/**
 * @template R
 * @extends Relation<R>
 */
abstract class Morph extends Relation
{

    public function getMorphClassFor($name)
    {
        if($class = $this->getClassInsteadOf($name))
        {
            if(class_exists($class))
            {
                return $class;
            }
            return null;
        }
        
        if(class_exists($name))
        {
            return $name;
        }

        return null;
    }

    public function getMorphClassForArr(Arr $all)
    {
        return $all
                ->map(fn($name) => $this->getMorphClassFor($name))
                ->notNull();
    }

    public function getMorphTypeFor($class)
    {
        if($type = $this->getTypeInsteadOf($class))
        {
            return $type;
        }

        return $class;
    }

    protected $morphTypeInsteads = [];
    /**
     * برای کلاسی نامی تعیین می کنید تا در دیتابیس با آن نام صدا زده شود
     *
     * @param string|array $class
     * @param string|null $type
     * @return $this
     */
    public function instead(string|array $class, ?string $type = null)
    {
        if(is_array($class))
        {
            foreach($class as $class0 => $type)
            {
                $this->morphTypeInsteads[$class0] = $type;
            }
        }
        else
        {
            $this->morphTypeInsteads[$class] = $type;
        }

        return $this;
    }

    /**
     * گرفتن کلاس جایگزین برای یک نوع
     *
     * @param mixed $type
     * @return string|false
     */
    public function getClassInsteadOf($type)
    {
        if($type instanceof UnitEnum)
        {
            $type = $type->value;
        }

        return array_search($type, $this->morphTypeInsteads) ?: static::getGlobalClassInsteadOf($type);
    }

    /**
     * گرفتن نوع جایگزین برای کلاس
     *
     * @param string $class
     * @return string|false
     */
    public function getTypeInsteadOf(string $class)
    {
        return $this->morphTypeInsteads[$class] ?? static::getGlobalTypeInsteadOf($class);
    }


    protected static $globalMorphTypeInsteads = [];
    /**
     * برای کلاسی نامی تعیین می کنید تا در دیتابیس با آن نام صدا زده شود
     *
     * @param string|array $class
     * @param string|null $type
     * @return void
     */
    public static function globalInstead(string|array $class, ?string $type = null)
    {
        if(is_array($class))
        {
            foreach($class as $class0 => $type)
            {
                static::$globalMorphTypeInsteads[$class0] = $type;
            }
        }
        else
        {
            static::$globalMorphTypeInsteads[$class] = $type;
        }
    }

    /**
     * گرفتن کلاس جایگزین برای یک نوع
     *
     * @param string $type
     * @return string|false
     */
    public static function getGlobalClassInsteadOf(string $type)
    {
        return array_search($type, static::$globalMorphTypeInsteads);
    }

    /**
     * گرفتن نوع جایگزین برای کلاس
     *
     * @param string $class
     * @return string|false
     */
    public static function getGlobalTypeInsteadOf(string $class)
    {
        return static::$globalMorphTypeInsteads[$class] ?? false;
    }

    public function addWithQuery(QueryBuilder $query)
    {
        throw new MmbException("Morph don't support withQuery()");
    }

}

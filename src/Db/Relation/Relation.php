<?php
#auto-name
namespace Mmb\Db\Relation;

use Closure;
use Mmb\Db\QueryBuilder;
use Mmb\Db\Table\Table;
use Mmb\Db\WhereFacade;
use Mmb\Mapping\Arr;
use Mmb\Mapping\Arrayable;

/**
 * @template R
 * @extends QueryBuilder<R>
 */
abstract class Relation extends QueryBuilder
{

    public Table $model;

    /**
     * گرفتن مقدار این رابطه
     *
     * @return mixed
     */
    protected abstract function getValue();

    /**
     * گرفتن مقدار این رابطه
     *
     * @return mixed
     */
    public function getRelationValue()
    {
        // return $this->lazyLoadRelations(function()
        // {
            $value = $this->getValue();
            if($value)
            {
                $this->setRollback($value);
            }

            return $value;
        // });
    }

    /**
     * گرفتن مقدار این رابطه برای چند مدل
     *
     * @param string $name
     * @param array $models
     * @return mixed
     */
    public abstract function getRelationsFor(string $name, array $models);

    protected function init()
    {
    }

    /**
     * افزودن شرط دارا بودن به یک کوئری
     *
     * @param QueryBuilder $query
     * @param Closure $callback
     * @param string $operator
     * @param integer $count
     * @return void
     */
    public abstract function addHasCondition(QueryBuilder $query, Closure $callback, $operator = '>=', $count = 1, $bool = 'AND');

    /**
     * افزودن مقدار های رابطه به کوئری
     *
     * @param QueryBuilder $query
     * @return ?string
     */
    public abstract function addWithQuery(QueryBuilder $query);

    /**
     * متد پیشفرض افزودن شرط دارا بودن
     *
     * @param QueryBuilder $query
     * @param Closure $callback
     * @param string $operator
     * @param integer $count
     * @return void
     */
    protected function addHasConditionDefault(QueryBuilder $query, Closure $callback, $operator = '>=', $count = 1, $bool = 'AND')
    {
        if($callback)
        {
            $callback($this);
        }

        $this->setRelatedFromClass($query->output);

        if(($operator == '>=' && $count == 1) || ($operator == '>' && $count == 0))
        {
            $query->{"{$bool}whereRawExists"}($this);
        }
        else
        {
            $query->{"{$bool}whereInner"}($this->clearSelect()->select('COUNT(*)')->disableAddSelect(), $operator, $count);
        }
    }


    protected $related_to;
    protected $related_to_type;

    /**
     * تنظیم می کند که این رابطه با چه المانی اجرا شود
     *
     * @param mixed $value
     * @param string $type
     * @return $this
     */
    public function setRelatedTo($value, string $type = '')
    {
        $this->related_to = $value;
        $this->related_to_type = $type;
        return $this;
    }

    /**
     * تنظیم می کند که این رابطه با چه جدولی بصورت مستقیم اجرا شود
     *
     * @param string $class
     * @return $this
     */
    public abstract function setRelatedFromClass(string $class);

    /**
     * افزودن شرط رابطه
     *
     * @param string $column
     * @param mixed $default
     * @return $this
     */
    protected function whereRelationRelatedTo(string $column, $default = null)
    {
        if(isset($this->related_to))
        {
            $this->{"where{$this->related_to_type}"}($column, $this->related_to);
        }
        else
        {
            $this->where($column, $default);
        }

        return $this;
    }

    protected $disabled_add_select;
    /**
     * غیر فعال کردن سلکت های مربوط به رابطه
     *
     * @return $this
     */
    public function disableAddSelect()
    {
        $this->disabled_add_select = true;
        return $this;
    }

    protected function getRelationChangeItems()
    {
        return [ 'where' ];
    }
    protected function changeRelationQuery($type)
    {
    }

    protected function applyRelationChange(string $type, Closure $callback)
    {
        $before = [];
        foreach($this->getRelationChangeItems() as $item)
        {
            $before[$item] = $this->$item;
        }

        $this->changeRelationQuery($type);

        try
        {
            return $callback();
        }
        finally
        {
            foreach($before as $item => $restore)
            {
                $this->$item = $restore;
            }
        }
    }

    public function run($type, $local = array(), $exportStringQuery = false)
    {
        if(!in_array($type, ['select', 'update', 'delete']))
        {
            return parent::run($type, $local, $exportStringQuery);
        }

        return $this->applyRelationChange($type, fn() => parent::run($type, $local, $exportStringQuery));
    }

    public ?string $rollback = null;

    /**
     * تنظیم می کند این مدل، در مدل پاسخ رابطه چه نامی دارد و آن را پر می کند
     *
     * @param string $relation
     * @return $this
     */
    public function rollback(string $relation)
    {
        $this->rollback = $relation;
        $this->without($relation);
        return $this;
    }

    /**
     * رولبک ها را تنظیم می کند
     *
     * @param Table|Arr $value
     * @param Table|null $model
     * @return void
     */
    protected function setRollback($value, ?Table $model = null)
    {
        if($this->rollback)
        {
            $model ??= $this->model;
            if($value instanceof Arr)
            {
                foreach($value as $item)
                {
                    $item->{$this->rollback} = $model;
                }
            }
            elseif($value instanceof Table)
            {
                $value->{$this->rollback} = $model;
            }
        }
    }

    public function create(Arrayable|array $data = [], bool $modify = true)
    {
        return $this->applyRelationChange('insert', fn() => parent::create($data, $modify));
    }

    public function createMulti(Arrayable|array $data = [], bool $modify = true)
    {
        return $this->applyRelationChange('insert', fn() => parent::createMulti($data, $modify));
    }

}

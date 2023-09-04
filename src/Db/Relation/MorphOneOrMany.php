<?php
#auto-name
namespace Mmb\Db\Relation;

use Mmb\Db\QueryBuilder;
use Mmb\Db\Table\Table;
use Mmb\Tools\Text;

/**
 * @template R
 * @extends Morph<R>
 */
abstract class MorphOneOrMany extends Morph
{
    
    protected string $morph_class;
    protected string $morph_name;
    protected string $morph_type;
    protected string $morph_id;

    /**
     * یک رابطه می سازد
     *
     * @template T
     * @param Table $model
     * @param class-string<T> $class
     * @return static<T>
     */
    public static function makeRelation(Table $model, string $class, string $name, string $type = null, string $id = null)
    {
        $type ??= $name . '_type';
        $id ??= $name . '_id';

        $relation = $class::queryWith(static::class);
        $relation->model = $model;
        $relation->morph_class = $class;
        $relation->morph_name = $name;
        $relation->morph_type = $type;
        $relation->morph_id = $id;
        $relation->init();

        return $relation;
    }

    protected function changeRelationQuery($type)
    {
        $this->beforeWhere(function($query)
        {
            $query->whereRelatedTypeTo($this->morph_class::column($this->morph_type), $this->getMorphTypeFor(get_class($this->model)));
            $query->whereRelationRelatedTo($this->morph_class::column($this->morph_id), $this->model->getPrimaryValue());
        });
    }

    public function setRelatedFromClass(string $class)
    {
        return $this
                ->setRelatedTo($class::column($this->morph_id), 'col')
                ->setRelatedTypeTo($class::column($this->morph_type), 'col');
    }
    
    protected $related_type_to;
    protected $related_type_to_type;

    /**
     * تنظیم می کند که این رابطه با چه المانی اجرا شود
     *
     * @param mixed $value
     * @param string $type
     * @return $this
     */
    public function setRelatedTypeTo($value, string $type = '')
    {
        $this->related_type_to = $value;
        $this->related_type_to_type = $type;
        return $this;
    }

    /**
     * افزودن شرط رابطه
     *
     * @param string $column
     * @param mixed $default
     * @return $this
     */
    protected function whereRelatedTypeTo(string $column, $default = null)
    {
        if(isset($this->related_type_to))
        {
            $this->{"where{$this->related_type_to_type}"}($column, $this->related_type_to);
        }
        else
        {
            $this->where($column, $default);
        }

        return $this;
    }

    public function addHasCondition(QueryBuilder $query, $callback, $operator = ">=", $count = 1, $bool = 'AND')
    {
        $this->addHasConditionDefault($query, $callback, $operator, $count, $bool);
    }

}

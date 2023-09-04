<?php
#auto-name
namespace Mmb\Db\Relation;

use Mmb\Db\QueryBuilder;
use Mmb\Db\Table\Table;
use Mmb\Tools\Text;

abstract class HasOneOrMany extends Relation
{

    protected string $target_column;
    protected string $local_primary;
    protected string $target_class;

    /**
     * یک رابطه می سازد
     *
     * @template T
     * @param Table $model
     * @param class-string<T> $class
     * @param string|null $column
     * @param string|null $primary_column
     * @return static<T>
     */
    public static function makeRelation(Table $model, string $class, string $column = null, string $primary_column = null)
    {
        if($primary_column === null)
        {
            $primary_column = $model::getPrimaryKey();
        }

        if($column === null)
        {
            $column = Text::snake(Text::afterLast(get_class($model), "\\")) . "_" . $primary_column;
        }
        
        $relation = $class::queryWith(static::class);
        $relation->target_class = $class;
        $relation->target_column = $column;
        $relation->local_primary = $primary_column;
        $relation->model = $model;
        $relation->init();

        return $relation;
    }
    
    protected function changeRelationQuery($type)
    {
        $this->beforeWhere(function($query)
        {
            $query->whereRelationRelatedTo($this->target_class::column($this->target_column), $this->model->{$this->local_primary});
        });
    }

    public function setRelatedFromClass($class)
    {
        return $this->setRelatedTo($class::column($this->local_primary), 'col');
    }

    public function addHasCondition(QueryBuilder $query, $callback, $operator = ">=", $count = 1, $bool = 'AND')
    {
        $this->addHasConditionDefault($query, $callback, $operator, $count, $bool);
    }

    public function addWithQuery(QueryBuilder $query)
    {
        $query->leftJoin($this->target_class,
            fn($query) => $query
                ->whereCol($this->model::column($this->local_primary), $this->target_class::column($this->target_column))
        );
        return $this->target_class;
    }

}

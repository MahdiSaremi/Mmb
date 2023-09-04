<?php
#auto-name
namespace Mmb\Db\Relation;

use Closure;
use Mmb\Db\QueryBuilder;
use Mmb\Db\Table\Model;
use Mmb\Db\Table\Table;
use Mmb\Db\WhereFacade;
use Mmb\Tools\Text;

/**
 * رابطه یک به یک
 * 
 * `Liker:`
 * - `unsignedBigInt id`
 * - `unsignedBigInt like_id`
 * 
 * ----
 * 
 * `Like:`
 * - `unsignedBigInt id`
 * 
 * ----
 * 
 * متد رابطه.
 * 
 * `Liker::like():`
 * - `return $this->belongsTo(Like::class);`
 * 
 * @template R
 * @extends Relation<R>
 */
class BelongsTo extends Relation
{

    protected string $local_column;
    protected string $target_primary;
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
            $primary_column = $class::getPrimaryKey();
        }

        if($column === null)
        {
            $column = Text::snake(Text::afterLast($class, "\\")) . "_" . $primary_column;
        }
        
        $relation = $class::queryWith(static::class);
        $relation->target_class = $class;
        $relation->local_column = $column;
        $relation->target_primary = $primary_column;
        $relation->model = $model;
        $relation->init();

        return $relation;
    }
    
    protected function changeRelationQuery($type)
    {
        $this->beforeWhere(function($query)
        {
            $query->whereRelationRelatedTo($this->target_class::column($this->target_primary), $this->model->{$this->local_column});
        });
    }

    protected function getValue()
    {
        return $this->get();
    }

    public function getRelationsFor(string $name, array $models)
    {
        $ids = [];
        foreach($models as $model)
        {
            $ids[] = $model->{$this->local_column};
        }
        $this->setRelatedTo($ids, 'in');
        
        $all = $this->all();
        foreach($models as $model)
        {
            $model->$name = $all->find($model->{$this->local_column}, $this->target_primary);
            $this->setRollback($model->$name, $model);
        }
    }

    public function setRelatedFromClass($class)
    {
        return $this->setRelatedTo($class::column($this->local_column), 'col');
    }

    public function addHasCondition(QueryBuilder $query, $callback, $operator = ">=", $count = 1, $bool = 'AND')
    {
        $this->addHasConditionDefault($query, $callback, $operator, $count, $bool);
    }

    public function addWithQuery(QueryBuilder $query)
    {
        $query->leftJoin($this->target_class,
            fn($query) => $query
                ->whereCol($this->model::column($this->local_column), $this->target_class::column($this->target_primary))
        );
        return $this->target_class;
    }

    /**
     * اتصال این ردیف به این مدل
     * 
     * بعد از این عملیات نیاز به ذخیره کردن مدل این رابطه نیز دارید
     *
     * @param Table|mixed $model
     * @param bool $save
     * @return $this
     */
    public function associate($model, bool $save = false)
    {
        $this->model->{$this->local_column} = $model instanceof Table ? $model->{$this->target_primary} : $model;
        if($save)
        {
            $this->model->save();
        }

        return $this;
    }

    /**
     * حذف رابطه این مدل
     * 
     * توجه کنید که برای این عملیات باید ستون رابطه شما نال پذیر باشد
     * 
     * بعد از این عملیات نیاز به ذخیره کردن مدل این رابطه نیز دارید
     *
     * @param boolean $save
     * @return $this
     */
    public function dissociate(bool $save = false)
    {
        $this->model->{$this->local_column} = null;
        if($save)
        {
            $this->model->save();
        }

        return $this;
    }

    /**
     * بررسی وجود رابطه
     * 
     * بررسی می کند که مقدار ستون رابطه نال نباشد
     * 
     * @return bool
     */
    public function check()
    {
        return !is_null($this->model->{$this->local_column});
    }

    /**
     * آیدی رابطه ای که ذخیره شده است یا می گیرد
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->model->{$this->local_column};
    }

}

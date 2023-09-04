<?php
#auto-name
namespace Mmb\Db\Relation;

use Closure;
use Exception;
use InvalidArgumentException;
use Mmb\Db\QueryBuilder;
use Mmb\Db\Table\Table;
use Mmb\Db\WhereFacade;
use Mmb\Mapping\Arr;
use Mmb\Mapping\Arrayable;
use Mmb\Tools\Text;

/**
 * رابطه چند به چند
 * 
 * `Post:`
 * - `unsignedBigInt id`
 * 
 * ---
 * `Comment:`
 * - `unsignedBigInt id`
 * 
 * ----
 * 
 * `PostComment:`
 * - `unsignedBigInt id`
 * - `unsignedBigInt post_id`
 * - `unsignedBigInt comment_id`
 * 
 * ----
 * 
 * متد رابطه.
 * 
 * `Post::comment():`
 * - `return $this->belongsToMany(Comment::class, PostComment::class);`
 * 
 * @template R
 * @template P
 * @extends Relation<R>
 */
class BelongsToMany extends Relation
{

    protected string $local_column;
    protected string $local_primary;
    protected string $target_column;
    protected string $target_primary;
    protected string $pivot_class;
    protected string $target_class;

    /**
     * یک رابطه می سازد
     *
     * @template T
     * @template P
     * @param Table $model
     * @param class-string<T> $class
     * @param class-string<P> $pivotClass
     * @param string $localColumn
     * @param string|null $targetColumn
     * @param string|null $localPrimary
     * @param string|null $targetPrimary
     * @return static<T,P>
     */
    public static function makeRelation(Table $model, string $class, string $pivotClass, string $localColumn = null, string $targetColumn = null, string $localPrimary = null, string $targetPrimary = null)
    {
        if($localPrimary === null)
        {
            $localPrimary = $model::getPrimaryKey();
        }
        if($targetPrimary === null)
        {
            $targetPrimary = $class::getPrimaryKey();
        }

        if($localColumn === null)
        {
            $localColumn = Text::snake(Text::afterLast(get_class($model), "\\")) . "_" . $localPrimary;
        }
        if($targetColumn === null)
        {
            $targetColumn = Text::snake(Text::afterLast($class, "\\")) . "_" . $targetPrimary;
        }

        $relation = $class::queryWith(static::class);
        $relation->model = $model;
        $relation->target_class = $class;
        $relation->pivot_class = $pivotClass;
        $relation->target_column = $targetColumn;
        $relation->target_primary = $targetPrimary;
        $relation->local_column = $localColumn;
        $relation->local_primary = $localPrimary;
        $relation->init();

        return $relation;
    }

    public function init()
    {
        $this->join($this->pivot_class, $this->target_class::column($this->target_primary), $this->pivot_class::column($this->target_column));
    }

    public function getSelects($select = null)
    {
        $selects = parent::getSelects($select);
        foreach($selects as $i => $select)
        {
            if(($select == '*' || @$select[0] == '`') && !Text::contains($select, '.'))
            {
                $selects[$i] = $this->stringColumn($this->target_class::getTableName()) . '.' . $select;
            }
        }

        if(!$this->disabled_add_select)
        {
            $selects[] = $this->stringColumn($this->pivot_class::column($this->local_column)) . ' AS ' . $this->stringColumn($this->getLocalPivot());
            $selects[] = $this->stringColumn($this->pivot_class::column($this->target_column)) . ' AS ' . $this->stringColumn($this->getTargetPivot());
        }

        return $selects;
    }

    public function getLocalPivot()
    {
        return 'pivot_' . $this->local_column;
    }
    public function getTargetPivot()
    {
        return 'pivot_' . $this->target_column;
    }

    protected function getValue()
    {
        return $this->all();
    }

    public function getRelationsFor(string $name, array $models)
    {
        $ids = [];
        foreach($models as $model)
        {
            $ids[] = $model->{$this->local_primary};
        }
        $this->setRelatedTo($ids, 'in');
        
        $all = $this->all();
        foreach($models as $model)
        {
            $model->$name = $all->where($this->getLocalPivot(), $model->{$this->local_primary});
            $this->setRollback($model->$name, $model);
        }
    }

    protected function changeRelationQuery($type)
    {
        if($type != 'select')
        {
            return;
        }

        $this->beforeWhere(function($query)
        {
            $query->whereRelationRelatedTo($this->pivot_class::column($this->local_column), $this->model->{$this->local_primary});
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
        $query->leftJoin($this->pivot_class,
            fn($query) => $query
                ->whereCol($this->model::column($this->local_primary), $this->pivot_class::column($this->local_column))
        );
        $query->leftJoin($this->target_class,
            fn($query) => $query
                ->whereCol($this->pivot_class::column($this->target_column), $this->target_class::column($this->target_primary))
        );
        return $this->target_class;
    }

    protected function getModelsForPivot($models, bool $acceptData = true, array $data = [])
    {
        if(is_array($models))
        {
            $map = [];
            foreach($models as $i => $model)
            {
                if(is_array($model))
                {
                    if($acceptData)
                    {
                        $map[$i] = [ $this->target_column => $i ] + $model + $data;
                    }
                    else
                    {
                        throw new InvalidArgumentException("Required string|int|Table, given array");
                    }
                }
                elseif($model instanceof Table)
                {
                    $map[$model->{$this->target_primary}] = [ $this->target_column => $model->{$this->target_primary} ] + $data;
                }
                else
                {
                    $map[$model] = [ $this->target_column => $model ] + $data;
                }
            }
            return $map;
        }
        elseif($models instanceof Table)
        {
            return [ $models->{$this->target_primary} => [ $this->target_column => $models->{$this->target_primary} ] + $data ];
        }
        else
        {
            return [ $models => [ $this->target_column => $models ] + $data ];
        }
    }

    /**
     * این مدل را به رابطه اضافه می کند
     *
     * @param Table|array|mixed $model Model or ID
     * @param array $data
     * @return P|Table|bool
     */
    public function attach($model, array $data = [])
    {
        if(is_array($model))
        {
            $map = $this->getModelsForPivot($model, true, $data);
            return $this->pivot()->createMulti($map);
        }
        else
        {
            return $this->pivotFor($model)->create($data);
        }
    }

    /**
     * این مدل را از لیست رابطه حذف می کند
     *
     * @param Table|array|mixed $model
     * @return bool
     */
    public function detach($model)
    {
        if(is_array($model))
        {
            $map = $this->getModelsForPivot($model, false);
            return $this->pivot()->whereIn($this->target_column, array_keys($map))->delete();
        }
        else
        {
            return $this->pivotFor($model)->delete();
        }
    }

    /**
     * همه مدل هایی که در این لیست نیستند را از رابطه حذف می کند
     *
     * @param Table|array|mixed $model
     * @return bool
     */
    public function detachExcept($model)
    {
        $map = $this->getModelsForPivot($model, false);
        return $this->pivot()->whereNotIn($this->target_column, array_keys($map))->delete();
    }

    /**
     * همه رابطه ها را حذف می کند
     *
     * @return bool
     */
    public function detachAll()
    {
        return $this->pivot()->delete();
    }

    /**
     * از رابطه ها تنها این رابطه ها را باقی می گذارد. آنهایی که در لیست نیست حذف می شوند و آنهایی که هستند اضافه یا بدون تغییر می شوند
     *
     * @param Table|array|mixed $model
     * @param array $data
     * @return bool
     */
    public function sync($model, array $data = [])
    {
        $map = $this->getModelsForPivot($model, true, $data);

        $this->detachExcept(array_keys($map));

        $exists = $this->pivot()->pluck($this->target_column);
        foreach($exists as $id)
        {
            unset($map[$id]);
        }

        return $this->pivot()->createMulti($map);
    }

    /**
     * رابطه هایی که در لیست هستند را در صورت عدم وجود اضافه می کند
     *
     * @param Table|array|mixed $model
     * @param array $data
     * @return bool
     */
    public function syncWithoutDetach($model, array $data = [])
    {
        $map = $this->getModelsForPivot($model, true, $data);

        $exists = $this->pivot()->whereIn($this->target_column, array_keys($map))->pluck($this->target_column);
        foreach($exists as $id)
        {
            unset($map[$id]);
        }

        return $this->pivot()->createMulti($map);
    }

    /**
     * رابطه ها را اگر وجود دارند، حذف و اگر وحود ندارند اضافه می کند
     *
     * @param Table|array|mixed $model
     * @param array $data
     * @return bool
     */
    public function toggle($model, array $data = [])
    {
        $map = $this->getModelsForPivot($model, true, $data);
        $mapExists = [];
        $mapNotExists = $map;

        $exists = $this->pivot()->whereIn($this->target_column, array_keys($map))->pluck($this->target_column);
        foreach($exists as $id)
        {
            if(isset($map[$id]))
            {
                $mapExists[$id] = $map[$id];
                unset($mapNotExists[$id]);
            }
        }

        $ok1 = $this->detach(array_keys($mapExists));
        $ok2 = $this->pivot()->createMulti($mapNotExists);

        return $ok1 && $ok2;
    }

    /**
     * بررسی می کند این مدل/مدل ها در رابطه وجود دارد
     *
     * @param Table|array|mixed $model
     * @return bool
     */
    public function check($model)
    {
        if(is_array($model))
        {
            $ids = array_keys($this->getModelsForPivot($model, false));
            $exists = $this->pivot()->whereIn($this->target_column, $ids)->pluck($this->target_column);

            return $exists->containsAll($ids);
        }
        else
        {
            return $this->pivotFor($model)->exists();
        }
    }
    

    /**
     * یک ردیف به جدول تارگت اضافه می کند و همچنین یک رابطه بین این دو می سازد
     * 
     * @param array $data
     * @return R|Table|false
     */
    public function create($data = [], bool $modify = true)
    {
        if($target = parent::create($data, $modify))
        {
            try
            {
                if(!$this->attach($target))
                {
                    $target->delete();
                    return false;
                }
            }
            catch(Exception $e)
            {
                $target->delete();
                throw $e;
            }
        }

        return $target;
    }

    /**
     * کوئری ای برای جدول رابط می دهد
     *
     * @return QueryBuilder<P>
     */
    public function pivot()
    {
        return $this->pivot_class::query()
            ->where($this->local_column, $this->model->{$this->local_primary});
    }

    /**
     * کوئری ای برای جدول رابط و این میانه می دهد
     *
     * @param Table|mixed $model
     * @return $this
     */
    public function pivotFor($model)
    {
        return $this->pivot()->where($this->target_column, $model instanceof Table ? $model->{$this->target_primary} : $model);
    }


    
    /**
     * افزودن شرط برای ستون جدول میانی
     *
     * @return $this
     */
    public function wherePivot(string $col, $operator, $value = null)
    {
        $args = func_get_args();
        unset($args[0]);

        return $this->where($this->pivot_class::column($col), ...$args);
    }

    /**
     * افزودن شرط برای ستون جدول میانی
     *
     * @return $this
     */
    public function andWherePivot(string $col, $operator, $value = null)
    {
        $args = func_get_args();
        unset($args[0]);

        return $this->andWhere($this->pivot_class::column($col), ...$args);
    }

    /**
     * افزودن شرط برای ستون جدول میانی
     *
     * @return $this
     */
    public function orWherePivot(string $col, $operator, $value = null)
    {
        $args = func_get_args();
        unset($args[0]);

        return $this->orWhere($this->pivot_class::column($col), ...$args);
    }

    /**
     * افزودن شرط برای ستون جدول میانی و یک ستون دیگر
     *
     * @return $this
     */
    public function wherePivotCol(string $col, $operator, $col2 = null)
    {
        $args = func_get_args();
        unset($args[0]);

        return $this->whereCol($this->pivot_class::column($col), ...$args);
    }

    /**
     * افزودن شرط برای ستون جدول میانی و یک ستون دیگر
     *
     * @return $this
     */
    public function andWherePivotCol(string $col, $operator, $col2 = null)
    {
        $args = func_get_args();
        unset($args[0]);

        return $this->andWhereCol($this->pivot_class::column($col), ...$args);
    }

    /**
     * افزودن شرط برای ستون جدول میانی و یک ستون دیگر
     *
     * @return $this
     */
    public function orWherePivotCol(string $col, $operator, $col2 = null)
    {
        $args = func_get_args();
        unset($args[0]);

        return $this->orWhereCol($this->pivot_class::column($col), ...$args);
    }

    /**
     * افزودن شرط برای وجود ستون جدول میانی در یکی از عناصر آرایه
     *
     * @return $this
     */
    public function wherePivotIn(string $col, array|Arrayable $array)
    {
        return $this->whereIn($this->pivot_class::column($col), $array);
    }

    /**
     * افزودن شرط برای وجود ستون جدول میانی در یکی از عناصر آرایه
     *
     * @return $this
     */
    public function andWherePivotIn(string $col, array|Arrayable $array)
    {
        return $this->andWhereIn($this->pivot_class::column($col), $array);
    }

    /**
     * افزودن شرط برای وجود ستون جدول میانی در یکی از عناصر آرایه
     *
     * @return $this
     */
    public function orWherePivotIn(string $col, array|Arrayable $array)
    {
        return $this->orWhereIn($this->pivot_class::column($col), $array);
    }

    /**
     * افزودن شرط برای بررسی نال بودن ستون جدول میانی
     *
     * @return $this
     */
    public function wherePivotIsNull(string $col)
    {
        return $this->whereIsNull($this->pivot_class::column($col));
    }

    /**
     * افزودن شرط برای بررسی نال بودن ستون جدول میانی
     *
     * @return $this
     */
    public function andWherePivotIsNull(string $col)
    {
        return $this->andWhereIsNull($this->pivot_class::column($col));
    }

    /**
     * افزودن شرط برای بررسی نال بودن ستون جدول میانی
     *
     * @return $this
     */
    public function orWherePivotIsNull(string $col)
    {
        return $this->orWhereIsNull($this->pivot_class::column($col));
    }

    /**
     * افزودن شرط برای بررسی نال نبودن ستون جدول میانی
     *
     * @return $this
     */
    public function wherePivotIsNotNull(string $col)
    {
        return $this->whereIsNotNull($this->pivot_class::column($col));
    }

    /**
     * افزودن شرط برای بررسی نال نبودن ستون جدول میانی
     *
     * @return $this
     */
    public function andWherePivotIsNotNull(string $col)
    {
        return $this->andWhereIsNotNull($this->pivot_class::column($col));
    }

    /**
     * افزودن شرط برای بررسی نال نبودن ستون جدول میانی
     *
     * @return $this
     */
    public function orWherePivotIsNotNull(string $col)
    {
        return $this->orWhereIsNotNull($this->pivot_class::column($col));
    }


}

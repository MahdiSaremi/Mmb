<?php
#auto-name
namespace Mmb\Db\Relation;

use Mmb\Db\QueryBuilder;
use Mmb\Db\Table\Table;
use Mmb\Exceptions\MmbException;
use Mmb\Mapping\Arr;
use Mmb\Mapping\Arrayable;
use Mmb\Mapping\Map;
use Mmb\Tools\Text;

/**
 * رابطه چند به چند
 * 
 * `Movie:`
 * - `unsignedBigInt id`
 * 
 * ----
 * 
 * `Asset:`
 * - `unsignedBigInt id`
 * - `unsignedBigInt movie_id`
 * - `enum asset_type`
 * - `unsignedBigInt asset_id`
 * 
 * ----
 * 
 * `ImageAsset:`
 * - `unsignedBigInt id`
 * 
 * ----
 * 
 * `VideoAsset:`
 * - `unsignedBigInt id`
 * 
 * ----
 * 
 * متد رابطه.
 * 
 * `Movie::assets():`
 * - `return $this->morphToMany(Asset::class, 'asset');`
 * 
 * @template R
 * @extends Morph<R>
 */
class MorphToMany extends Morph
{
    
    protected string $morph_class;
    protected string $morph_name;
    protected string $morph_local_column;
    protected string $morph_type;
    protected string $morph_id;

    /**
     * یک رابطه می سازد
     *
     * @param Table $model
     * @return static<Table>
     */
    public static function makeRelation(Table $model, string $morphClass, string $name, string $localColumn = null, string $type = null, string $id = null)
    {
        $type ??= $name . '_type';
        $id ??= $name . '_id';

        $localColumn ??= Text::snake(Text::afterLast(get_class($model), "\\")) . '_' . $model::getPrimaryKey();

        $relation = new static;
        $relation->model = $model;
        $relation->morph_class = $morphClass;
        $relation->morph_name = $name;
        $relation->morph_local_column = $localColumn;
        $relation->morph_type = $type;
        $relation->morph_id = $id;
        $relation->init();

        return $relation;
    }

    // public function init()
    // {
    //     $this->join($this->morph_class, $this->morph_class::column($this->target_primary), $this->pivot_class::column($this->target_column));
    // }

    protected function getValue()
    {
        return $this->all();
    }

    public function getRelationsFor(string $name, array $models)
    {
        $models = arr($models);

        $ids = $models->pluck($this->model::getPrimaryKey())->unique();
        $this->setRelatedTo($ids, 'in');

        $pivotAll = $this->pivot()->all([ $this->morph_local_column, $this->morph_type, $this->morph_id ]);
        $types = $pivotAll->pluck($this->morph_type)->unique();

        $modelRelations = [];
        foreach($models as $model)
        {
            $modelRelations[$model->getPrimaryValue()] = [];
        }

        foreach($types as $type)
        {
            if($targetClass = $this->getMorphClassFor($type))
            {
                $pivotsThisType = $pivotAll->where($this->morph_type, $type);
                    
                $ids = $pivotsThisType->pluck($this->morph_local_column)->unique();
                $this->setRelatedTo($ids, 'in');
                
                // $this->model = first($pivotsThisType);
                $all = $this
                        ->targetFor($targetClass, $type)
                        ->all();

                if($all->count())
                {
                    foreach($pivotsThisType as $pivot)
                    {
                        $modelRelations[$pivot->{$this->morph_local_column}][]
                            = $all->find($pivot->{$this->morph_id}, $targetClass::getPrimaryKey());
                        // $this->setRollback($model->$name, $model);
                    }
                }
            }
        }

        $mprimary = first($models)?->getPrimaryKey();
        foreach($modelRelations as $id => $relations)
        {
            $models->find($id, $mprimary)->$name = arr($relations);
        }
    }

    /**
     * انتخاب ها را تنظیم می کند
     *
     * @param QueryBuilder $query
     * @param string $class
     * @param mixed $select
     * @return QueryBuilder
     */
    protected function setSelects(QueryBuilder $query, $class, $select = null)
    {
        if($select === null)
        {
            $query->clearSelect();
            $query->selects = [$this->stringColumn($class::getTableName()) . '.*'];
        }
        else
        {
            $selects = $query->getSelects($select);
            $query->clearSelect();

            foreach($selects as $i => $select)
            {
                if(($select == '*' || @$select[0] == '`') && !Text::contains($select, '.'))
                {
                    $selects[$i] = $this->stringColumn($class::getTableName()) . '.' . $select;
                }
            }
            $query->selects = $selects;
        }

        if(!$this->disabled_add_select)
        {
            $query->selects[] = $this->stringColumn($this->morph_class::column($this->morph_local_column)) . ' AS ' . $this->stringColumn($this->getLocalPivot());
            $query->selects[] = $this->stringColumn($this->morph_class::column($this->morph_type)) . ' AS ' . $this->stringColumn($this->getTypePivot());
            $query->selects[] = $this->stringColumn($this->morph_class::column($this->morph_id)) . ' AS ' . $this->stringColumn($this->getTargetPivot());
        }

        return $query;
    }

    public function getLocalPivot()
    {
        return 'pivot_' . $this->morph_local_column;
    }
    public function getTargetPivot()
    {
        return 'pivot_' . $this->morph_id;
    }
    public function getTypePivot()
    {
        return 'pivot_' . $this->morph_type;
    }

    /**
     * گرفتن کوئری ای برای جدول واسط مورف
     *
     * @return QueryBuilder<R>
     */
    public function pivot()
    {
        return $this
                ->newQuery()
                ->beforeWhere(function($query)
                {
                    $query->whereRelationRelatedTo($this->morph_class::column($this->morph_local_column), $this->model->getPrimaryValue());
                })
                ->newQueryFrom($this->morph_class);
    }

    /**
     * گرفتن کوئری ای برای نوع دلخواه
     *
     * @param string $class
     * @param string $type
     * @param mixed $select
     * @return QueryBuilder
     */
    protected function targetFor(string $class, string $type, $select = null)
    {
        return $this->setSelects($this
                ->newQuery()
                ->beforeWhere(function($query) use($type)
                {
                    $query->where($this->morph_class::column($this->morph_type), $type);
                    $query->whereRelationRelatedTo($this->morph_class::column($this->morph_local_column), $this->model->getPrimaryValue());
                })
                ->newQueryFrom($class)
                ->join($this->morph_class, $class::column($class::getPrimaryKey()), $this->morph_class::column($this->morph_id))
                ->without($this->rollback ?? ''),
            $class,
            $select
        );
    }

    /**
     * لیست نوع ها را می دهد
     *
     * @return Arr<string>
     */
    public function pluckTypes()
    {
        return $this->pivot()->pluckUnique($this->morph_type);
    }

    /**
     * گرفتن کل مقدار ها
     *
     * @param string|array|null $select
     * @return Arr<Table>
     */
    public function all($select = null)
    {
        $result = [];
        foreach($this->pluckTypes() as $type)
        {
            if($targetClass = $this->getMorphClassFor($type))
            {
                array_push($result, ...
                    $this
                        ->targetFor($targetClass, $type, $select)
                        ->all()
                );
            }
        }

        return arr($result);
    }

    /**
     * گرفتن کل مقدار ها، دسته بندی شده بر اساس نوع
     *
     * @param string|array|null $select
     * @return Map<Table>
     */
    public function allAssocByType($select = null)
    {
        $result = [];
        foreach($this->pluckTypes() as $type)
        {
            if($targetClass = $this->getMorphClassFor($type))
            {
                $result[$type] =
                    $this
                        ->targetFor($targetClass, $type, $select)
                        ->all();
            }
        }

        return map($result);
    }

    public function pluck($select)
    {
        $result = [];
        foreach($this->pluckTypes() as $type)
        {
            if($targetClass = $this->getMorphClassFor($type))
            {
                array_push($result, ...
                    $this
                        ->targetFor($targetClass, $type)
                        ->pluck($select)
                );
            }
        }

        return arr($result);
    }

    public function get($select = null)
    {
        $first = $this->pivot()->get($select);
        if($first)
        {
            if($target = $this->getMorphClassFor($first->{$this->morph_type}))
            {
                return $target::find($first->{$this->morph_id});
            }
        }

        return false;
    }

    public function count($of = "*")
    {
        return $this->pivot()->count();
    }

    public function exists()
    {
        return $this->pivot()->exists();
    }

    public function run($type, $local = array(), $exportStringQuery = false)
    {
        throw new MmbException("This method is not supported");
    }

    public function setRelatedFromClass(string $class)
    {
        return $this->setRelatedTo($class::column($this->morph_id), 'col');
    }

    public function rollback($relation)
    {
        throw new MmbException("MorphToMany is not supported rollback()");
    }
    
    public function addHasCondition(QueryBuilder $query, $callback, $operator = ">=", $count = 1, $bool = 'AND')
    {
        throw new MmbException("MorphToMany relation is not suppported has()");
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

        return $this->where($this->morph_class::column($col), ...$args);
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

        return $this->andWhere($this->morph_class::column($col), ...$args);
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

        return $this->orWhere($this->morph_class::column($col), ...$args);
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

        return $this->whereCol($this->morph_class::column($col), ...$args);
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

        return $this->andWhereCol($this->morph_class::column($col), ...$args);
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

        return $this->orWhereCol($this->morph_class::column($col), ...$args);
    }

    /**
     * افزودن شرط برای وجود ستون جدول میانی در یکی از عناصر آرایه
     *
     * @return $this
     */
    public function wherePivotIn(string $col, array|Arrayable $array)
    {
        return $this->whereIn($this->morph_class::column($col), $array);
    }

    /**
     * افزودن شرط برای وجود ستون جدول میانی در یکی از عناصر آرایه
     *
     * @return $this
     */
    public function andWherePivotIn(string $col, array|Arrayable $array)
    {
        return $this->andWhereIn($this->morph_class::column($col), $array);
    }

    /**
     * افزودن شرط برای وجود ستون جدول میانی در یکی از عناصر آرایه
     *
     * @return $this
     */
    public function orWherePivotIn(string $col, array|Arrayable $array)
    {
        return $this->orWhereIn($this->morph_class::column($col), $array);
    }

    /**
     * افزودن شرط برای بررسی نال بودن ستون جدول میانی
     *
     * @return $this
     */
    public function wherePivotIsNull(string $col)
    {
        return $this->whereIsNull($this->morph_class::column($col));
    }

    /**
     * افزودن شرط برای بررسی نال بودن ستون جدول میانی
     *
     * @return $this
     */
    public function andWherePivotIsNull(string $col)
    {
        return $this->andWhereIsNull($this->morph_class::column($col));
    }

    /**
     * افزودن شرط برای بررسی نال بودن ستون جدول میانی
     *
     * @return $this
     */
    public function orWherePivotIsNull(string $col)
    {
        return $this->orWhereIsNull($this->morph_class::column($col));
    }

    /**
     * افزودن شرط برای بررسی نال نبودن ستون جدول میانی
     *
     * @return $this
     */
    public function wherePivotIsNotNull(string $col)
    {
        return $this->whereIsNotNull($this->morph_class::column($col));
    }

    /**
     * افزودن شرط برای بررسی نال نبودن ستون جدول میانی
     *
     * @return $this
     */
    public function andWherePivotIsNotNull(string $col)
    {
        return $this->andWhereIsNotNull($this->morph_class::column($col));
    }

    /**
     * افزودن شرط برای بررسی نال نبودن ستون جدول میانی
     *
     * @return $this
     */
    public function orWherePivotIsNotNull(string $col)
    {
        return $this->orWhereIsNotNull($this->morph_class::column($col));
    }


}

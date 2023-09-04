<?php
#auto-name
namespace Mmb\Db\Relation;

use Mmb\Db\QueryBuilder;
use Mmb\Db\Table\Table;
use Mmb\Exceptions\MmbException;
use Mmb\Tools\Text;

/**
 * رابطه چند به یک
 * 
 * `Asset:`
 * - `unsignedBigInt id`
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
 * `Asset::asset():`
 * - `return $this->morphTo('asset');`
 * 
 * @template R
 * @extends Morph<R>
 */
class MorphTo extends Morph
{
    
    protected string $morph_name;
    protected string $morph_type;
    protected string $morph_id;

    /**
     * یک رابطه می سازد
     *
     * @param Table $model
     * @return static<Table>
     */
    public static function makeRelation(Table $model, string $name, string $type = null, string $id = null)
    {
        $type ??= $name . '_type';
        $id ??= $name . '_id';

        $relation = new static;
        $relation->model = $model;
        $relation->morph_name = $name;
        $relation->morph_type = $type;
        $relation->morph_id = $id;
        $relation->init();

        return $relation;
    }

    protected function getValue()
    {
        return $this->first();
    }

    public function getRelationsFor(string $name, array $models)
    {
        $models = arr($models);
        $types = $this->getMorphClassForArr($models->pluck($this->morph_type)->unique());

        foreach($types as $type)
        {
            $modelsThisType = $models->where($this->morph_type, $type);
                
            $ids = $modelsThisType->pluck($this->morph_id)->unique();
            $this->setRelatedTo($ids, 'in');
            
            $this->model = first($modelsThisType);
            $all = $this->all();

            foreach($modelsThisType as $model)
            {
                $model->$name = $all->find($model->{$this->morph_id}, $type::getPrimaryKey());
                $this->setRollback($model->$name, $model);
            }
        }
    }

    public function run($type, $local = array(), $exportStringQuery = false)
    {
        $morphType = $this->getMorphClassFor($this->model->{$this->morph_type});
        if(is_null($morphType))
        {
            $this->andWhereRaw('0');
            return parent::run($type, $local, $exportStringQuery);
        }

        return $this
                ->newQuery()
                ->beforeWhere(function($query) use($morphType)
                {
                    $query->whereRelationRelatedTo($morphType::column($morphType::getPrimaryKey()), $this->model->{$this->morph_id});
                })
                ->without($this->rollback ?? '')
                ->newQueryFrom($morphType)
                ->run($type, $local, $exportStringQuery);
    }

    public function setRelatedFromClass(string $class)
    {
        return $this->setRelatedTo($class::column($this->morph_id), 'col');
    }
    
    public function addHasCondition(QueryBuilder $query, $callback, $operator = ">=", $count = 1, $bool = 'AND')
    {
        throw new MmbException("MorphTo relation don't suppport has()");
    }

}

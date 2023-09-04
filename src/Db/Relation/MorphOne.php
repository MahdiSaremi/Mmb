<?php
#auto-name
namespace Mmb\Db\Relation;

/**
 * رابطه یک به یک مورف
 * 
 * `AssetImage:`
 * - `unsignedBigInt id`
 * 
 * ----
 * 
 * `Asset:`
 * - `unsignedBigInt id`
 * - `enum asset_type`
 * - `unsignedBigInt asset_id`
 * 
 * ----
 * 
 * متد رابطه.
 * 
 * `AssetImage::asset():`
 * - `return $this->morphOne(Asset::class, 'asset');`
 * 
 * @template R
 * @extends MorphOneOrMany<R>
 */
class MorphOne extends MorphOneOrMany
{
    
    protected function getValue()
    {
        return $this->get();
    }

    public function getRelationsFor(string $name, array $models)
    {
        $ids = [];
        foreach($models as $model)
        {
            $ids[] = $model->getPrimaryValue();
        }
        $this->setRelatedTo($ids, 'in');
        
        $all = $this->all();
        foreach($models as $model)
        {
            $model->$name = $all->find($model->getPrimaryValue(), $this->morph_id);
            $this->setRollback($model->$name, $model);
        }
    }

    /**
     * تبدیل رابطه به رابطه یک به چند
     *
     * @return MorphMany<R>
     */
    public function many()
    {
        return $this->newQueryAs(MorphMany::class);
    }

}

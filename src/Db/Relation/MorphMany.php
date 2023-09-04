<?php
#auto-name
namespace Mmb\Db\Relation;

/**
 * رابطه یک به چند مورف
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
 * - `return $this->morphMany(Asset::class, 'asset');`
 * 
 * @template R
 * @extends MorphOneOrMany<R>
 */
class MorphMany extends MorphOneOrMany
{
    
    protected function getValue()
    {
        return $this->all();
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
            $model->$name = $all->where($this->morph_id, $model->getPrimaryValue());
            $this->setRollback($model->$name, $model);
        }
    }

    /**
     * تبدیل رابطه به رابطه یک به یک
     *
     * @return MorphOne<R>
     */
    public function one()
    {
        return $this->newQueryAs(MorphOne::class);
    }

}

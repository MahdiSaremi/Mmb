<?php
#auto-name
namespace Mmb\Db\Relation;

use Closure;
use Mmb\Db\Table\Table;
use Mmb\Db\WhereFacade;
use Mmb\Tools\Text;

/**
 * رابطه یک به چند
 * 
 * `Like:`
 * - `unsignedBigInt id`
 * 
 * ----
 * 
 * `Liker:`
 * - `unsignedBigInt id`
 * - `unsignedBigInt like_id`
 * 
 * ----
 * 
 * متد رابطه.
 * 
 * `Like::likers():`
 * - `return $this->hasMany(Liker::class);`
 * 
 * @template R
 * @extends HasOneOrMany<R>
 */
class HasMany extends HasOneOrMany
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
            $ids[] = $model->{$this->local_primary};
        }
        $this->setRelatedTo($ids, 'in');
        
        $all = $this->all();
        foreach($models as $model)
        {
            $model->$name = $all->where($this->target_column, $model->{$this->local_primary});
            $this->setRollback($model->$name, $model);
        }
    }

    /**
     * تبدیل رابطه به رابطه یک به یک
     *
     * @return HasOne<R>
     */
    public function one()
    {
        return $this->newQueryAs(HasOne::class);
    }

}

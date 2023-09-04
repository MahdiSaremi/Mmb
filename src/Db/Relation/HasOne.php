<?php
#auto-name
namespace Mmb\Db\Relation;

use Closure;
use Mmb\Db\Table\Table;
use Mmb\Db\WhereFacade;
use Mmb\Tools\Text;

/**
 * رابطه یک به یک
 * 
 * `Post:`
 * - `unsignedBigInt id`
 * 
 * ----
 * 
 * `PostImage:`
 * - `unsignedBigInt id`
 * - `unsignedBigInt post_id`
 * 
 * ----
 * 
 * متد رابطه.
 * 
 * `Post::image():`
 * - `return $this->hasOne(PostImage::class);`
 * 
 * @template R
 * @extends HasOneOrMany<R>
 */
class HasOne extends HasOneOrMany
{

    protected $orCreate = null;

    protected function getValue()
    {
        if(is_null($this->orCreate))
        {
            return $this->get();
        }
        else
        {
            return $this->getOrCreate($this->orCreate);
        }
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
            $model->$name = $all->find($model->{$this->local_primary}, $this->target_column);
            $this->setRollback($model->$name, $model);
        }
    }

    /**
     * تبدیل رابطه به رابطه یک به چند
     *
     * @return HasMany<R>
     */
    public function many()
    {
        return $this->newQueryAs(HasMany::class);
    }

    /**
     * اگر مقداری در این رابطه وجود نداشته باشد، آن را ایجاد می کند
     *
     * @return $this
     */
    public function orCreate(array|Closure $data = [])
    {
        $this->orCreate = $data;
        return $this;
    }
    
}

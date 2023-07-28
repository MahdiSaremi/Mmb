<?php
#auto-update
namespace Mmb\Controller\Form;

use Closure;
use Mmb\Controller\StepHandler\Handlable;
use Mmb\Update\Upd;

class Filter
{

    use UpdateFilter;

    /**
     * ایجاد فیلتر جدید
     * 
     * `Filter::filter()->text()->then(function($value) { replyText("ثبت شد"); return $this('back'); })->apply();`
     *
     * @param Upd|null $update
     * @return static
     */
    public static function filter($update = null)
    {
        return (new static($update));
    }

    protected $upd;

    public function __construct(Upd $upd = null)
    {
        if($upd === null)
            $upd = Upd::$this;
        $this->upd = $upd;
    }

    protected $then = [];

    /**
     * بعد از اتمام فیلتر ها صدا زده می شود
     *
     * @param Closure $callback function(&$value) { return Handlable|null; }
     * @return $this
     */
    public function then($callback = null)
    {
        $this->then[] = $callback;
        return $this;
    }

    protected $error;

    /**
     * زمان نمایش خطا صدا زده می شود
     *
     * @param Closure $callback function($error) { replyText($error); }
     * @return $this
     */
    public function error($callback)
    {
        $this->error = $callback;
        return $this;
    }

    /**
     * انجام فیلتر ها و عملیات
     *
     * @return Handlable|null
     */
    public function apply()
    {
        try
        {
            $value = $this->applyFilters($this->upd, true);
            $result = null;
            foreach($this->then as $then)
            {
                $temp = $then($value);
                if($temp !== null)
                    $result = $temp;
            }
            return $result;
        }
        catch(FilterError $error)
        {
            $callback = $this->error;
            if($callback)
                return $callback($error->getMessage());
            else
                response($error->getMessage());
        }
    }

}

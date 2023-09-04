<?php
#auto-name
namespace Mmb\Db;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Mmb\Controller\Controller;
use Mmb\Mapping\Arr;
use Mmb\Mapping\Arrayable;

/**
 * @template M
 * @implements IteratorAggregate<M>
 * @implements ArrayAccess<int,M>
 * @implements Arrayable<M>
 */
class Paginate implements IteratorAggregate, Countable, ArrayAccess, Arrayable
{

    /**
     * لیست مدل های این صفحه
     * 
     * @var Arr<M>
     */
    public Arr $result;

    /**
     * تعداد کل مدل ها
     *
     * @var int|null
     */
    public $paginateCount = null;

    /**
     * تعداد کل صفحات
     *
     * @var int|null
     */
    public $pageCount = null;


    /**
     * @param QueryBuilder<M> $query
     */
    public function __construct(
        QueryBuilder $query,

        /**
         * شماره صفحه
         */
        public int $page = 1,
        
        /**
         * تعداد بر هر صفحه
         */
        public int $perPage = 20,

        /**
         * خطا در صورت عدم وجود
         */
        public ?string $error = "صفحه یافت نشد",
    )
    {
        $this->paginateCount = $query->count();

        $this->result = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->all();

        if(!is_null($error) && $this->result->isEmpty())
        {
            error($error);
        }

        $this->pageCount = ceil($this->paginateCount / $perPage);
    }

    public function getIterator()
    {
        return $this->result->getIterator();
    }

    public function count()
    {
        return $this->result->count();
    }

    public function toArray()
    {
        return $this->result->toArray();
    }

    public function offsetExists($offset)
    {
        return $this->result->offsetExists($offset);
    }
    
    public function offsetGet($offset)
    {
        return $this->result->offsetGet($offset);
    }
    
    public function offsetSet($offset, $value)
    {
        $this->result->offsetSet($offset, $value);
    }
    
    public function offsetUnset($offset)
    {
        $this->result->offsetUnset($offset);
    }

    /**
     * بررسی می کند این صفحه وجود دارد
     *
     * @param integer $page
     * @return boolean
     */
    public function hasPage(int $page)
    {
        return $page > 0 && $page <= $this->pageCount;
    }

    /**
     * بررسی می کند صفحه بعدی وجود دارد
     *
     * @return boolean
     */
    public function hasNext()
    {
        return $this->hasPage($this->page + 1);
    }

    /**
     * بررسی می کند صفحه قبلی وجود دارد
     *
     * @return boolean
     */
    public function hasBefore()
    {
        return $this->hasPage($this->page - 1);
    }

    /**
     * بررسی می کند صفحه، اولین صفحه است
     *
     * @param integer|null $page
     * @return boolean
     */
    public function isFirst(?int $page = null)
    {
        return ($page ?? $this->page) == 1;
    }

    /**
     * بررسی می کند صفحه، آخرین صفحه است
     *
     * @param integer|null $page
     * @return boolean
     */
    public function isLast(?int $page = null)
    {
        return ($page ?? $this->page) == $this->pageCount;
    }

    protected $inline = false;

    /**
     * فعال کردن حالت اینلاین
     *
     * با فعال کردن این قابلیت، دکمه ها بصورت اینلاین ساخته می شوند
     * 
     * @return $this
     */
    public function inline()
    {
        $this->inline = true;
        return $this;
    }

    /**
     * فعال کردن حالت عادی
     *
     * با فعال کردن این قابلیت، دکمه ها بصورت عادی ساخته می شوند
     * 
     * _این ویژگی پیشفرض فعال است_
     * 
     * @return $this
     */
    public function normal()
    {
        $this->inline = false;
        return $this;
    }

    /**
     * ایجاد دکمه پریدن به صفحه
     *
     * @return array
     */
    public function key(string|Controller $controller, string $method, int $page, $text)
    {
        if($this->inline)
        {
            return $controller::keyInline(value($text), $method, $page);
        }
        else
        {
            return $controller::key(value($text), $method, $page);
        }
    }
    
    /**
     * ایجاد دکمه پریدن به صفحه
     * 
     * اگر صفحه وجود نداشت، متن جایگزین نوشته می شود
     *
     * @return array
     */
    public function keyOr(string|Controller $controller, string $method, int $page, $text, $or = '----', ?string $orMethod = null)
    {
        if(!$this->hasPage($page))
        {
            $text = $or;
            $method = $orMethod;
        }

        return $this->key($controller, $method, $page, $text);
    }
    
    /**
     * ایجاد دکمه پریدن به صفحه
     * 
     * اگر صفحه وجود نداشت، نال برگشت داده می شود
     *
     * @return array|null
     */
    public function keyIf(string|Controller $controller, string $method, int $page, $text)
    {
        return
            $this->hasPage($page) ?
                $this->key($controller, $method, $page, $text) :
                null;
    }
    
    
    /**
     * ایجاد دکمه پریدن به صفحه بعدی
     *
     * @return array
     */
    public function keyNext(string|Controller $controller, string $method, $text)
    {
        return $this->key($controller, $method, $this->page + 1, $text);
    }
    
    /**
     * ایجاد دکمه پریدن به صفحه بعدی
     * 
     * اگر صفحه بعدی وجود نداشت، متن جایگزین نوشته می شود
     *
     * @return array
     */
    public function keyNextOr(string|Controller $controller, string $method, $text, $or = '----', ?string $orMethod = null)
    {
        return $this->keyOr($controller, $method, $this->page + 1, $text, $or, $orMethod);
    }
    
    /**
     * ایجاد دکمه پریدن به صفحه بعدی
     * 
     * اگر صفحه بعدی وجود نداشت، نال برگشت داده می شود
     *
     * @return array
     */
    public function keyNextIf(string|Controller $controller, string $method, $text)
    {
        return $this->keyIf($controller, $method, $this->page + 1, $text);
    }
    
    
    /**
     * ایجاد دکمه پریدن به صفحه قبلی
     *
     * @return array
     */
    public function keyBefore(string|Controller $controller, string $method, $text)
    {
        return $this->key($controller, $method, $this->page - 1, $text);
    }
    
    /**
     * ایجاد دکمه پریدن به صفحه قبلی
     * 
     * اگر صفحه قبلی وجود نداشت، متن جایگزین نوشته می شود
     *
     * @return array
     */
    public function keyBeforeOr(string|Controller $controller, string $method, $text, $or = '----', ?string $orMethod = null)
    {
        return $this->keyOr($controller, $method, $this->page - 1, $text, $or, $orMethod);
    }
    
    /**
     * ایجاد دکمه پریدن به صفحه قبلی
     * 
     * اگر صفحه قبلی وجود نداشت، نال برگشت داده می شود
     *
     * @return array
     */
    public function keyBeforeIf(string|Controller $controller, string $method, $text)
    {
        return $this->keyIf($controller, $method, $this->page - 1, $text);
    }
    
    
    /**
     * ایجاد دکمه پریدن به صفحه اول
     *
     * @return array
     */
    public function keyFirst(string|Controller $controller, string $method, $text)
    {
        return $this->key($controller, $method, 1, $text);
    }
    
    /**
     * ایجاد دکمه پریدن به صفحه اول
     * 
     * اگر صفحه اول وجود نداشت، متن جایگزین نوشته می شود
     *
     * @return array
     */
    public function keyFirstOr(string|Controller $controller, string $method, $text, $or = '----', ?string $orMethod = null)
    {
        return $this->keyOr($controller, $method, 1, $text, $or, $orMethod);
    }
    
    /**
     * ایجاد دکمه پریدن به صفحه اول
     * 
     * اگر صفحه اول وجود نداشت، نال برگشت داده می شود
     *
     * @return array
     */
    public function keyFirstIf(string|Controller $controller, string $method, $text)
    {
        return $this->keyIf($controller, $method, 1, $text);
    }
    
    
    /**
     * ایجاد دکمه پریدن به صفحه آخر
     *
     * @return array
     */
    public function keyLast(string|Controller $controller, string $method, $text)
    {
        return $this->key($controller, $method, $this->pageCount, $text);
    }
    
    /**
     * ایجاد دکمه پریدن به صفحه آخر
     * 
     * اگر صفحه آخر وجود نداشت، متن جایگزین نوشته می شود
     *
     * @return array
     */
    public function keyLastOr(string|Controller $controller, string $method, $text, $or = '----', ?string $orMethod = null)
    {
        return $this->keyOr($controller, $method, $this->pageCount, $text, $or, $orMethod);
    }
    
    /**
     * ایجاد دکمه پریدن به صفحه آخر
     * 
     * اگر صفحه آخر وجود نداشت، نال برگشت داده می شود
     *
     * @return array
     */
    public function keyLastIf(string|Controller $controller, string $method, $text)
    {
        return $this->keyIf($controller, $method, $this->pageCount, $text);
    }
    
    /**
     * ردیف آپشن های صفحه بندی را می سازد
     * 
     * این تابع شامل دکمه صفحه قبل و بعد می باشد
     *
     * @param boolean $rtl مشخص می کند چیدمان راست چین باشد
     * @return array
     */
    public function rowSimple(string|Controller $controller, string $method, $before, $next, bool $rtl = false)
    {
        $row = [
            $this->keyBefore($controller, $method, $before),
            $this->keyNext($controller, $method, $next),
        ];

        return $rtl ? array_reverse($row) : $row;
    }

    /**
     * ردیف آپشن های صفحه بندی را می سازد
     * 
     * این تابع شامل دکمه صفحه اول، قبل، بعد و آخر می باشد
     *
     * @param boolean $rtl مشخص می کند چیدمان راست چین باشد
     * @return array
     */
    public function rowSimple2(string|Controller $controller, string $method, $first, $before, $next, $last, bool $rtl = false)
    {
        $row = [
            $this->keyFirst($controller, $method, $first),
            $this->keyBefore($controller, $method, $before),
            $this->keyNext($controller, $method, $next),
            $this->keyLast($controller, $method, $last),
        ];

        return $rtl ? array_reverse($row) : $row;
    }

    /**
     * ردیف آپشن های صفحه بندی را می سازد
     * 
     * این تابع شامل دکمه صفحه اول، لیست صفحات و صفحه آخر می باشد
     * 
     * `<<  2  3  4  [5]  6  7  8  >>`
     *
     * @param boolean $rtl مشخص می کند چیدمان راست چین باشد
     * @return array
     */
    public function rowAdvanced(string|Controller $controller, string $method, $first = '<<', $page = '%', $current = '[%]', $last = '>>', bool $rtl = false, int $pageAround = 3)
    {
        $row = [];

        // First
        $row[] = $this->keyFirst($controller, $method, $this->keyNumber($first, 1));

        // Before
        for(
            $page_number = $this->page - $pageAround;
            $page_number < $this->page;
            $page_number++
        )
        {
            if($this->hasPage($page_number))
            {
                $row[] = $this->key($controller, $method, $page_number, $this->keyNumber($page, $page_number));
            }
        }
        
        // Current
        $row[] = $this->keyLast($controller, $method, $this->keyNumber($current, $this->page));

        // Next
        for(
            $page_number = $this->page + 1;
            $page_number <= $this->page + $pageAround;
            $page_number++
        )
        {
            if($this->hasPage($page_number))
            {
                $row[] = $this->key($controller, $method, $page_number, $this->keyNumber($page, $page_number));
            }
        }

        // Last
        $row[] = $this->keyLast($controller, $method, $this->keyNumber($last, $this->pageCount));

        return $rtl ? array_reverse($row) : $row;
    }

    /**
     * علامت درصد را جایگزین شماره صفحه می کند
     *
     * @return string
     */
    protected function keyNumber($text, $page)
    {
        return str_replace('%', $page, value($text, $page));
    }

}

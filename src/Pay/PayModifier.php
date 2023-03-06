<?php
#auto-name
namespace Mmb\Pay;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Mmb\Tools\Staticable;

class PayModifier implements ArrayAccess, Countable, IteratorAggregate
{
    use Staticable;

    public $info;
    public function __construct(PayInfo $info)
    {
        $this->info = $info;
    }

    public function offsetExists($offset)
    {
        return $this->info->offsetExists($offset);
    }
    public function offsetGet($offset)
    {
        return $this->info->offsetGet($offset);
    }
    public function offsetSet($offset, $value)
    {
        return $this->info->offsetSet($offset, $value);
    }
    public function offsetUnset($offset)
    {
        $this->info->offsetUnset($offset);
    }
    
	public function count()
    {
        return $this->info->count();
	}
	
	public function getIterator()
    {
        return $this->info->getIterator();
	}



    /**
     * زمان موفقیت آمیز بودن پرداخت صدا زده می شود
     * 
     * @param PayInfo $info
     * @return void
     */
    public function paySuccess(PayInfo $info)
    { }
    
    /**
     * قبل از تایید پرداخت جهت اعتبارسنجی صدا زده می شود
     * 
     * @param PayInfo $info
     * @return bool
     */
    public function payValidate(PayInfo $info)
    {
        return true; // Skip
    }

    /**
     * اگر تابع ولیدیت فالس بدهد اجرا می شود
     * 
     * @param PayInfo $info
     * @return mixed
     */
    public function payFailed(PayInfo $info)
    {
        return false; // Cancel event
    }
    
}

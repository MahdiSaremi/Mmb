<?php

namespace Mmb; #auto

use Closure;

class MmbBase
{

    protected array $realData;
    protected ?Mmb $_base = null;

    public function __construct(array $args, ?Mmb $mmb = null)
    {
        $this->realData = $args;
        if(!$mmb) $mmb = Mmb::$this;
        $this->_base = $mmb;
    }

    /**
     * اجرای اسلیپ و برگرداندن خود
     *
     * @param float $seconds
     * @return $this
     */
    public function sleep($seconds)
    {
        sleep($seconds);
        return $this;
    }

    /**
     * مقدار های مشخص شده را در صورت وجود در کلاس ذخیره می کند
     *
     * @param array $data
     * @param array $names
     * @return void
     */
    protected function initFrom(array $data, array $names)
    {
        foreach($names as $dataName => $resultName)
        {
            if(array_key_exists($dataName, $data))
            {
                $value = $data[$dataName];
                if($resultName instanceof Closure)
                {
                    $resultName($value);
                }
                else
                {
                    $this->$resultName = $value;
                }
            }
        }
    }

    public function getRealData()
    {
        return $this->realData;
    }

}

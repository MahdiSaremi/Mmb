<?php
#auto-name
namespace Mmb\Job;
use Mmb\Tools\Staticable;

abstract class CronJobPro
{

    use Staticable;

    /**
     * حداکثر زمانی که می تواند اجرا شود به ثانیه
     *
     * بعد از این زمان بصورت خودکار توسط پی اچ پی کد بسته می شود
     * 
     * @return float
     */
    public abstract function maxTime();

    /**
     * مقدار زمانی که از حداکثر زمان، بصورت خالی باقی می ماند
     * 
     * به عنوان مثال از 60 ثانیه اجرا، 5 ثانیه را زمان آزاد در نظر می گیریم تا اجرای طولانی کد باعث اختلال نشود
     *
     * @return float
     */
    public abstract function freeTime();

    /**
     * مقدار زمان لازم برای هر اجرا
     *
     * @return float
     */
    public abstract function loopTime();

    /**
     * اجرای کرون جاب
     *
     * @return void
     */
    protected abstract function run();


    protected $endTime;
    protected $loopLimit;
    /**
     * اجرای جاب
     *
     * @return void
     */
    public static function job()
    {
        // initialize
        $obj = static::instance();
        $max = $obj->maxTime();
        $loop = $obj->loopTime();
        set_time_limit($max);

        // Loop
        $startTime = microtime(true);
        $obj->endTime = $endTime = $startTime + $max - $obj->freeTime();
        $firstExecute = true;
        $sleep = 0;

        do
        {
            if($firstExecute)
                $firstExecute = false;
            else
            {
                // Sleep
                $startTime += $loop;
                if($startTime >= $endTime)
                    break;
                $sleep = $startTime - microtime(true);
                if($sleep > 0.1)
                    usleep($sleep * 1000000);
            }
            
            // Next job limit
            $obj->loopLimit = $startTime + $loop;
            if($obj->loopLimit < microtime(true) - 0.1)
                continue;

            $obj->run();
        }
        while(microtime(true) < $endTime);
    }

    /**
     * بررسی می کند زمانی برای ادامه دادن این جاب وجود دارد
     *
     * @return boolean
     */
    public function canContinue()
    {
        $now = microtime(true);
        return ($now < $this->loopLimit) && ($now < $this->endTime);
    }
    
}

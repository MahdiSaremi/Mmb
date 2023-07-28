<?php
#auto-name
namespace Mmb\Job;

abstract class JobLoop extends Job
{

    /**
     * مقدار زمان به ثانیه که بعد از آن متوقف می شود
     *
     * @return int
     */
    protected function exitAfter()
    {
        return 20;
    }

    /**
     * تنظیم می کند آیا در هر بار تکرار حلقه ذخیره شود یا خیر
     * در هر صورت بعد از محدودیت زمانی خودکار ذخیره می شود
     *
     * @return bool
     */
    protected function updateEveryTime()
    {
        return true;
    }

    public $initialized = false;

    protected final function run()
    {
        if(!$this->initialized)
        {
            $this->initialized = true;
            $this->start();
        }

        for($startTime = time(); $this->check(); $this->step())
        {
            // Update
            if($this->updateEveryTime())
                $this->update();
            
            // Loop
            $this->loop();

            // Break
            if($this->break)
            {
                break;
            }
            
            // End of this thread
            if(time() - $startTime >= $this->exitAfter())
            {
                $this->step();
                $this->localEnd();
                $this->update();
                return;
            }
        }

        // End of job
        $this->end();
        $this->delete();
    }

    // Tip: for($this->start() ; $this->check() ; $this->step()) will run

    /**
     * مقدار دهی اولیه
     *
     * @return void
     */
    protected function start()
    { }
    /**
     * شرط ادامه ی حلقه
     *
     * @return bool
     */
    protected function check()
    {
        return false;
    }
    /**
     * مرحله حلقه
     *
     * @return void
     */
    protected function step()
    { }

    /**
     * زمان پایان این اجرا، اجرا میشود
     *
     * @return void
     */
    protected function localEnd()
    { }

    /**
     * زمان پایان جاب اجرا می شود
     *
     * @return void
     */
    protected function end()
    { }

    /**
     * در حال تکرار است
     *
     * @return void
     */
    protected abstract function loop();

    public $break;

    /**
     * شکستن حلقه
     *
     * @return void
     */
    protected function break()
    {
        $this->break = true;
    }
    
}

<?php

namespace Mmb\Job; #auto

class Job
{

    /**
     * افزودن جاب
     *
     * @param Job $job
     * @return string
     */
    public static function add($job)
    {
        $i = null;
        $i = time() . rand(0,9999);
        $job->name = $i;
        JobStorage::set('jobs.' . $i, serialize($job));
        return $i;
    }

    /**
     * اجرای تمام جاب ها
     *
     * @return void
     */
    public static function runAll()
    {
        foreach(JobStorage::get('jobs') as $job) {
            $job = unserialize($job);
            if($job->checkCond())
                $job->runJob();
        }
    }

    /**
     * اجرای اولین جاب صف
     *
     * @return void
     */
    public static function runNext()
    {
        $jobs = JobStorage::get('jobs');
        foreach($jobs as $job) {
            $job = array_values($jobs)[0];
            $job = unserialize($job);

            if($job->checkCond()) {
                $job->runJob();
                return;
            }
        }
    }

    
    public $name;

    /**
     * زمان اجرای جاب اجرا می شود
     *
     * @return void
     */
    protected function run()
    {
    }

    /**
     * جاب را اجرا می کند
     *
     * @return void
     */
    public final function runJob()
    {
        $this->run();
        if($this->autoDelete())
            $this->delete();
    }

    /**
     * شرط اجرایی شدن جاب
     *
     * @return bool
     */
    protected function cond()
    {
        return true;
    }

    public final function checkCond()
    {
        return $this->cond();
    }


    /**
     * افزودن جاب به لیست
     *
     * @return string
     */
    public function addJob()
    {
        return self::add($this);
    }

    /**
     * حذف چاب از لیست
     *
     * @return void
     */
    public function delete()
    {
        JobStorage::unset('jobs.' . $this->name);
    }

    /**
     * ذخیره مجدد جاب در لیست
     *
     * @return void
     */
    public function update()
    {
        JobStorage::set('jobs.' . $this->name, serialize($this));
    }

    /**
     * بصورت خودکار جاب بعد از اجرا حذف شود
     *
     * @return bool
     */
    protected function autoDelete()
    {
        return false;
    }

}

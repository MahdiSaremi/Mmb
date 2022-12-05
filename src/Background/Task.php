<?php

namespace Mmb\Background; #auto

use Mmb\Tools\Staticable;

class Task
{

    use Staticable;

    /**
     * زمان اجرای تسک اجرا می شود
     *
     * @return void
     */
    protected function run() {
    }

    /**
     * شبیه سازی و اجرای تسک در پس زمینه
     *
     * @return void
     */
    public function runTask() {
        
        Background::runTask($this);

    }

    /**
     * اجرای تسک بدون پس زمینه
     *
     * @return void
     */
    public function runNow() {

        $this->run();

    }

}

<?php

namespace Mds\Mmb\Update\Webhook; #auto

use Mds\Mmb\Mmb;
use Mds\Mmb\MmbBase;

class Info extends MmbBase
{
    /**
     * @var Mmb
     */
    private $_base;

    /**
     * Webhook url
     *
     * @var string
     */
    public $url;

    /**
     * Pending update count
     * تعداد آپدیت های درون صف
     *
     * @var int
     */
    public $pendings;

    /**
     * آی پی تنظیم شده
     *
     * @var string
     */
    public $ip;

    /**
     * Last error time
     * تاریخ آخرین خطا
     *
     * @var int
     */
    public $lastErrorTime;

    /**
     * Last error message
     * آخرین خطا
     *
     * @var string
     */
    public $lastError;

    /**
     * Max connections
     *
     * @var int
     */
    public $maxConnections;

    /**
     * Allowed updates
     *
     * @var string[]
     */
    public $allowedUpds;

    public function __construct($data, Mmb $base){
        $this->_base = $base;
        $this->url = @$data['url'];
        $this->pendings = @$data['pending_update_count'];
        $this->ip = @$data['ip_address'];
        $this->lastErrorTime = @$data['last_error_date'];
        $this->lastError = @$data['last_error_message'];
        $this->maxConnections = @$data['max_connections'];
        $this->allowedUpds = @$data['allowed_updates'];
    }
}

<?php

namespace Mmb\Update\Webhook; #auto

use Mmb\Mmb;
use Mmb\MmbBase;

class Info extends MmbBase
{
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

    public function __construct(array $args, ?Mmb $mmb = null)
    {
        parent::__construct($args, $mmb);

        $this->initFrom($args, [
            'url' => 'url',
            'pending_update_count' => 'pendings',
            'ip_address' => 'ip',
            'last_error_date' => 'lastErrorTime',
            'last_error_message' => 'lastError',
            'max_connections' => 'maxConnections',
            'allowed_updates' => 'allowedUpds',
        ]);
    }
}

<?php

namespace Mmb\Debug; #auto

use Mmb\Exceptions\MmbException;
use Mmb\Update\Chat\Chat;

class Debug
{

    private static $on = false;

    /**
     * حالت دیباگ برنامه را روشن می کند
     *
     * @return void
     */
    public static function on() {
        self::$on = true;
    }
    
    /**
     * بررسی می کند حالت دیباگ روشن است یا خیر
     *
     * @return bool
     */
    public static function isOn() {
        return self::$on;
    }


    /**
     * گزارش خطا
     *
     * @param string $description
     * @return void
     */
    public static function error($description) {
        if(self::isOn()) {
            if(Chat::$this) {
                Chat::$this->sendMsg([
                    'text' => "<b>Mmb+ Error:</b>\n\n<code>".htmlEncode($description)."</code>",
                    'mode' => 'HTML'
                ]);
            }
            throw new MmbException("Mmb+ Error: " . str_replace("\n", " ", $description));
        }
    }

    /**
     * گزارش هشدار
     *
     * @param string $description
     * @return void
     */
    public static function warning($description) {
        if(self::isOn()) {
            if(Chat::$this) {
                Chat::$this->sendMsg([
                    'text' => "<b>Mmb+ Warning:</b>\n\n<code>".htmlEncode($description)."</code>",
                    'mode' => 'HTML'
                ]);
            }
            error_log("Mmb+ Warning: " . str_replace("\n", " ", $description));
        }
    }

}

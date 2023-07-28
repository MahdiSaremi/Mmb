<?php
#auto-name
namespace Mmb\Core;

use Mmb\Exceptions\MmbException;

/**
 * @deprecated
 */
class StartHandler {

    use Defaultable;

    /**
     * ساخت کد برای لینک استارت ربات
     *
     * @param string $name
     * @param string $data
     * @return string
     */
    public function toCode($name, $data) {

        if($name == 'invite')
            return $this->encodeInviteCode($data);
        else
            return "$name-" . base64_encode($data);
            
    }

    /**
     * تبدیل کد استارت به نام و دیتایی که زمان ساخت قرار داده شده بود
     *
     * @param string $code
     * @return array|false
     */
    public function fromCode($code) {

        $explode = explode("-", $code);

        if(count($explode) == 1) {

            return [ 'invite', $this->decodeInviteCode($code) ];

        }
        elseif(count($explode) == 2) {

            $explode[1] = base64_decode($explode[1]);

            return $explode;

        }
        else {
        
            return false;

        }

    }

    /**
     * انکد کردن کد لینک دعوت
     * 
     * این تابع توسط همین کلاس صدا زده می شود
     * 
     * @param string $code
     * @return string
     */
    public function encodeInviteCode($code)
    {
        return "$code";
    }

    /**
     * دیکد کردن کد لینک دعوت
     * 
     * این تابع توسط همین کلاس صدا زده می شود
     * 
     * @param string $code
     * @return string
     */
    public function decodeInviteCode($code)
    {
        return $code;
    }

    /**
     * ساخت لینک کامل استارت
     *
     * @param string $name
     * @param string $data
     * @return string
     */
    public function createLink($name, $data) {

        $username = config('bot.username');
        if(!$username)
            throw new MmbException("Link generator required 'bot.username' config");

        $username = trim($username, '@');
        $code = $this->toCode($name, $data);

        return "https://t.me/$username?start=" . urlencode($code);

    }

}

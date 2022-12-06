<?php

namespace Mmb\Handlers; #auto

use Mmb\Mmb;

/**
 * کلاسی که اطلاعات درخواست های ام ام بی به تلگرام را در خود دارد
 */
class Request {

    use Defaultable;


    /**
     * ام ام بی تارگت
     *
     * @var \Mmb
     */
    public $mmb;

    /**
     * توکن ربات
     *
     * @var string
     */
    protected $token;

    /**
     * متد
     *
     * @var string
     */
    public $method;

    /**
     * ورودی ها
     *
     * @var array
     */
    public $args;

    /**
     * نادیده گرفتن خطا
     *
     * @var boolean
     */
    public $ignoreError = false;

    public function __construct(Mmb $mmb, string $token, string $method, array $args)
    {
        
        $this->mmb = $mmb;
        $this->token = $token;
        $this->method = $method;
        $this->args = $args;

    }

    /**
     * ارسال درخواست به تلگرام
     *
     * @return \stdClass|array|false
     */
    public function request($associative = false) {

        $url = "https://api.telegram.org/bot".$this->token."/".$this->method;

        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, $this->args);

        $this->curlSetup($request);

        $responce = curl_exec($request);
        if (curl_error($request)) {

            return false;

        }
        else {

            $result = json_decode($responce, $associative);
            return $result;

        }

    }

    public function curlSetup($curl)
    {
    }

    /**
     * تحلیل ورودی های ام ام بی
     *
     * @return void
     */
    public function parseArgs() {

        ArgsParser::defaultStatic()->parse( $this );

    }

    private $lowerMethod;

    /**
     * گرفتن متد با حروف کوچک
     *
     * @return string
     */
    public function lowerMethod() {

        if($this->lowerMethod)
            return $this->lowerMethod;
        
        return $this->lowerMethod = strtolower($this->method);

    }

}

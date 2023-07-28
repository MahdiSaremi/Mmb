<?php
#auto-name
namespace Mmb\Core;

use Closure;
use Mmb\Listeners\HasListeners;
use Mmb\Listeners\HasNormalStaticListeners;
use Mmb\Listeners\HasStaticListeners;
use Mmb\Mmb;

/**
 * کلاسی که اطلاعات درخواست های ام ام بی به تلگرام را در خود دارد
 */
class Request
{

    use Defaultable;


    /**
     * ام ام بی تارگت
     *
     * @var Mmb
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
    public function request($associative = false)
    {
        static::invokeListeners('requesting', [ $this ]);

        $url = "https://api.telegram.org/bot" . $this->token . "/" . $this->method;

        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, $this->args);

        $this->curlSetup($request);
        static::invokeListeners('setuping', [ $this, $request ]);

        $response = curl_exec($request);
        if (curl_error($request))
        {
            return false;
        }
        else
        {
            static::invokeListeners('requested', [ $this ]);

            $result = json_decode($response, $associative);
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
    public function parseArgs()
    {
        ArgsParser::defaultStatic()->parse( $this );
    }

    private $lowerMethod;

    /**
     * گرفتن متد با حروف کوچک
     *
     * @return string
     */
    public function lowerMethod()
    {
        if($this->lowerMethod)
            return $this->lowerMethod;
        return $this->lowerMethod = strtolower($this->method);
    }

    use HasNormalStaticListeners;

    /**
     * افزودن شنونده ای که قبل از اجرای درخواست صدا زده می شود
     *
     * @param Closure $callback `function(Request $request)`
     * @return void
     */
    public static function requesting(Closure $callback)
    {
        static::listen(__FUNCTION__, $callback);
    }

    /**
     * افزودن شنونده ای که بعد از اجرای درخواست صدا زده می شود
     *
     * @param Closure $callback `function(Request $request)`
     * @return void
     */
    public static function requested(Closure $callback)
    {
        static::listen(__FUNCTION__, $callback);
    }

    /**
     * افزودن شنونده ای که در زمان نصب کارل صدا زده می شود
     *
     * @param Closure $callback `function(Request $request, $curl)`
     * @return void
     */
    public static function setuping(Closure $callback)
    {
        static::listen(__FUNCTION__, $callback);
    }

}

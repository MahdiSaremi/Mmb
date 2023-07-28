<?php

namespace Mmb;

use Exception;
use Mmb\Exceptions\MmbException;
use Mmb\Update\Bot\BotCmd;
use Mmb\Update\Chat\Chat;
use Mmb\Update\Chat\Invite;
use Mmb\Update\Chat\Member;
use Mmb\Update\Message\Data\StickerSet;
use Mmb\Update\Message\Data\TelFile;
use Mmb\Update\Message\Msg;
use Mmb\Update\Upd;
use Mmb\Update\User\Profiles;
use Mmb\Update\User\UserInfo;
use Mmb\Update\Webhook\Info as WebhookInfo;
use Mmb\Update\Exceptions;
use Mmb\Update\Exceptions\TelRequestError;

// Telegram IP
if(isset($_SERVER['REMOTE_ADDR']))
{
    $telegram_ip_ranges = [
        ['lower' => '149.154.160.0', 'upper' => '149.154.175.255'],
        ['lower' => '91.108.4.0',    'upper' => '91.108.7.255'],
    ];

    $ip_dec = (float) sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
    $ok=false;
    foreach ($telegram_ip_ranges as $telegram_ip_range)
    {
        $lower_dec = (float) sprintf("%u", ip2long($telegram_ip_range['lower']));
        $upper_dec = (float) sprintf("%u", ip2long($telegram_ip_range['upper']));
        if ($ip_dec >= $lower_dec and $ip_dec <= $upper_dec)
        {
            $ok=true;
            break;
        }
    }
    define('TELEGRAM_IP', $ok);
    unset($ok, $ip_dec, $telegram_ip_ranges, $telegram_ip_range, $lower_dec, $upper_dec);
}
else define('TELEGRAM_IP', false);


// Classes

class Mmb extends MmbBase implements \Serializable
{

    /**
     * شی اصلی این کلاس
     * 
     * @var static
     */
    public static $this;
    public static function this()
    {
        return static::$this;
    }

    
    public static $HARD_ERROR = true;
    public static $LOG = true;
    public static $_BOTS = [];
    public const VERSION = '4.0';
    private $_token;

    /**
     * مقدار دهی و ساخت کلاس ربات
     *
     * @param string $token
     */
    function __construct(string $token)
    {
        $token = trim($token);
        $this->_token = $token;

        self::$_BOTS[] = $this;
        if (!self::$this)
            self::$this = $this;
    }
    
    /**
     * ارسال درخواست به API تلگرام با متد و پارامتر های عادی
     *
     * @param string $method
     * @param array $args
     * @return \stdClass|false
     */ 
    public function bot($method, array $args = [])
    {
        // Edit method
        $method = str_replace(["-", "_", " ", "\n", "\t", "\r"], '', $method);

        // Request
        $request = Core\Request::defaultNew($this, $this->_token, $method, $args);
        return $request->request();
    }
    
    /**
     * ارسال درخواست به ای پی آی تلگرام با متد عادی و پارامتر های ام.ام.بی
     *
     * @param string $method
     * @param array $args
     * @return array|false
     */
    public function call($method, array $args = [])
    {
        // Edit method
        $method = str_replace(["-", "_", " ", "\n", "\t", "\r"], '', $method);

        // Run listener
        // if(!Listeners::__runMmbReq($method, $args)) {
        //     return false;
        // }

        // Request
        $request = Core\Request::defaultNew($this, $this->_token, $method, $args);
        $request->parseArgs();

        // Response
        $response = $request->request(true);

        if (!$response)
        {
            // Connection error
            $des = "Connection error";
        }
        else
        {
            if($response['ok'] === true)
            {
                // Success result
                // Listeners::__runMmbReqEnd($response['result'], $method, $args);
                return $response['result'];
            }
            else
            {
                // Telegram error
                $this->getRequestException($response);
                $des = "Telegram error: ".$response['description'] . ": on $method";
            }
        }

        // Error
        if(!$request->ignoreError)
            throw new TelRequestError($des, $response['error_code'] ?? 0);

        return false;
    }

    public function getRequestException($response)
    {
        $target = [
            Exceptions\TelBadRequestError::$error_code => [
                Exceptions\TelArgEmptyError::class,
                Exceptions\TelChatNotFound::class,
                Exceptions\TelInvalidError::class,
                Exceptions\TelNotSpecifiedError::class,

                Exceptions\TelBadRequestError::class,
            ],
            Exceptions\TelUnauthorizedError::$error_code => [
                Exceptions\TelUnauthorizedError::class,
            ],
            Exceptions\TelNotFoundError::$error_code => [
                Exceptions\TelNotFoundError::class,
            ],
        ];
        
        foreach($target[$response['error_code']] ?? [] as $class)
        {
            if(method_exists($class, 'match'))
            {
                if(!$class::match($response['description']))
                    continue;
            }

            return new $class($response['description'], $response['error_code']);
        }
    }

    public $loading_update = false;

    /**
     * دریافت آپدیت ارسال شده
     *
     * @return Upd|null|false
     */
    public function getUpd() {

        // Post input
        $input = @file_get_contents("php://input");
        if (!$input)
            return false;
        
        // Decode input
        $update = @json_decode($input, true);
        if (!$update)
            return false;

        // Load update
        try {
            $this->loading_update = true;
            return new Upd($update, $this);
        }
        finally {
            $this->loading_update = false;
        }
        
    }

    /**
     * این لینک را برای وبهوک تنظیم می کند
     *
     * @param array $array
     * @return bool
     */
    public function setWebhookThis(array $args = []) {

        // Find current url
        $uri = @$_SERVER['SCRIPT_URI'];
        if($uri == "")
            $dm = "https://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];
        else
            $dm = str_replace(["http://","Http://"], "https://", $uri);

        if(strlen($dm) > 10) {

            // Setwebhook
            $args['url'] = $dm;
            return $this->setWebhook($args);

        }

        return false;

    }
    
    
    private $_first = true;
    private $_offset = null;
    /**
     * ارسال درخواست به تلگرام و دریافت آپدیت ها
     * 
     * اگر ورودی ای قرار ندهید، مقدار افست را بصورت خودکار، توسط آخرین درخواستی که دادید محاسبه می کند
     * به این طریق می توانید با حلقه بی نهایت آپدیت های پشت سر هم و غیر تکراری را بگیرید
     * 
     * توجه: این تابع نیاز دارد که وبهوکی نداشته باشید، بنابر این وبهوک شما را حذف می کند
     *
     * @param int $offset
     * @param int $limit
     * @param array $filter
     * @return Upd[]
     */
    public function getUpds($offset = false, $limit = 10, $filter = null){

        // Delete webhook
        if ($this->_first) {
            $web = $this->getWebhook();
            if ($web->url) {
                $this->delWebhook();
            }
            $this->_first = false;
        }

        // Auto offset
        if($offset == false){
            if($this->_offset !== null)
                $offset = $this->_offset;
            else
                $offset = -1;
        }
        
        $upds = $this->call('getupdates', [
            'offset' => $offset,
            'limit' => $limit,
            'filter' => $filter
        ]);
        if (!$upds)
            return [];

        try {
            $this->loading_update = true;
            $updates = [];
            foreach ($upds as $upd) {
                $upd = new Upd($upd, $this);
                //if(!$this->updListenersRun($x))
                //    continue;
                $this->_offset = $upd->id + 1;
                $updates[] = $upd;
            }
            return $updates;
        }
        finally {
            $this->loading_update = false;
        }
    }
    
    /**
     * گرفتن اطلاعات عمومی ربات
     *
     * @param array $args
     * @return UserInfo|false
     */
    public function getMe(array $args = []){

        return objOrFalse(UserInfo::class, $this->call('getme', $args), $this);

    }

    public function delWebhook(array $args = []) {

        return $this->call('deletewebhook', $args);

    }
    
    /**
     * پاسخ به کالبک
     * 
     * ورودی ها:
     *  id => Callback id | آیدی کالبک
     *  text => Text for show | متن نمایشی
     *  alert => Show alert | نمایش پیغام
     * 
     * @param array $args
     * @return bool
     */
    public function answerCallback(array $args){

        return $this->call('answercallbackquery', $args);

    }
    
    /**
     * ارسال پیام
     * 
     * ورودی ها:
     *  id => Chat id | آیدی چت
     *  text => Text | متن
     *  mode => Parse mode | مد متن
     *  key => Keyboard | دکمه ها
     * 
     * @param array $args
     * @return Msg|false
     */
    public function sendMsg(array $args)
    {
        if(isset($args['type']))
        {
            return $this->send($args);
        }

        return objOrFalse(Msg::class, $this->call('sendmessage', $args), $this);
    }
    
    /**
     * حذف پیام
     * 
     * ورودی ها:
     *  id => Chat id | آیدی چت
     *  msg => Message id | آیدی پیام
     * 
     * @param array $args
     * @return bool
     */
    public function delMsg(array $args)
    {
        return $this->call('deletemessage', $args);
    }

    /**
     * ارسال رسانه
     * 
     * ورودی ها:
     *  id => Chat id | آیدی چت
     *  text => Caption | متن توضیحات
     *  media => Media | مدیا
     *  mode => Parse mode | مد متن
     *  key => Keyboard | دکمه ها
     * 
     * @param array $args
     * @return Msg|false
     */
    public function sendMedia(array $args)
    {
        return objOrFalse(Msg::class, $this->call('sendmedia', $args), $this);
    }
    
    /**
     * باز ارسال پیام بدون نام، می توانید محتویات مثل کپشن را جایگزین نیز کنید
     * 
     * ورودی ها:
     *  id => Chat id | آیدی چت
     *  from => From chat id | آیدی چتی که پیام در آن است
     *  msg => Message id | آیدی عددی پیام
     * 
     * @param array $args
     * @return Msg|false
     */
    public function copyMsg(array $args)
    {
        $r = $this->call('copymessage', $args);
        if($r) {
            if(!isset($r['chat'])){
                $r['chat'] = [
                    'id' => $args['chat'] ?? $args['id'] ?? $args['chat_id'] ?? $args['chatID'] ?? 0
                ];
            }
            return new Msg($r, $this);
        }
        else
            return false;
    }

    /**
     * باز ارسال پیام
     * 
     * ورودی ها:
     *  id => Chat id | آیدی چت
     *  from => From chat id | آیدی چتی که پیام در آن است
     *  msg => Message id | آیدی عددی پیام
     * 
     * @param array $args
     * @return Msg|false
     */
    public function forwardMsg(array $args)
    {
        return objOrFalse(Msg::class, $this->call('forwardmessage', $args), $this);
    }
    
    /**
     * ارسال آلبوم
     * 
     * ورودی ها:
     *  id => Chat id | آیدی چت
     *  text => Caption | متن توضیحات
     *  medias => Medias | مدیا ها
     *  mode => Parse mode | مد متن
     *  key => Keyboard | دکمه ها
     * 
     * @param array $args
     * @return Msg|false
     */
    public function sendMedias(array $args)
    {
        return objOrFalse(Msg::class, $this->call('sendmediagroup', $args), $this);
    }
    
    /**
     * ارسال هر چیز
     * 
     * ورودی ها:
     *  id => Chat id | آیدی چت
     *  type => Type | نوع پیام
     *  text => Caption or text | متن توضیحات یا متن اصلی
     *  val => Value | مقدار
     *  mode => Parse mode | مد متن
     *  key => Keyboard | دکمه ها
     * 
     * @param string|array $type
     * @param array $args
     * @return Msg|false
     */
    public function send($type, array $args = [])
    {
        // send([ ... ])
        if(is_array($type))
        {
            $args = array_merge($type, $args);
            $type = $args['type'] ?? 'text';
            unset($args['type']);
        }
        $type = strtolower($type);
        
        // Text message
        if($type == "text")
        {
            unset($args['val'], $args['value']);
            return $this->sendMsg($args);
        }

        // Copy message
        elseif($type == 'copy')
        {
            return $this->copyMsg($args);
        }

        // Forward message
        elseif($type == 'for' || $type == 'forward')
        {
            return $this->forwardMsg($args);
        }

        // Other message
        else
        {
            if($type == "doc")
                $type = "Document";
            elseif($type == "anim")
                $type = "animation";

            if(isset($args['val']))
            {
                $args[strtolower($type)] = $args['val'];
                unset($args['val']);
            }
            elseif(isset($args['value']))
            {
                $args[strtolower($type)] = $args['value'];
                unset($args['value']);
            }

            return objOrFalse(Msg::class, $this->call('send'.$type, $args), $this);
        }
    }

    /**
     * ارسال تاس
     *
     * @param array $args
     * @return Msg|false
     */
    public function sendDice($args)
    {
        $args = maybeArray([
            'chat' => $args,
        ]);

        return objOrFalse(Msg::class, $this->call('senddice', $args), $this);
    }
    
    /**
     * ارسال نظرسنجی
     *
     * @param array $args
     * @return Msg|false
     */
    public function sendPoll(array $args)
    {
        return objOrFalse(Msg::class, $this->call('sendpoll', $args), $this);
    }
    
    public const ACTION_TYPING = 'typing';
    public const ACTION_UPLOAD_PHOTO = 'upload_photo';
    public const ACTION_UPLOAD_VIDEO = 'upload_video';
    public const ACTION_UPLOAD_VIDEO_NOTE = 'upload_video_note';
    public const ACTION_UPLOAD_VIOCE = 'upload_voice';
    public const ACTION_UPLOAD_DOC = 'upload_document';
    public const ACTION_RECORD_VIDEO = 'record_video';
    public const ACTION_RECORD_VIDEO_NOTE = 'record_video_note';
    public const ACTION_RECORD_VIOCE = 'record_voice';
    public const ACTION_CHOOSE_STICKER = 'choose_sticker';
    public const ACTION_FIND_LOCATION = 'find_location';

    /**
     * ارسال حالت چت
     *
     * @param int|array|mixed $chat
     * @param string $action
     * @return bool
     */
    public function action($chat, $action = 'typing')
    {
        $args = maybeArray([
            'chat' => $chat,
            'action' => $action
        ]);

        return $this->call('sendchataction', $args);
    }
    
    /**
     * حذف ممبر گروه یا کانال
     *
     * @param mixed $chat
     * @param mixed $user
     * @param int $until
     * @return bool
     */
    public function kick($chat, $user = null, $until = null){

        $args = maybeArray([
            'chat' => $chat,
            'user' => $user,
            'until' => $until,
        ]);

        return $this->call('banChatMember', $args);

    }

    /**
     * حذف ممبر گروه یا کانال
     *
     * @param mixed $chat
     * @param mixed $user
     * @param int $until
     * @return bool
     */
    public function ban($chat, $user = null, $until = null)
    {
        $args = maybeArray([
            'chat' => $chat,
            'user' => $user,
            'until' => $until
        ]);
        
        return $this->call('banchatmember', $args);
    }
    
    /**
     * گرفتن تصاویر پروفایل کاربر
     *
     * @param mixed $user
     * @param int $offset
     * @param int $limit
     * @return Profiles|false
     */
    public function getUserProfs($user, $offset = null, $limit = null)
    {
        $args = maybeArray([
            'user' => $user,
            'offset' => $offset,
            'limit' => $limit,
        ]);
        
        return objOrFalse(Profiles::class, $this->call('getuserprofilephotos', $args), $this);
    }
    
    /**
     * گرفتن اطلاعات فایل
     *
     * @param string|object $id
     * @return TelFile|false
     */
    public function getFile($id){

        $args = maybeArray([
            'id' => $id,
        ]);
        
        return objOrFalse(TelFile::class, $this->call('getfile', $args), $this);
        
    }
    
    /**
     * رفع مسدودیت کاربر در گروه یا کانال
     *
     * @param mixed $chat
     * @param mixed $user
     * @return bool
     */
    public function unban($chat, $user = null){

        $args = maybeArray([
            'chat' => $chat,
            'user' => $user,
        ]);

        return $this->call('unbanchatmember', $args);
        
    }
    
    /**
     * محدود کردن کاربر
     *
     * @param mixed $chat
     * @param mixed $user
     * @param array $per
     * @param int $until
     * @return bool
     */
    public function restrict($chat, $user = null, $per = null, $until = null){

        $args = maybeArray([
            'chat' => $chat,
            'user' => $user,
            'per' => $per,
            'until' => $until,
        ], 'per');
        
        return $this->call('restrictchatmember', $args);
        
    }
    
    /**
     * ترفیع دادن به کاربر
     *
     * @param mixed $chat
     * @param mixed $user
     * @param array $per
     * @return bool
     */
    public function promote($chat, $user = null, $per = []){

        $args = maybeArray([
            'chat' => $chat,
            'user' => $user,
            'per' => $per,
        ], 'per');
        
        return $this->call('promoteChatMember', $args);

    }
    
    /**
     * تنظیم دسترسی های گروه
     *
     * @param mixed $chat
     * @param array $per
     * @return bool
     */
    public function setChatPer($chat, $per = []){

        $args = maybeArray([
            'chat' => $chat,
            'per' => $per
        ], 'per');
        
        return $this->call('setchatpermissions', $args);

    }
    
    /**
     * تنظیم عکس گروه یا کانال
     *
     * @param mixed $chat
     * @param mixed $photo
     * @return bool
     */
    public function setChatPhoto($chat, $photo = null){

        $args = maybeArray([
            'chat' => $chat,
            'photo' => $photo,
        ]);
        
        return $this->call('setchatphoto', $args);

    }
    
    /**
     * گرفتن لینک دعوت
     *
     * @param mixed $chat
     * @return string|false
     */
    public function getInviteLink($chat){

        $args = maybeArray([
            'chat' => $chat,
        ]);

        return $this->call('exportchatinvitelink', $args);

    }

    /**
     * ساخت لینک دعوت
     * [chat-name-expire-limit-joinReq]
     *
     * @param array $args
     * @return Invite|false
     */
    function createInviteLink(array $args)
    {
        $r = $this->call('createchatinvitelink', $args);
        if(!$r)
            return false;
        return new Invite($r, $args['chat'] ?? $args['chat_id'] ?? $args['chatid'] ?? null, $this);
    }

    /**
     * ویرایش لینک دعوت
     * [chat-link-name-expire-limit-joinReq]
     *
     * @param array $args
     * @return Invite|false
     */
    public function editInviteLink(array $args)
    {
        $r = $this->call('editchatinvitelink', $args);
        if(!$r)
            return false;
        return new Invite($r, $args['chat'] ?? $args['chat_id'] ?? $args['chatid'] ?? null, $this);
    }
    
    /**
     * منقضی کردن لینک دعوت
     * [chat-link]
     *
     * @param mixed $chat
     * @param string $link
     * @return Invite|false
     */
    public function revokeInviteLink($chat, $link = null){
        
        $args = maybeArray([
            'chat' => $chat,
            'link' => $link
        ]);

        $r = $this->call('revokechatinvitelink', $args);
        if(!$r)
            return false;
        return new Invite($r, $chat['chat'] ?? $chat['chat_id'] ?? $chat['chatid'] ?? null, $this);
    }

    /**
     * تایید درخواست عضویت توسط لینک
     *
     * @param mixed $chat
     * @param mixed $user
     * @return bool
     */
    public function approveJoinReq($chat, $user = null){
        
        $args = maybeArray([
            'chat' => $chat,
            'user' => $user
        ]);

        return $this->call('approvechatjoinrequest', $args);

    }

    /**
     * رد کردن درخواست عضویت توسط لینک
     *
     * @param mixed $chat
     * @param mixed $user
     * @return bool
     */
    public function declineJoinReq($chat, $user = null){
        
        $args = maybeArray([
            'chat' => $chat,
            'user' => $user,
        ]);

        return $this->call('declinechatjoinrequest', $args);

    }

    /**
     * حذف عکس گروه
     *
     * @param mixed $chat
     * @return bool
     */
    public function delChatPhoto($chat){

        $args = maybeArray([
            'chat' => $chat
        ]);

        return $this->call('deletechatphoto', $args);

    }
    
    /**
     * تنظیم عنوان گروه یا کانال
     *
     * @param mixed $chat
     * @param string $title
     * @return bool
     */
    public function setChatTitle($chat, $title = ""){
        
        $args = maybeArray([
            'chat' => $chat,
            'title' => $title
        ]);

        return $this->call('setchattitle', $args);

    }
    
    /**
     * تنظیم توضیحات گروه یا کانال
     *
     * @param mixed $chat
     * @param string $des
     * @return bool
     */
    public function setChatDes($chat, $des = ""){

        $args = maybeArray([
            'chat' => $chat,
            'des' => $des
        ]);
        
        return $this->call('setchatdescription', $args);

    }
    
    /**
     * سنجاق کردن پیام
     *
     * @param mixed $chat
     * @param mixed $msg
     * @return bool
     */
    public function pinMsg($chat, $msg = null){

        $args = maybeArray([
            'chat' => $chat,
            'msg' => $msg,
        ]);

        return $this->call('pinchatmessage', $args);

    }
    
    /**
     * برداشتن سنجاق پیام
     *
     * @param mixed $chat
     * @param mixed $msg
     * @return bool
     */
    public function unpinMsg($chat, $msg = null){

        $args = maybeArray([
            'chat' => $chat,
            'msg' => $msg,
        ]);

        return $this->call('unpinchatmessage', $args);

    }
    
    /**
     * برداشتن تمام پیام های سنجاق شده
     *
     * @param mixed $chat
     * @return bool
     */
    public function unpinAll($chat){

        $args = maybeArray([
            'chat' => $chat,
        ]);
        
        return $this->call('unpinallchatmessages', $args);

    }
    
    /**
     * ترک گروه یا کانال
     *
     * @param mixed $chat
     * @return bool
     */
    public function leave($chat){

        $args = maybeArray([
            'chat' => $chat,
        ]);
        
        return $this->call('leavechat', $args);

    }
    
    /**
     * گرفتن اطلاعات چت
     *
     * @param mixed $chat
     * @return Chat|false
     */
    public function getChat($chat){

        $args = maybeArray([
            'chat' => $chat,
        ]);
        
        return objOrFalse(Chat::class, $this->call('getchat', $args), $this);

    }
    
    /**
     * گرفتن لیست ادمین ها
     *
     * @param mixed $chat
     * @return Member[]|false
     */
    public function getChatAdmins($chat){

        $args = maybeArray([
            'chat' => $chat
        ]);

        $res = $this->call('getchatadministrators', $args);
        if(!$res)
            return false;

        $array = [];
        foreach($res as $one)
            $array[] = new Member($one, $this);
        return $array;

    }
    
    /**
     * گرفتن تعداد اعضای چت
     *
     * @param mixed $chat
     * @return int|false
     */
    public function getChatMemberNum($chat){

        $args = maybeArray([
            'chat' => $chat
        ]);
        
        return $this->call('getchatmembercount', $args);

    }
    
    /**
     * گرفتن تعداد اعضای چت
     *
     * @param mixed $chat
     * @return int|false
     */
    public function getChatMemberCount($chat){

        return $this->getChatMemberNum($chat);

    }
    
    /**
     * گرفتن اطلاعات یک کاربر در چت
     *
     * @param mixed $chat
     * @param mixed $user
     * @return Member|false
     */
    public function getChatMember($chat, $user=null)
    {
        $args = maybeArray([
            'chat' => $chat,
            'user' => $user,
        ]);

        return objOrFalse(Member::class, $this->call('getchatmember', $args), $this);
    }
    
    /**
     * تنظیم بسته استیکر گروه
     *
     * @param mixed $chat
     * @param mixed $setName
     * @return bool
     */
    public function SetChatStickerSet($chat, $setName = null){
        if(gettype($chat)=="array")
            return $this->call('setchatstickerset', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        if(gettype($setName)=="object")
            $setName = $setName->setName;
        return $this->call('setchatstickerset', ['id'=>$chat, 'setName'=>$setName]);
    }
    
    /**
     * حذف بسته استیکر گروه
     *
     * @param mixed $chat
     * @return bool
     */
    public function delChatStickerSet($chat){

        $args = maybeArray([
            'chat' => $chat
        ]);
        
        return $this->call('deletechatstickerset', $args);
        
    }
    
    /**
     * تنظیم کامند های ربات
     *
     * @param mixed $cmds
     * @return bool
     */
    public function setMyCmds($cmds){

        if(isset($cmds['cmds']))
            $cmds = $cmds['cmds'];

        $commands = [];
        foreach($cmds as $command){

            if(gettype($command)=="object")
                $cm = $command->toAr();
            else
                $cm = filterArray($command, [
                    'cmd' => "command",
                    'command' => "command",
                    'des' => "description",
                    'description' => "description",
                ]);
            
            $commands[] = $cm;

        }

        return $this->call('setmycommands', ['cmds' => $commands]);

    }
    
    /**
     * گرفتن کامند های ربات
     *
     * @param array $args
     * @return BotCmd[]|false
     */
    public function getMyCmds(array $args = []){

        $res = $this->call('getmycommands', $args);
        if(!$res)
            return false;

        $commands = [];
        foreach($res as $cmd)
            $commands[] = new BotCmd($cmd, $this);

        return $commands;

    }
    
    /**
     * پاسخ به اینلاین کوئری
     * 
     * @param array $args
     * @return bool
     */
    public function answerInline(array $args){

        return $this->call('answerinlinequery', $args);

    }
    
    /**
     * ویرایش متن پیام
     *
     * @param array $args
     * @return Msg|false
     */
    public function editMsgText(array $args){

        return objOrFalse(Msg::class, $this->call('editmessagetext', $args), $this);
        
    }
    
    /**
     * ویرایش توضیحات زیر پیام
     *
     * @param array $args
     * @return Msg|false
     */
    public function editMsgCaption($args)
    {
        $args = maybeArray([
            'caption' => $args
        ]);

        if(isset($args['text']))
        {
            $args['caption'] = $args['text'];
            unset($args['text']);
        }

        return objOrFalse(Msg::class, $this->call('editmessagecaption', $args), $this);
    }
    
    /**
     * ویرایش رسانه پیام
     *
     * @param array $args
     * @return Msg|false
     */
    public function editMsgMedia(array $args)
    {
        return objOrFalse(Msg::class, $this->call('editmessagemedia', $args), $this);
    }
    
    /**
     * ویرایش دکمه های پیام(دکمه های شیشه ای)
     *
     * @param array $args
     * @return Msg|false
     */
    public function editMsgKey($args)
    {
        return objOrFalse(Msg::class, $this->call('editmessagereplymarkup', $args), $this);
    }
    
    /**
     * گرفتن بسته استیکر
     *
     * @param mixed $setName
     * @return StickerSet|false
     */
    public function getStickerSet($setName)
    {
        $args = maybeArray([
            'name' => $setName,
        ]);
        
        return objOrFalse(StickerSet::class, $this->call('getstickerset', $args), $this);
    }
    
    /**
     * دانلود فایل تلگرامی با مسیر آن
     *
     * @param string $path
     * @param string $paste
     * @return bool
     */
    public function copyByFilePath($path, $paste)
    {
        return copy("https://api.telegram.org/file/bot" . $this->_token . "/" . $path, $paste);
    }

    /**
     * تنظیم وبهوک
     *
     * @param mixed $url
     * @param bool $drop حذف آپدیت های درون صف
     * @return bool
     */
    public function setWebhook($url, $drop = null)
    {
        $args = maybeArray([
            'url' => $url,
            'drop' => $drop
        ]);
        
        return $this->call('setwebhook', $args);
    }

    /**
     * گرفتن اطلاعات وبهوک
     *
     * @param array $args
     * @return WebhookInfo|false
     */
    public function getWebhook(array $args = [])
    {
        return objOrFalse(WebhookInfo::class, $this->call('getwebhookinfo', $args), $this);
    }

    
    public function serialize()
    {
        return "[Mmb]";
    }

    public function unserialize($serialized)
    {
        $this->_token = self::$this ? self::$this->_token : null;
    }
}

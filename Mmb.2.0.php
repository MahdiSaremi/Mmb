<?php

// Copyright (C): t.me/MMBlib

define('MMB_VERSION', '2.0', true);

// Copyright (C): t.me/MMBlib

class Mmb{
    
    // Setings and private values:
    public static $HARD_ERROR = true;
    public static $LOG = true;
    private static $_DB;
    private static $_DB_TABLE;
    public static $_BOTS = [];
    public const VERSION = MMB_VERSION;
    private $_token;
    public $SWebhook = true;
    public $SUpdateF = 0;

    /**
     * Initialize new MMB object
     * مقدار دهی و ساخت یک شی MMB
     *
     * @param string $token
     */
    function __construct(string $token){
        $token = trim($token);
        $this->_token = $token;
        $this->_c = false;
        //$this->mmb_dir(true);
        self::$_BOTS[] = $this;
    }
    
    public $_c = false;
    /**
     * Enable or disable MMB dir
     * تنظیم فعال یا غیر فعال بودن پوشه ام.ام.بی
     * 
     * (تابع منسوخ شده)
     *
     * @param bool $enabled
     * @return void
     */
    function mmb_dir(bool $enabled){
        $this->_c = $enabled;
        if($enabled)
            if(!file_exists("MMB")){
                mkdir("MMB");
                file_put_contents("MMB/index.php", "<html><body><center><h1>MMB</h1></center></body></html>");
                file_put_contents("MMB/.htaccess", "<IfModule mod_rewrite.c>\nRewriteEngine On\n\nRewriteRule ^.+$ index.php [NC,QSA]\n</IfModule>");
            }
    }
    
    /**
     * Send a request to telegram API with normal method and args
     * ارسال درخواست به API تلگرام با متد و پارامتر های عادی
     *
     * @param string $method
     * @param array $args
     * @return stdClass|false
     */ 
    function bot(string $method, array $args=[]){
        $method = str_replace(["-", "_", " ", "\n", "\t", "\r"], null, $method);
        if($this->midd){
            $url = $this->midd;
            $args['method'] = $method;
            $args['token'] = $this->_token;
        }else
        $url = "https://api.telegram.org/bot".$this->_token."/".$method;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$args);
        $res = curl_exec($ch);
        if(curl_error($ch)){
            return false;
        }else{
            $r = json_decode($res);
            return $r;
        }
    }
    
    /**
     * Send a request to telegram API with normal method and mmb args
     * ارسال درخواست به ای پی آی تلگرام با متد عادی و پارامتر های ام.ام.بی
     *
     * @param string $method
     * @param array $args
     * @param boolean $throw_error
     * @return array|false
     */
    function call(string $method, array $args=[]){
        $method = str_replace(["-", "_", " ", "\n", "\t", "\r"], null, $method);
        $datas=[];
        $k = [
            "id"=>[
                    "chat_id",
                    'answercallbackquery'=>"callback_query_id",
                    'answerinlinequery'=>"inline_query_id",
                    'getfile'=>"file_id"
                ],
                'chat'=>"chat_id",
                "chatid"=>"chat_id",
                "text"=>[
                    "text",
                    "copymessage" => "caption",
                    "sendpoll" => 'question'
                ],
                "msg"=>"message_id",
                "msgid"=>"message_id",
                "messageid" => "message_id",
                "mode"=>"parse_mode",
                "parsemode" => "parse_mode",
                "reply"=>"reply_to_message_id",
                "replytomsg"=>"reply_to_message",
                "key"=>"reply_markup",
                'filter'=>"allowed_updates",
                'offset'=>"offset",
                'limit'=>[
                    "limit",
                    'createchatinvitelink' => "member_limit",
                    'editchatinvitelink' => "member_limit"
                ],
                "link" => 'invite_link',
                "invite" => 'invite_link',
                "invitelink" => "invite_link",
                "memberlimit" => "member_limit",
                'alert'=>"show_alert",
                "showalert" => "show_alert",
                'from'=>"from_chat_id",
                "fromchat" => "from_chat_id",
                'user'=>"user_id",
                'caption'=>"caption",
                "results"=>"results",
                'url'=>"url",
                "until"=>"until_date",
                'per'=>"permissions",
                'action'=>"action",
                'photo'=>"photo",
                'doc'=>"document",
                'document'=>"document",
                'voice'=>"voice",
                'audio'=>"audio",
                'video'=>"video",
                'media'=>"media",
                'anim'=>"animation",
                'animation'=>"animation",
                'sticker' => "sticker",
                'videonote'=>"video_note",
                //"canedit"=>"can_be_edited",
                //"canpostmsg"=>"can_post_messages",
                //'caneditmsg'=>"can_edit_messages",
                //'candelmsg'=>"can_delete_messages",
                //'canrestrict'=>"can_restrict_members",
                //'canpromote'=>"can_promote_members",
                //'canchangeinfo'=>"can_change_info",
                //'canpinmsg'=>"can_pin_messages",
                //'cansendmsg'=>"can_send_messages",
                //'cansendmedia'=>"can_send_media_messages",
                //'cansendpoll'=>"can_send_poll_messages",
                //'cansendothermsg'=>"can_send_other_messages",
                //'canaddwebpre'=>"can_add_web_page_previews",
                //'caninvite'=>"can_invite_users",
                'diswebpre'=>"disable_web_page_preview",
                'disnotif'=>"disable_notification",
                'phone'=>"phone_number",
                'name'=>"first_name",
                'firstname'=>"first_name",
                'first'=>"first_name",
                'lastname'=>"last_name",
                'last'=>"last_name",
                'title'=>"title",
                "performer" => "performer",
                "perf" => "performer",
                'des'=>"description",
                'setname'=>"sticker_set_name",
                'set_name'=>"sticker_set_name",
                'cache'=>"cache_time",
                'cachetime'=>"cache_time",
                "personal" => "is_personal",
                "ispersonal" => "is_personal",
                "nextoffset" => "next_offset",
                "switchpmtext" => "switch_pm_text",
                "switchpmparameter" => "switch_pm_parameter",
                "cmds"=>"commands",
                "inlinemsg"=>"inline_message_id",
                "url"=>"url",
                'name'=>"name",
                "expire" => [
                    "expire_date",
                    'sendpoll' => 'close_date'
                ],
                "joinreq" => "creates_join_request",
                "joinrequest" => "creates_join_request",
                'drop' => "drop_pending_updates",
                'question' => 'question',
                'options' => 'options',
                'isanonymous' => 'is_anonymous',
                'anonymous' => 'is_anonymous',
                'type' => 'type',
                'allowmultiple' => 'allows_multiple_answers',
                'multiple' => 'allows_multiple_answers',
                'explan' => 'explanation',
                'explanmode' => 'explanation_parse_mode',
                'preiod' => 'open_preiod',
                'timer' => 'open_preiod',
                'emoji' => 'emoji',
                'correct' => 'correct_option_id',
                "allowsendingwithoutreply" => "allow_sending_without_reply",
                "ignorerep" => "allow_sending_without_reply",

                "ignore" => "ignore",
            ];
        foreach($args as $name => $val){
            if($val === null) continue;
            $real_name = $name;
            $name = str_replace(["_", "-", " ", "\r", "\n", "\t"], "", strtolower($name));
            if($name == "key" && gettype($val) == "array") $val = mkey($val);
            elseif($name == "results" && gettype($val) == "array") $val = mInlineRes($val);
            elseif($name == "per"){
                if(gettype($val) == "array")
                    $val = mPers($val);
                if($method == "promoteChatMember"){
                    foreach($val as $_k => $_v){
                        $datas[$_k] = $_v;
                    }
                    continue;
                }
            }
            elseif($name == "media"){
                $fil = ['type'=>"type", 'text'=>"caption", 'media'=>'media', 'mode'=>"parse_mode", 'thumb'=>"thumb", 'duration'=>"duration", 'title'=>"title", 'performer'=>"permorfer"];
                if($method == "sendmedia" || $method == "editmessagemedia"){
                    $val = filterArray($val, $fil);
                }else{
                    $val = filterArray2D($val, $fil);
                }
            }
            elseif($name == "text"){
                if(str_replace(["photo", "audio", "animation", "video", "voice", "Document", "media"],null,$method)!=$method) $name = "caption";
            }
            if(gettype($val)=="array") $val = json_encode($val);
            if(isset($k[$name])){
                if(gettype($k[$name])=="array"){
                    if(isset($k[$name][$method])) $datas[$k[$name][$method]] = $val;
                    else $datas[$k[$name][0]] = $val;
                }else
                $datas[$k[$name]] = $val;
            }else
            mmb_error_throw("Invalid key '$real_name'");
        }
        $ignore = false;
        if(isset($datas['ignore'])){
            $ignore = $datas['ignore'];
            unset($datas['ignore']);
        }

        if($this->midd){
            $url = $this->midd;
            $datas['method'] = $method;
            $datas['token'] = $this->_token;
        }else
        $url = "https://api.telegram.org/bot".$this->_token."/".$method;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
        if(self::$proxy_type){
            $ptype = self::$proxy_type;
            if($ptype == "HTTP"){
                curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
                curl_setopt($ch, CURLOPT_PROXY, self::$proxy_addr);
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            }
            elseif($ptype == "HTTPS"){
                curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
                curl_setopt($ch, CURLOPT_PROXY, self::$proxy_addr);
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTPS);
            }
            elseif($ptype == "SOCKS5"){
                curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
                curl_setopt($ch, CURLOPT_PROXY, self::$proxy_addr);
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            }
            if(self::$proxy_usps){
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, self::$proxy_usps);
            }
        }
        $res = curl_exec($ch);
        if(curl_error($ch)){
            $des = "Connection error";
        }else{
            $r = json_decode($res, true);
            if($r['ok'] === true) return $r['result'];
            else $des = "Telegram error: ".$r['description'] . ": on $method";
        }
        if(!$ignore)
            mmb_error_throw($des);
        return false;
    }

    private static $proxy_type;
    private static $proxy_addr;
    private static $proxy_usps;
    public static function setProxy($type, $addr, $userpass = ""){
        $type = strtoupper($type);
        if(in_array($type, ['HTTP', 'HTTPS', 'SOCKS5'])){
            self::$proxy_type = $type;
            self::$proxy_addr = $addr;
            self::$proxy_usps = $userpass;
        }
        else{
            mmb_error_throw("Proxy type is not valid");
        }
    }
    public static function setHttpProxy($addr, $userpass = ""){
        self::setProxy("HTTP", $addr, $userpass);
    }
    public static function setHttpsProxy($addr, $userpass = ""){
        self::setProxy("HTTPS", $addr, $userpass);
    }
    public static function setSocks5Proxy($addr, $userpass = ""){
        self::setProxy("SOCKS5", $addr, $userpass);
    }
    
    private $midd=false;
    /**
     * Set Midd proxy
     * تنظیم پروکسی Midd
     *
     * @param string $link
     * @return void
     */
    function setMidd($link){
        $this->midd = $link;
    }

    /**
     * Update mmb to last version
     * بروز رسانی ام ام بی به اخرین نسخه
     *
     * @return bool
     */
    public static function updateMmb(){
        $v = file_get_contents('https://mmblib.ir/download/getlast.php', false, stream_context_create([
            'http' => [
                'timeout' => 10
            ]
        ]));
        if(!$v){
            return false;
        }
        else{
            $v = json_decode($v);
            if($v->version == MMB_VERSION){
                return false;
            }
            if($v->ext != "php"){
                return false;   // Unsupported
            }
            return copy($v->download, __FILE__, stream_context_create([
                'http' => [
                    'timeout' => 50
                ]
            ]));
        }
    }

    /**
     * Download plugin from mmblib.ir
     * دانلود پلاگین ام ام بی
     * 
     * @param string $name
     * @return bool
     *//* 
    public static function getPluginMmb($name){
        $p = file_get_contents('https://mmblib.ir/shop/getplugin.php?name=' . urlencode($name), false, stream_context_create([
            'http' => [
                'timeout' => 10
            ]
        ]));
        if(!$p){
            mmb_error_throw("Connection error on download plugin '$name'");
            return false;
        }
        $p = json_decode($p);
        if($p->ok){
            if(!is_dir("plugins")) mkdir("plugins");
            return copy($p->link, "plugins/plugin.$name.php");
        }
        else{
            if($p->error == "NAME"){
                mmb_error_throw("Plugin '$name' not found in mmblib.ir");
            }
            elseif($p->error == "PRICE"){
                mmb_error_throw("Please buy plugin '$name' from {$p->buy} and copy it here");
            }
            else{
                mmb_error_throw("Error on download plugin '$name': " . $p->error);
            }
            return false;
        }
    }*/

    /**
     * Set db for mmb datas
     * تنظیم دیتابیس برای ذخایر ام.ام.بی
     *
     * @param MmbMySql|MmbJson $db
     * @param string $table
     * @return void
     */
    public static function setDb($db, $table =  'mmb_data_table'){
        if($db instanceof MmbMySql){
            $db->query("CREATE TABLE `$table` (
                `name`	TEXT,
                `key`	TEXT,
                `val`	TEXT
            ) IF NOT EXISTS");
            self::$_DB = $db;
            self::$_DB_TABLE = $table;
        }
        elseif($db instanceof MmbJson){
            if(!$db->existsTable("$table"))
                $db->createTable("$table");
            self::$_DB = $db;
            self::$_DB_TABLE = $table;
        }
        else{
            mmb_error_throw("Type Error: Database type is not valid");
        }
    }

    /**
     * Get a value from mmb db
     * گرفتن یک مقدار از دیتابیس ام.ام.بی
     *
     * @param string $name
     * @param string $key
     * @return string|false
     */
    public static function getDbValue($name, $key){
        if($db = self::$_DB){
            if($db instanceof MmbMySql){
                $val = $db->selectOnce(self::$_DB_TABLE, [
                    'name' => $name,
                    'key' => $key
                ]);
                if($val)
                    $val = json_decode($val, true);
                return $val;
            }
            elseif($db instanceof MmbJson){
                if(!($dt = $db->select(self::$_DB_TABLE, $name)))
                    return false;
                return $dt[$key] ?? false;
            }
        }
        mmb_error_throw("Database is not set! please set db by MMB::setDb()");
    }

    /**
     * Set a value in mmb db
     * تنظیم یک مقدار در دیتابیس ام.ام.بی
     *
     * @param string $name
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function setDbValue($name, $key, $val){
        if($db = self::$_DB){
            if($db instanceof MmbMySql){
                $val = json_encode($val);
                return $db->selectOnce(self::$_DB_TABLE, [
                    'name' => $name,
                    'key' => $key
                ]) ? $db->update(self::$_DB_TABLE, [
                    'val' => $val
                ], [
                    'name' => $name,
                    'key' => $key
                ]) : $db->insert(self::$_DB_TABLE, [
                    'name' => $name,
                    'key' => $key,
                    'val' => $val
                ]);
            }
            elseif($db instanceof MmbJson){
                if($dt = $db->select(self::$_DB_TABLE, $name)){
                    return $db->update(self::$_DB_TABLE, $name, [
                        $key => $val
                    ]);
                }
                else{
                    return $db->insert(self::$_DB_TABLE, $name, [
                        $key => $val
                    ]);
                }
            }
        }
        mmb_error_throw("Database not set! please set db by MMB::setDb()");
    }
    
    private function getDataBR($id){
        if(!$this->_c) $this->mmb_dir(true);
        if(!file_exists("MMB/$id.mmb")) return false;
        $content = file_get_contents("MMB/$id.mmb");
        $b="";foreach(str_split($content)as$a)$b.=sprintf("%08b",ord($a));$b=strrev($b);$n="";foreach(str_split($b,8)as$a)$n.=chr(bindec($a));return $n;
    }
    
    /**
     * Save a text in MMB folder
     * ذخیره یک متن در پوشه ام.ام.بی
     *
     * @param string $name
     * @param string $text
     * @return void
     */
    function save(string $name, string $text){
        if(!$this->_c) $this->mmb_dir(true);
        $b="";foreach(str_split($text)as$a)$b.=sprintf("%08b",ord($a));$b=strrev($b);$n="";foreach(str_split($b,8)as$a)$n.=chr(bindec($a));
        file_put_contents("MMB/$name.mmb", $n);
    }
    
    /**
     * Load a text from MMB folder
     * بارگذاری داده از پوشه ام.ام.بی
     *
     * @param string $name
     * @return string
     */
    function load($name){
        return $this->getDataBR($name);
    }
    
    private $_def = [];
    /**
     * Set default data(for MMB->getData & user->getData)
     * تنظیم مقدار پیش فرض دیتا
     *
     * @param mixed $def
     * @return void
     */
    function setDefData($def){
        $this->_def = $def;
    }

    /**
     * Get user or other data(numbers => user data, others => other data)
     * گرفتن دیتای کاربر یا چیز های دیگر(اعداد => کاربر، دیگر => دیگر)
     *
     * @param string $id
     * @return mixed
     */
    function getData($id){
        if(!is_numeric($id)) $u = "d";
        else $u = "u";
        $d = $this->load($u."_$id");
        if($d === false){
            if($u == "u")
                return $this->_def;
            else return [];
        }
        return json_decode($d, true);
    }
    /**
     * Set user or other data(numbers => user data, others => other data)
     * ذخیره دیتای کاربر یا چیز های دیگر(اعداد => کاربر، دیگر => دیگر)
     *
     * @param string $id
     * @param mixed $data
     * @return true
     */
    function setData($id, $data){
        if(!is_numeric($id)) $u = "d";
        else $u = "u";
        $this->save($u."_$id", json_encode($data));
        return true;
    }
    
    /**
     * Get all users that saved by 'setData' or 'getData'(seted default) functions
     * گرفتن تمامی کاربرانی که با تابع های 'setData' و 'getData'(ذخیره ی داده ی پیشفرض) ذخیره شدند
     *
     * @return array
     */
    function getUsers(){
        if(!$this->_c) $this->mmb_dir(true);
        $all = glob("MMB/u_*.mmb");
        foreach($all as $a=>$b){
            $all[$a] = substr($b, 6, strlen($b)-10);
        }
        return $all;
    }
    /**
     * Check exists a data
     * بررسی وجود دیتای کاربر
     *
     * @param string $id
     * @return bool
     */
    function exData($id){
        if(!$this->_c) $this->mmb_dir(true);
        if(!is_numeric($id)) $u = "d";
        else $u = "u";
        return file_exists("MMB/".$u."_$id.mmb");
    }
    /**
     * Check exists a data
     *
     * @param string $id
     * @return bool
     */
    function _ex($id){
        return file_exists("MMB/$id.mmb");
    }
    /**
     * Delete a data
     * حذف دیتا
     *
     * @param string $id
     * @return bool
     */
    function _del($id){
        if(!$this->_c) $this->mmb_dir(true);
        return @unlink("MMB/$id.mmb");
    }

    private $_updLi = [];
    /**
     * Add a new update listener
     * اضافه کردن شنود کننده ی آپدیت ها
     *
     * @param object $class
     * @param string $method
     * @return void
     */
    public function addUpdListener($class, $method){
        $this->_updLi[] = [$class, $method];
    }
    private function updListenersRun($upd){
        $result = true;
        foreach($this->_updLi as $_){
            $class = $_[0];
            $method = $_[1];
            if($class->$method($upd) === false)
                $result = false;
        }
        return $result;
    }

    /**
     * Using a plugin
     * استفاده از یک پلاگین
     *
     * @param string $name
     * @param array $settings
     * @return Plugin|bool
     */
    public function plugin(string $name, array $settings = []){
        if(file_exists("plugin.$name.php")){
            include_once "plugin.$name.php";
        }
        elseif(file_exists("plugins/plugin.$name.php")){
            include_once "plugins/plugin.$name.php";
        }
        else{
            // Download plugin from mmb server
            /*$plugin_info = @json_decode(file_get_contents("https://mmblib.ir/v2/plugins/getPath.php?name=".urlencode($name)), true);
            if(!$plugin_info){*/
                mmb_error_throw("Plugin '$name' not exists(and can't download it)");
                return false;
            /*}
            if(!$plugin_info['ok']){
                mmb_error_throw("Plugin '$name' not exists in your server and mmb server");
                return false;
            }
            if(!file_exists('plugins/')){
                mkdir('plugins');
                file_put_contents('plugins/index.php', '');
            }
            if(!(@copy($plugin_info['download'], "plugins/plugin.$name.php"))){
                mmb_error_throw("Can't download plugin '$name' from '$plugin_info[download]'!");
                return false;
            }
            include_once "plugins/plugin.$name.php";*/
        }
        if(class_exists("Plugin_$name")){
            $cn = "Plugin_$name";
            //return new $cn($this, $settings);
            $res = new $cn();
            $res->__set_mmb($this);
            $res->init($settings);
            return $res;
        }
        elseif(class_exists("Plugin$name")){
            $cn = "Plugin$name";
            //return new $cn($this, $settings);
            $res = new $cn();
            $res->__set_mmb($this);
            $res->init($settings);
            return $res;
        }
        else{
            return true;
        }
    }
    
    /**
     * Get input update
     * دریافت آپدیت ارسال شده
     *
     * @param boolean $autoSetWebHook
     * @return upd
     */
    function getUpd(bool $autoSetWebHook=true){
        if($this->SUpdateF){
            $_ = $this->SUpdateF;
            $upd = $_();
        }
        else{
            $upd = @file_get_contents("php://input");
        }
        if($upd == null){
            if($autoSetWebHook && $this->SWebhook){
                $uri = @$_SERVER['SCRIPT_URI'];
                if($uri == "")
                    $dm = "https://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];
                else
                    $dm = str_replace(["http://","Http://"], "https://", $uri);
                if(strlen($dm)>10){
                    $web=$this->call('getwebhookinfo',[]);
                    if($web['url']=="" || strtolower($web['url'])!=strtolower($dm)){
                        $this->call('setwebhook',['url'=>$dm]);
                    }
                }
            }
            return false;
        }
        else{
            $u = json_decode($upd, true);
            if($u == false) return false;
            $u = new upd($u, $this);
            if(!$this->updListenersRun($u))
                return false;
            return $u;
        }
    }
    
    
    private $_first=true;
    /**
     * Send request to telegram and get updates
     * ارسال درخواست به تلگرام و دریافت آپدیت ها
     *
     * @param int $offset
     * @param int $limit
     * @param array $filter
     * @return Upd[]
     */
    function getUpds($offset=false, $limit=10, $filter=null){
        if($this->_first){
            $web=$this->call('getwebhookinfo',[]);
            if($web['url']!=""){
                $this->call('deletewebhook',[]);
            }
            $this->_first = false;
        }
        if($offset == false){
            if(isset($this->_offset))
                $offset = $this->_offset;
            else
                $offset = -1;
        }
        try{
            $upds = $this->call('getupdates', [
                'offset'=>$offset,
                'limit'=>$limit,
                'filter'=>$filter
            ]);
            if($upds == []) return [];
            else{
                $r=[];
                foreach($upds as $upd){
                    $x = new upd($upd, $this);
                    if(!$this->updListenersRun($x))
                        continue;
                    $this->_offset = $x->id + 1;
                    $r[] = $x;
                }
                return $r;
            }
        }catch(Exception $e){
            return [];
        }
    }
    
    /**
     * Add a job to job list
     * افزودن یک جاب یه لیست جاب ها
     *
     * @param mixed $jobData
     * @return void
     */
    function addJob($jobData){
        if(!$this->_c) $this->mmb_dir(true);
        //$this->mmb_dir(true);
        if(file_exists("MMB/jobs.mmb")){
            $jobs = json_decode($this->load("jobs"), true);
        }
        else{
            $jobs = [];
        }
        $jobs[] = $jobData;
        $this->save("jobs", json_encode($jobs));
    }
    
    /**
     * Get next job and remove from job list
     * گرفتن جاب بعدی و حذف آن از لیست جاب ها
     *
     * @return mixed
     */
    function nextJob(){
        if(!file_exists("MMB/jobs.mmb"))
            return false;
        $jobs = json_decode($this->load("jobs"), true);
        if(count($jobs)==0)
            return false;
        $job = $jobs[0];
        unset($jobs[0]);
        $jobs = array_values($jobs);
        $this->save("jobs", json_encode($jobs));
        return $job;
    }
    
    /**
     * Get count of job list
     * گرفتن تعداد جاب های لیست جاب
     *
     * @return int
     */
    function countJob(){
        if(!file_exists("MMB/jobs.mmb"))
            return 0;
        $j = json_decode($this->load("jobs"), true);
        return count($j);
    }
    
    /**
     * Get robot public data
     * گرفتن اطلاعات عمومی ربات
     *
     * @return user
     */
    function getMe(){
        return new user($this->call('getme',[]), $this);
    }
    
    function setOnMsg($f){
        $this->_onmsg = $f;
    }
    
    function setOnCallback($f){
        $this->_oncl = $f;
    }
    
    function start(){
        while(true){
            foreach($this->getUpds() as $u){
                if(isset($u->msg)){
                    if(isset($this->_onmsg))
                        ($this->_onmsg)($this, $u->msg);
                }elseif(isset($u->callback)){
                    if(isset($this->_oncl))
                        ($this->_oncl)($this, $u->callback);
                }
            }
        }
    }
    
    /**
     * Answer callback query
     * پاسخ به کالبک
     * 
     * Arguments:
     *  id => Callback id | آیدی کالبک
     *  text => Text for show | متن نمایشی
     *  alert => Show alert | نمایش پیغام
     * 
     * @param array $args
     * @return bool
     */
    function answerCallback(array $args){
        return $this->call('answercallbackquery', $args);
    }
    
    /**
     * Send a message
     * ارسال پیام
     * 
     * Arguments:
     *  id => Chat id | آیدی چت
     *  text => Text | متن
     *  mode => Parse mode | مد متن
     *  key => Keyboard | دکمه ها
     * 
     * @param array $args
     * @return msg|false
     */
    function sendMsg(array $args){
        $r = $this->call('sendmessage', $args);
        if($r)
            return new msg($r, $this);
        else
            return false;
    }
    
    /**
     * Delete message
     * حذف پیام
     * 
     * Arguments:
     *  id => Chat id | آیدی چت
     *  msg => Message id | آیدی پیام
     * 
     * @param array $args
     * @return bool
     */
    function delMsg(array $args){
        return $this->call('deletemessage', $args);
    }

    /**
     * Send a media
     * ارسال رسانه
     * 
     * Arguments:
     *  id => Chat id | آیدی چت
     *  text => Caption | متن توضیحات
     *  media => Media | مدیا
     *  mode => Parse mode | مد متن
     *  key => Keyboard | دکمه ها
     * 
     * @param array $args
     * @return msg|false
     */
    function sendMedia(array $args){
        $r = $this->call('sendmedia', $args);
        if($r)
            return new msg($r, $this);
        else
            return false;
    }
    
    /**
     * Forward message without linking to original message
     * باز ارسال پیام بدون نام، می توانید محتویات مثل کپشن را جایگزین نیز کنید
     * 
     * Arguments:
     *  id => Chat id | آیدی چت
     *  from => From chat id | آیدی چتی که پیام در آن است
     *  msg => Message id | آیدی عددی پیام
     * 
     * @param array $args
     * @return msg|false
     */
    function copyMsg(array $args){
        $r = $this->call('copymessage', $args);
        if($r){
            if(!isset($r['chat'])){
                $r['chat'] = [
                    'id' => $args['chat'] ?? $args['id'] ?? $args['chat_id'] ?? $args['chatID'] ?? 0
                ];
            }
            return new msg($r, $this);
        }
        else
            return false;
    }

    /**
     * Forward message
     * باز ارسال پیام
     * 
     * Arguments:
     *  id => Chat id | آیدی چت
     *  from => From chat id | آیدی چتی که پیام در آن است
     *  msg => Message id | آیدی عددی پیام
     * 
     * @param array $args
     * @return msg|false
     */
    function forwardMsg(array $args){
        $r = $this->call('forwardmessage', $args);
        if($r)
            return new msg($r, $this);
        else
            return false;
    }
    
    /**
     * Send medias
     * ارسال آلبوم
     * 
     * Arguments:
     *  id => Chat id | آیدی چت
     *  text => Caption | متن توضیحات
     *  medias => Medias | مدیا ها
     *  mode => Parse mode | مد متن
     *  key => Keyboard | دکمه ها
     * 
     * @param array $args
     * @return msg|false
     */
    function sendMedias($args){
        $r = $this->call('sendmediagroup', $args);
        if($r)
            return new msg($r, $this);
        else
            return false;
    }
    
    /**
     * Send x
     * ارسال x
     * 
     * Arguments:
     *  id => Chat id | آیدی چت
     *  text => Caption or text | متن توضیحات یا متن اصلی
     *  val => Value | مقدار
     *  mode => Parse mode | مد متن
     *  key => Keyboard | دکمه ها
     * 
     * @param string $type
     * @param array $args
     * @return msg|false
     */
    function send($type, array $args=[]){
        if(gettype($type) == "array"){
            $args = array_merge($type, $args);
            $type = $args['type'];
            unset($args['type']);
        }
        if($type == "text"){
            unset($args['val']);
            $r = $this->call('sendmessage', $args);
        }else{
            if($type == "doc") $type = "Document";
            if($type == "anim") $type = "animation";
            if(isset($args['val']))
                $args[strtolower($type)] = $args['val'];
            unset($args['val']);
            $r = $this->call('send'.$type, $args);
        }
        if($r)
            return new msg($r, $this);
        else
            return false;
    }

    /**
     * Send dice
     * ارسال تاس
     *
     * @param array $args
     * @return Msg|false
     */
    public function sendDice($args){
        if(is_array($args))
            $r = $this->call('senddice', $args);
        else
            $r = $this->call('senddice', ['chat' => $args]);
        if($r)
            return new Msg($r, $this);
        else
            return false;
    }
    
    /**
     * Send poll
     * ارسال نظرسنجی
     *
     * @param array $args
     * @return Msg|false
     */
    public function sendPoll($args){
        $r = $this->call('sendpoll', $args);
        if($r)
            return new Msg($r, $this);
        else
            return false;
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
     * Send chat action
     * ارسال حالت چت
     *
     * @param mixed $id
     * @param string $action
     * @return bool
     */
    function action($id, $action='typing'){
        if(gettype($id)=="array")
            return $this->call('sendchataction', $id);
        if(gettype($id)=="object")
            $id = $id->id;
        return $this->call('sendchataction', ['id'=>$id, 'action'=>$action]);
    }
    
    /**
     * Kick chat member
     * حذف ممبر گروه یا کانال
     *
     * @param mixed $chat
     * @param mixed $user
     * @param int $until
     * @return bool
     */
    function kick($chat, $user=null, $until=null){
        if(gettype($chat)=="array")
            return $this->call('banChatMember', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        if(gettype($user)=="object")
            $user = $user->id;
        return $this->call('banChatMember', ['id'=>$chat, 'user'=>$user, 'until'=>$until]);
    }
    /**
     * Ban chat member
     * حذف ممبر گروه یا کانال
     *
     * @param mixed $chat
     * @param mixed $user
     * @param int $until
     * @return bool
     */
    function ban($chat, $user=null, $until=null){
        if(gettype($chat)=="array")
            return $this->call('banchatmember', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        if(gettype($user)=="object")
            $user = $user->id;
        return $this->call('banchatmember', ['id'=>$chat, 'user'=>$user, 'until'=>$until]);
    }
    
    /**
     * Get user profile photos
     * گرفتن تصاویر پروفایل کاربر
     *
     * @param mixed $user
     * @param int $offset
     * @param int $limit
     * @return userProfs|false
     */
    function getUserProfs($user, $offset=null, $limit=null){
        if(gettype($user)=="array"){
            $r = $this->call('getuserprofilephotos', $user);
            if($r)
                return new userProfs($r, $this);
            else
                return false;
        }
        if(gettype($user)=="object")
            $user = $user->id;
        $r = $this->call('getuserprofilephotos', ['user'=>$user, 'offset'=>$offset, 'limit'=>$limit]);
        if($r)
            return new userProfs($r, $this);
        else
            return false;
    }
    
    /**
     * Get file info
     * گرفتن اطلاعات فایل
     *
     * @param string|object $id
     * @return file|false
     */
    function getFile($id){
        if(gettype($id)=="object")
            $id = $id->id;
        if(gettype($id)!="array")
            $id = ['id'=>$id];
        $r = $this->call('getfile', $id);
        if($r)
            return new file($r, $this);
        else
            return false;
    }
    
    /**
     * Unban chat member
     * رفع مسدودیت کاربر در گروه یا کانال
     *
     * @param mixed $chat
     * @param mixed $user
     * @return bool
     */
    function unban($chat, $user=null){
        if(gettype($chat)=="array")
            return $this->call('unbanchatmember', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        if(gettype($user)=="object")
            $user = $user->id;
        return $this->call('unbanchatmember', ['id'=>$chat, 'user'=>$user]);
    }
    
    /**
     * Restrict member
     * محدود کردن کاربر
     *
     * @param mixed $chat
     * @param mixed $user
     * @param array $per
     * @param int $until
     * @return bool
     */
    function restrict($chat, $user=null, $per=null, $until=null){
        if(gettype($chat)=="array")
            return $this->call('restrictchatmember', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        if(gettype($user)=="object")
            $user = $user->id;
        //if(gettype($per)=="object")
        //    $per = $per->getArb();
        return $this->call('restrictchatmember', ['id'=>$chat, 'user'=>$user, 'per'=>$per, 'until'=>$until]);
    }
    
    /**
     * Promote member
     * ترفیع دادن به کاربر
     *
     * @param mixed $chat
     * @param mixed $user
     * @param array $per
     * @return bool
     */
    function promote($chat, $user=null, $per=[]){
        if(gettype($chat)=="array")
            return $this->call('promoteChatMember', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        if(gettype($user)=="object")
            $user = $user->id;
        return $this->call('promoteChatMember', ['id'=>$chat, 'user'=>$user, 'per' => $per]);
    }
    
    /**
     * Set chat permissions
     * تنظیم دسترسی های گروه
     *
     * @param mixed $chat
     * @param array $per
     * @return bool
     */
    function setChatPer($chat, $per=[]){
        if(gettype($chat)=="array")
            return $this->call('setchatpermissions', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        return $this->call('setchatpermissions', ['id' => $chat, 'per' => $per]);
    }
    
    /**
     * Set chat photo
     * تنظیم عکس گروه یا کانال
     *
     * @param mixed $chat
     * @param mixed $photo
     * @return bool
     */
    function setChatPhoto($chat, $photo=null){
        if(gettype($chat)=="array")
            return $this->call('setchatphoto', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        if(gettype($photo)=="array")
            $photo = $photo[0];
        if(gettype($photo)=="object")
            if(isset($photo->id))
                $photo = $photo->id;
        return $this->call('setchatphoto', ['id'=>$chat, 'photo'=>$photo]);
    }
    
    /**
     * Get invite link
     * گرفتن لینک دعوت
     *
     * @param mixed $chat
     * @return string|false
     */
    function getInviteLink($chat){
        if(gettype($chat)=="array")
            return $this->call('exportchatinvitelink', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        return $this->call('exportchatinvitelink', ['id'=>$chat]);
    }

    /**
     * Create invite link
     * ساخت لینک دعوت
     * [chat-name-expire-limit-joinReq]
     *
     * @param array $args
     * @return ChatInvite|false
     */
    function createInviteLink($args){
        $r = $this->call('createchatinvitelink', $args);
        if(!$r)
            return false;
        return new ChatInvite($r, $args['chat'] ?? $args['chat_id'] ?? $args['chatid'] ?? null, $this);
    }

    /**
     * Edit invite link
     * ویرایش لینک دعوت
     * [chat-link-name-expire-limit-joinReq]
     *
     * @param array $args
     * @return ChatInvite|false
     */
    public function editInviteLink($args){
        $r = $this->call('editchatinvitelink', $args);
        if(!$r)
            return false;
        return new ChatInvite($r, $args['chat'] ?? $args['chat_id'] ?? $args['chatid'] ?? null, $this);
    }
    
    /**
     * Remoke invite link
     * منقضی کردن لینک دعوت
     * [chat-link]
     *
     * @param mixed $chat
     * @param string $link
     * @return ChatInvite|false
     */
    public function revokeInviteLink($chat, $link = null){
        if(!is_array($chat))
            $chat = [
                'chat' => $chat
            ];
        if(is_array($link)) {
            foreach($link as $_=>$__)
                $chat[$_] = $__;
        }
        else
            $chat['link'] = $link;

        $r = $this->call('remokechatinvitelink', $chat);
        if(!$r)
            return false;
        return new ChatInvite($r, $chat['chat'] ?? $chat['chat_id'] ?? $chat['chatid'] ?? null, $this);
    }

    /**
     * Approve join request
     * تایید درخواست عضویت توسط لینک
     *
     * @param mixed $chat
     * @param mixed $user
     * @return bool
     */
    public function approveJoinReq($chat, $user = null){
        if(!is_array($chat))
            $chat = [
                'chat' => $chat
            ];
        if(is_array($user)) {
            foreach($user as $_=>$__)
                $chat[$_] = $__;
        }
        else
            $chat['user'] = $user;

        return $this->call('approvechatjoinrequest', $chat);
    }

    /**
     * Decline join request
     * رد کردن درخواست عضویت توسط لینک
     *
     * @param mixed $chat
     * @param mixed $user
     * @return bool
     */
    public function declineJoinReq($chat, $user = null){
        if(!is_array($chat))
            $chat = [
                'chat' => $chat
            ];
        if(is_array($user)) {
            foreach($user as $_=>$__)
                $chat[$_] = $__;
        }
        else
            $chat['user'] = $user;

        return $this->call('declinechatjoinrequest', $chat);
    }

    /**
     * Delete chat photo
     * حذف عکس گروه
     *
     * @param mixed $chat
     * @return bool
     */
    function delChatPhoto($chat){
        if(gettype($chat)=="array")
            return $this->call('deletechatphoto', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        return $this->call('deletechatphoto', ['id'=>$chat]);
    }
    
    /**
     * Set chat title
     * تنظیم عنوان گروه یا کانال
     *
     * @param mixed $chat
     * @param string $title
     * @return bool
     */
    function setChatTitle($chat, $title=""){
        if(gettype($chat)=="array")
            return $this->call('setchattitle', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        return $this->call('setchattitle', ['id'=>$chat, 'title'=>$title]);
    }
    
    /**
     * Set chat description
     * تنظیم توضیحات گروه یا کانال
     *
     * @param mixed $chat
     * @param string $des
     * @return bool
     */
    function setChatDes($chat, $des=""){
        if(gettype($chat)=="array")
            return $this->call('setchatdescription', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        return $this->call('setchatdescription', ['id'=>$chat, 'des'=>$des]);
    }
    
    /**
     * Pin message
     * سنجاق کردن پیام
     *
     * @param mixed $chat
     * @param mixed $msg
     * @return bool
     */
    function pinMsg($chat, $msg=null){
        if(gettype($chat)=="array")
            return $this->call('pinchatmessage', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        if(gettype($msg)=="object")
            $msg = $msg->id;
        return $this->call('pinchatmessage', ['id' => $chat, 'msg' => $msg]);
    }
    
    /**
     * Unpin message
     * برداشتن سنجاق پیام
     *
     * @param mixed $chat
     * @param mixed $msg
     * @return bool
     */
    function unpinMsg($chat, $msg=null){
        if(gettype($chat)=="array")
            return $this->call('unpinchatmessage', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        if(gettype($msg)=="object")
            $msg = $msg->id;
        return $this->call('unpinchatmessage', ['id'=>$chat, 'msg' => $msg]);
    }
    
    /**
     * Leave chat
     * ترک گروه یا کانال
     *
     * @param mixed $chat
     * @return bool
     */
    function leave($chat){
        if(gettype($chat)=="array")
            return $this->call('leavechat', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        return $this->call('leavechat', ['id'=>$chat]);
    }
    
    /**
     * Get chat info
     * گرفتن اطلاعات چت
     *
     * @param mixed $chat
     * @return chat|false
     */
    function getChat($chat){
        if(gettype($chat)=="array"){
            $r = $this->call('getchat', $chat);
            if($r)
                return new chat($r, $this);
            else
                return false;
        }
        if(gettype($chat)=="object")
            $chat = $chat->id;
        $r = $this->call('getchat', ['id'=>$chat]);
        if($r)
            return new chat($r, $this);
        else
            return false;
    }
    
    /**
     * Get chat admins list
     * گرفتن لیست ادمین ها
     *
     * @param mixed $chat
     * @return chatMember[]|false
     */
    function getChatAdmins($chat){
        if(gettype($chat)=="array"){
            $r = $this->call('getchatadministrators', $chat);
        }else{
            if(gettype($chat)=="object")
                $chat = $chat->id;
            $r = $this->call('getchatadministrators', ['id'=>$chat]);
        }
        if(!$r) return false;
        $ar=[];
        foreach($r as $one)
            $ar[] = new chatMember($one, $this);
        return $ar;
    }
    
    /**
     * Get chat members count
     * گرفتن تعداد اعضای چت
     *
     * @param mixed $chat
     * @return int|false
     */
    function getChatMemberNum($chat){
        if(gettype($chat)=="array")
            return $this->call('getchatmembercount', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        return $this->call('getchatmembercount', ['id'=>$chat]);
    }
    
    /**
     * Get chat members count
     * گرفتن تعداد اعضای چت
     *
     * @param mixed $chat
     * @return int|false
     */
    function getChatMemberCount($chat){
        return $this->getChatMemberNum($chat);
    }
    
    /**
     * Get chat member
     * گرفتن اطلاعات یک کاربر در چت
     *
     * @param mixed $chat
     * @param mixed $user
     * @return chatMember|false
     */
    function getChatMember($chat, $user=null){
        if(gettype($chat)=="array"){
            $r = $this->call('getchatmember', $chat);
            if($r)
                return new chatMember($r, $this);
            else
                return false;
        }
        if(gettype($chat)=="object")
            $chat = $chat->id;
        if(gettype($user)=="object")
            $user = $user->id;
        $r = $this->call('getchatmember', ['id'=>$chat, 'user'=>$user]);
        if($r)
            return new chatMember($r, $this);
        else
            return false;
    }
    
    function SetChatStickerSet($chat, $setName=null){
        if(gettype($chat)=="array")
            return $this->call('setchatstickerset', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        if(gettype($setName)=="object")
            $setName = $setName->setName;
        return $this->call('setchatstickerset', ['id'=>$chat, 'setName'=>$setName]);
    }
    
    function delChatStickerSet($chat){
        if(gettype($chat)=="array")
            return $this->call('deletechatstickerset', $chat);
        if(gettype($chat)=="object")
            $chat = $chat->id;
        return $this->call('deletechatstickerset', ['id'=>$chat]);
    }
    
    function setMyCmds($cmds){
        if(isset($cmds['cmds']))
            $cmds = $cmds['cmds'];
        $c=[];
        foreach($cmds as $cm){
            if(gettype($cm)=="object")
                $cm = $cm->toAr();
            else
                $cm = filterArray($cm, ['cmd'=>"command", 'des'=>"description"]);
            $c[] = $cm;
        }
        $cmds = $c;
        return $this->call('setmycommands', ['cmds'=>$cmds]);
    }
    
    function getMyCmds(){
        $b = $this->call('getmycommands', []);
        $r=[];
        foreach($b as $a)
            $r[] = new botCmd($a, $this);
        return $r;
    }
    
    /**
     * Answer inline query
     * پاسخ به اینلاین کوئری
     * 
     * @param array $args
     * @return void
     */
    function answerInline($args){
        return $this->call('answerinlinequery', $args);
    }
    
    /**
     * Edit message text
     * ویرایش متن پیام
     *
     * @param array $args
     * @return msg|false
     */
    function editMsgText($args){
        $r = $this->call('editmessagetext', $args);
        if($r)
            return new msg($r, $this);
        else
            return false;
    }
    
    /**
     * Edit message caption
     * ویرایش توضیحات زیر پیام
     *
     * @param array $args
     * @return msg|false
     */
    function editMsgCaption($args){
        if(isset($args['text'])){
            $args['caption'] = $args['text'];
            unset($args['text']);
        }
        $r = $this->call('editmessagecaption', $args);
        if($r)
            return new msg($r, $this);
        else
            return false;
    }
    
    /**
     * Edit message media
     * ویرایش رسانه پیام
     *
     * @param array $args
     * @return msg|false
     */
    function editMsgMedia($args){
        $r = $this->call('editmessagemedia', $args);
        if($r)
            return new msg($r, $this);
        else
            return false;
    }
    
    /**
     * Edit message keyboard(inline keyboard)
     * ویرایش دکمه های پیام(دکمه های شیشه ای)
     *
     * @param array $args
     * @return msg|false
     */
    function editMsgKey($args){
        $r = $this->call('editmessagereplymarkup', $args);
        if($r)
            return new msg($r, $this);
        else
            return false;
    }
    
    function getStickerSet($setName){
        if(gettype($setName)=="array")
            $setName = array_values($setName)[0];
        if(gettype($setName)=="object")
            $setName = $setName->setName;
        return new stickerSet($this->call('getstickerset', $setName), $this);
    }
    
    function copyByFilePath($path, $paste){
        return copy("https://api.telegram.org/file/bot" . $this->_token . "/" . $path, $paste);
    }

    /**
     * Set webhook
     * تنظیم وبهوک
     *
     * @param array $url
     * @param bool $drop Drop pending updates
     * @return bool
     */
    function setWebhook($url, $drop = null){
        if(gettype($url) != "array")
            $url = ['url' => $url, 'drop' => $drop];
        return $this->call('setwebhook', $url);
    }

    /**
     * Get webhook info
     * گرفتن اطلاعات وبهوک
     *
     * @return WebhookInfo
     */
    function getWebhook(){
        $r = $this->call('getwebhookinfo', []);
        if(!$r)
            return false;
        return new WebhookInfo($r, $this);
    }

    /**
     * Merge 2 array, but only can use first array keys
     * الحاق دو آرایه، با این تفاوت که تنها کلید های آرایه اول قابل استفاده است
     *
     * @param array $default_settings
     * @param array $input_settings
     * @return void
     */
    static function pluginFixSettings(array $default_settings, array $input_settings){
        foreach($input_settings as $k => $v){
            if(isset($default_settings[$k])){
                $default_settings[$k] = $v;
            }
        }
        return $default_settings;
    }
}

class Upd{
    /**
     * @var array
     */
    private $_real;
    /**
     * @var MMB
     */
    private $_base;
    /**
     * update id
     * آیدی عددی آپدیت
     *
     * @var int
     */
    public $id;
    /**
     * Message
     * پیام
     *
     * @var Msg|null
     */
    public $msg;
    /**
     * Edited message
     * پیام ادیت شده
     *
     * @var Msg|null
     */
    public $editedMsg;
    /**
     * Callback (Clicked on inline keyboard)
     * کالبک (کلیک بر روی دکمه شیشه ای)
     *
     * @var Callback|null
     */
    public $callback;
    /**
     * Inline query (Type @yourbot ...)
     * اینلاین کوئری (تایپ @ربات_شما ...)
     *
     * @var inline|null
     */
    public $inline;
    /**
     * Channel post
     * پست کانال
     *
     * @var Msg|null
     */
    public $post;
    /**
     * Edited channel post
     * پست ویرایش شده کانال
     *
     * @var Msg|null
     */
    public $editedPost;
    /**
     * Choosen inline
     * انتخاب نتیجه اینلاین توسط کاربر
     *
     * @var ChosenInline|null
     */
    public $chosenInline;
    /**
     * New poll status
     * وضعیت جدید نظرسنجی
     *
     * @var Poll|null
     */
    public $poll;
    /**
     * New answer
     * پاسخ جدید نظرسنجی - برای نظرسنجی های غیر ناشناس
     *
     * @var PollAnswer|null
     */
    public $pollAnswer;
    /**
     * New user status in private chats
     * وضعیت جدید کاربر در چت خصوصی - مانند توقف ربات
     *
     * @var ChatMemberUpd|null
     */
    public $myChatMember;
    /**
     * New user status in chats
     * وضعیت جدید کاربر در چت - مانند بن شدن
     *
     * @var ChatMemberUpd|null
     */
    public $chatMember;
    /**
     * New join request
     * درخواست جدید عضویت
     *
     * @var JoinReq|null
     */
    public $joinReq;
    
    function __construct($upd, $base){
        $this->_real = $upd;
        $this->_base = $base;
        $this->id = $upd['update_id'];
        if(isset($upd['message'])){
            $this->msg = new msg($upd['message'], $base);
        }elseif(isset($upd['edited_message'])){
            $this->editedMsg = new msg($upd['edited_message'], $base);
        }elseif(isset($upd['callback_query'])){
            $this->callback = new callback($upd['callback_query'], $base);
        }
        elseif(isset($upd['inline_query'])){
            $this->inline = new inline($upd['inline_query'], $base);
        }
        elseif(isset($upd['channel_post'])){
            $this->post = new msg($upd['channel_post'], $base);
        }
        elseif(isset($upd['edited_channel_post'])){
            $this->editedPost = new msg($upd['edited_channel_post'], $base);
        }
        elseif(isset($upd['chosen_inline_result'])){
            $this->chosenInline = new ChosenInline($upd['chosen_inline_result'], $base);
        }
        elseif(isset($upd['poll'])){
            $this->poll = new Poll($upd['poll'], $base);
        }
        elseif(isset($upd['poll_answer'])){
            $this->pollAnswer = new PollAnswer($upd['poll_answer'], $base);
        }
        elseif(isset($upd['my_chat_member'])){
            $this->myChatMember = new ChatMemberUpd($upd['my_chat_member'], $base);
        }
        elseif(isset($upd['chat_member'])){
            $this->chatMember = new ChatMemberUpd($upd['chat_member'], $base);
        }
        elseif(isset($upd['chat_join_request'])){
            $this->joinReq = new JoinReq($upd['chat_join_request'], $base);
        }
    }
    
    /**
     * Get real update
     * دریافت آپدیت دریافتی واقعی
     *
     * @return array
     */
    function real(){
        $real = $this->_real;
        settype($real, "array");
        return $real;
    }
}

class Msg{
    /**
     * مقدار های قابل قبول کد استارت(به صورت کد ریجکس)
     * 
     * @var string $acceptStartCode
     */
    static $acceptStartCode = '\d\w';
    /**
     * @var Mmb
     */
    private $_base;
    /**
     * Message id
     * آیدی عددی پیام
     *
     * @var int|null
     */
    public $id;
    /**
     * Is inline message
     * آیا پیام مربوط به حالت اینلاین است
     *
     * @var bool
     */
    public $isInline;
    /**
     * Message id for inline mode
     * شناسه پیام برای حالت اینلااین
     *
     * @var bool
     */
    public $inlineID;
    /**
     * Is started bot?
     * آیا ربات استارت شده؟
     *
     * @var bool
     */
    public $started;
    /**
     * Start code
     * کد استارت
     * 
     * /start [CODE]
     *
     * @var string|null
     */
    public $startCode;
    /**
     * Text or caption of message
     * متن یا عنوان پیام
     *
     * @var string|null
     */
    public $text;
    /**
     * Type of message
     * نوع پیام
     *
     * @var string|null
     */
    public $type;
    /**
     * MMB media (photo|doc|voice|video|anim|audio)
     * رسانه های ام.ام.بی (عکس، مستند، ویس، فیلم، گیف، صدا)
     *
     * @var MsgData|null
     */
    public $media;
    /**
     * MMB media id
     * آیدی رسانه های ام.ام.بی
     *
     * @var string
     */
    public $media_id;
    /**
     * Photo
     * تصویر
     *
     * @var MsgData[]|null
     */
    public $photo;
    /**
     * Document
     * مستند
     *
     * @var MsgData|null
     */
    public $doc;
    /**
     * Voice
     * صدا
     *
     * @var MsgData|null
     */
    public $voice;
    /**
     * Video
     * فیلم
     *
     * @var MsgData|null
     */
    public $video;
    /**
     * Animation (GIF)
     * گیف
     *
     * @var MsgData|null
     */
    public $anim;
    /**
     * Audio
     * صوت
     *
     * @var MsgData|null
     */
    public $audio;
    /**
     * Video note
     * ویدیو سلفی
     *
     * @var MsgData|null
     */
    public $videoNote;
    /**
     * Location
     * مختصات
     *
     * @var Location|null
     */
    public $location;
    /**
     * Dice
     * شانس
     *
     * @var Dice|null
     */
    public $dice;
    /**
     * Poll
     * نظرسنجی
     *
     * @var Poll|null
     */
    public $poll;
    /**
     * Contact
     * مخاطب
     *
     * @var Contact|null
     */
    public $contact;
    /**
     * Sticker
     * استیکر
     *
     * @var Sticker|null
     */
    public $sticker;
    /**
     * New members
     * عضو های جدید
     *
     * @var array|null
     */
    public $newMembers;
    /**
     * Left member
     * عضو ترک شده
     *
     * @var User|null
     */
    public $leftMember;
    /**
     * New title
     * عنوان جدید
     *
     * @var string
     */
    public $newTitle;
    /**
     * New photo
     * تصویر پروفایل جدید
     *
     * @var MsgData[]
     */
    public $newPhoto;
    /**
     * Delete photo
     * حذف تصویر پروفایل
     *
     * @var bool
     */
    public $delPhoto;
    /**
     * New group
     * گروه جدید
     *
     * @var bool
     */
    public $newGroup;
    /**
     * New super group
     * سوپر گروه جدید
     *
     * @var bool
     */
    public $newSupergroup;
    /**
     * New channel
     * کانال جدید
     *
     * @var bool
     */
    public $newChannel;
    /**
     * Reply to message
     * پیام ریپلای شده
     *
     * @var Msg|null
     */
    public $reply;
    /**
     * Chat info
     * اطلاعات چت
     *
     * @var Chat|null
     */
    public $chat;
    /**
     * Sender chat info
     * چت ارسال کننده
     *
     * @var Chat|null
     */
    public $sender;
    /**
     * User info
     * اطلاعات ارسال کننده
     *
     * @var User|null
     */
    public $from;
    /**
     * Message date
     * تاریخ ارسال پیام
     *
     * @var int|null
     */
    public $date;
    /**
     * Media group id
     * آیدی آلبوم
     *
     * @var string
     */
    public $mediaGroupID;
    /**
     * Is edited?
     * ویرایش شده؟
     *
     * @var bool
     */
    public $edited;
    /**
     * Edit date
     * تاریخ ویرایش پیام
     *
     * @var int|null
     */
    public $editDate;
    /**
     * Is forwarded?
     * باز ارسال شده؟
     *
     * @var bool
     */
    public $forwarded;
    /**
     * Forward from user (if forwarded from a user)
     * کاربری که پیام آن باز ارسال شده است (در صورت باز ارسال از کاربر)
     *
     * @var User|null
     */
    public $forwardFrom;
    /**
     * Forward from chat (if forwarded from a chat)
     * چتی که پیام از آنجا باز ارسال شده است (در صورت باز ارسال از چت)
     *
     * @var Chat|null
     */
    public $forwardChat;
    /**
     * Forward from message id (if forwarded from a chat)
     * آیدی پیام در چت باز ارسال شده (در صورت باز ارسال از چت)
     *
     * @var int|null
     */
    public $forwardMsgId;
    /**
     * Forward from message signature (if forwarded from a chat and message has signature)
     * امضای پیام (در صورت باز ارسال از چت و داشتن امضا)
     *
     * @var string|null
     */
    public $forwardSig;
    /**
     * Forward date
     * تاریخ پیام باز ارسال شده
     *
     * @var int|null
     */
    public $forwardDate;
    /**
     * Entities
     * نهاد ها(علائمی همچون لینک، تگ، منشن و ...)
     *
     * @var Entity[]|null
     */
    public $entities;
    /**
     * Pinned message
     * پیام سنجاق شده
     *
     * @var Msg|null
     */
    public $pinnedMsg;
    /**
     * Message keyboard
     * دکمه های پیام
     *
     * @var array|null
     */
    public $key;
    /**
     * Via bot
     * رباتی که پیغام توسط آن ایجاد شده
     *
     * @var User|null
     */
    public $via;
    /**
     * User in chat object
     * کاربر در چت
     *
     * @var UserInChat|null
     */
    public $userInChat;

    public const TYPE_TEXT = 'text';
    public const TYPE_PHOTO = 'photo';
    public const TYPE_VOICE = 'voice';
    public const TYPE_VIDEO = 'video';
    public const TYPE_ANIM = 'anim';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_VIDEO_NOTE = 'video_note';
    public const TYPE_LOCATION = 'location';
    public const TYPE_DICE = 'dice';
    public const TYPE_STICKER = 'sticker';
    public const TYPE_CONTACT = 'contact';
    public const TYPE_DOC = 'doc';
    public const TYPE_POLL = 'poll';
    
    public const TYPE_NEW_MEMBERS = 'new_members';
    public const TYPE_LEFT_MEMBER = 'left_member';
    public const TYPE_NEW_TITLE = 'new_title';
    public const TYPE_NEW_PHOTO = 'new_photo';
    public const TYPE_DEL_PHOTO = 'del_photo';
    public const TYPE_NEW_GROUP = 'new_group';
    public const TYPE_NEW_SUPERGROUP = 'new_supergroup';
    public const TYPE_NEW_CHANNEL = 'new_channel';

    function __construct($msg, $base, $isInline = false){
        $this->_base = $base;

        if($isInline){
            $this->isInline = true;
            $this->inlineID = $msg;
            return;
        }
        $this->isInline = false;

        $this->id = $msg['message_id'];
        $this->started = false;
        if(isset($msg['text'])){
            $this->text = $msg['text'];
            $this->type = "text";
            if($this->started = preg_match('/^\/start(\s(['.(self::$acceptStartCode).']+)|)$/i', $this->text, $r))
                $this->startCode = @$r[2];
        }elseif(isset($msg['caption'])){
            $this->text = $msg['caption'];
        }
        if(isset($msg['photo'])){
            $this->type = "photo";
            $this->photo = [];
            foreach($msg['photo'] as $a){
                $this->photo[] = new msgData("photo", $a, $base);
            }
            $this->media = end($this->photo);
            $this->media_id = $this->media->id;
        }elseif(isset($msg['voice'])){
            $this->type = "voice";
            $this->voice = new msgData("voice", $msg['voice'], $base);
        }elseif(isset($msg['video'])){
            $this->type = "video";
            $this->media = new msgData("video", $msg['video'], $base);
            $this->media_id = $this->media->id;
            $this->video = $this->media;
        }elseif(isset($msg['animation'])){
            $this->type = "anim";
            $this->media = new msgData("anim", $msg['animation'], $base);
            $this->media_id = $this->media->id;
            $this->anim = $this->media;
        }elseif(isset($msg['audio'])){
            $this->type = "audio";
            $this->media = new msgData("audio", $msg['audio'], $base);
            $this->media_id = $this->media->id;
            $this->audio = $this->media;
        }elseif(isset($msg['video_note'])){
            $this->type = "video_note";
            $this->videoNote = new msgData("videonote", $msg['video_note'], $base);
        }
        elseif(isset($msg['location'])){
            $this->type = "location";
            $this->location = new location($msg['location'], $base);
        }
        elseif(isset($msg['dice'])){
            $this->type = "dice";
            $this->dice = new dice($msg['dice'], $base);
        }
        elseif(isset($msg['poll'])){
            $this->type = "poll";
            $this->poll = new Poll($msg['poll'], $base);
        }
        elseif(isset($msg['sticker'])){
            $this->type = "sticker";
            $this->sticker = new sticker($msg['sticker'], $base);
        }
        elseif(isset($msg['contact'])){
            $this->type = "contact";
            $this->contact = new contact($msg['contact'], $base);
        }
        elseif(isset($msg['new_chat_members'])){
            $this->type = "new_members";
            $this->newMembers = [];
            foreach($msg['new_chat_members']as$once)
                $this->newMembers[] = new user($once, $base);
        }
        elseif(isset($msg['left_chat_member'])){
            $this->type = "left_member";
            $this->leftMember = new user($msg['left_chat_member'], $base);
        }
        elseif(isset($msg['new_chat_title'])){
            $this->type = "new_title";
            $this->newTitle = $msg['new_chat_title'];
        }
        elseif(isset($msg['new_chat_photo'])){
            $this->type = "new_photo";
            $this->newPhoto = [];
            foreach($msg['new_chat_photo'] as $once)
                $this->newPhoto[] = new msgData("photo", $once, $base);
        }
        elseif(isset($msg['delete_chat_photo'])){
            $this->type = "del_photo";
            $this->delPhoto = true;
        }
        elseif(isset($msg['group_chat_created'])){
            $this->type = "new_group";
            $this->newGroup = true;
        }
        elseif(isset($msg['supergroup_chat_created'])){
            $this->type = "new_supergroup";
            $this->newSupergroup = true;
        }
        elseif(isset($msg['channel_chat_created'])){
            $this->type = "new_channel";
            $this->newChannel = true;
        }
        if(isset($msg['document'])){
            if(!$this->type){
                $this->type = "doc";
            }
            if(!$this->media){
                $this->media = new msgData("doc", $msg['document'], $base);
                $this->media_id = $this->media->id;
            }
            $this->doc = $this->media;
        }
        if(isset($msg['reply_to_message'])){
            $this->reply = new msg($msg['reply_to_message'], $base);
        }
        if(isset($msg['chat'])){
            $this->chat = new chat($msg['chat'], $base);
        }
        if(isset($msg['from'])){
            $this->from = new user($msg['from'], $base);
        }
        $this->date = $msg['date'];
        $this->edited = isset($msg['edit_date']);
        if($this->edited) $this->editDate = $msg['edit_date'];
        if(isset($msg['forward_from'])){
            $this->forwarded = true;
            $this->forwardFrom = new user($msg['forward_from'], $base);
        }
        elseif(isset($msg['forward_from_chat'])){
            $this->forwarded = true;
            $this->forwardChat = new chat($msg['forward_from_chat'], $base);
            $this->forwardMsgId = $msg['forward_from_message_id'];
            $this->forwardSig = @$msg['forward_signature'];
        }
        else{
            $this->forwarded = false;
        }
        if($this->forwarded)
            $this->forwardDate = @$msg['forward_date'];
        if(isset($msg['entities']))
            $e = $msg['entities'];
        elseif(isset($msg['caption_entities']))
            $e = $msg['caption_entities'];
        else
            $e = [];
        $this->entities = [];
        foreach($e as $once)
            $this->entities[] = new entity($once, $base);
        if(isset($msg['pinned_message'])){
            $this->pinnedMsg = new msg($msg['pinned_message'], $base);
        }
        if(isset($msg['reply_markup'])){
            try{
                $this->key = filterArray3D($msg['reply_markup'], ['text'=>"text", 'callback_data'=>"data", 'url'=>"url", 'login_url'=>"login"],null);
            }catch(Exception $e){
                $this->key = null;
            }
        }
        if($this->chat && $this->from && $this->chat->id != $this->from->id){
            $this->userInChat = new userInChat($this->from, $this->chat, $base);
        }
        if($_ = @$msg['via_bot'])
            $this->via = new user($_, $base);
        if($_ = @$msg['sender_chat'])
            $this->sender = new Chat($_, $this);
        if($_ = @$msg['media_group_id'])
            $this->mediaGroupID = $_;
    }
    
    /**
     * Reply to message and send text message
     * پاسخ به پیام با ارسال متن
     *
     * @param string $text
     * @param array $args
     * @return msg
     */
    function replyText($text, $args=[]){
        if(gettype($text) == "array"){
            $args = array_merge($text, $args);
        }else{
            $args['text'] = $text;
        }
        $args['id'] = $this->chat->id;
        $args['reply'] = $this->id;
        return $this->_base->sendMsg($args);
    }
    
    /**
     * Reply to message and send x message
     * پاسخ به پیام با ارسال پیامی با نوع x
     *
     * @param string $type
     * @param array $args
     * @return msg|false
     */
    function reply($type, $args=[]){
        if(gettype($type) == "array"){
            $args = array_merge($type, $args);
            $type = @$args['type'];
            unset($args['type']);
        }
        $args['id'] = $this->chat->id;
        $args['reply'] = $this->id;
        return $this->_base->send($type, $args);
    }

    /**
     * Send message
     * ارسال پیام
     *
     * @param string $text
     * @param array $args
     * @return msg|false
     */
    function sendMsg($text, $args = []){
        if(gettype($text) == "array"){
            $args = array_merge($text, $args);
        }else{
            $args['text'] = $text;
        }
        $args['id'] = $this->chat->id;
        return $this->_base->sendMsg($args);
    }
    
    /**
     * Send x message
     * ارسال پیام با ارسال پیامی با نوع x
     *
     * @param string $type
     * @param array $args
     * @return msg|false
     */
    function send($type, $args=[]){
        if(gettype($type) == "array"){
            $args = array_merge($type, $args);
            $type = @$args['type'];
            unset($args['type']);
        }
        $args['id'] = $this->chat->id;
        return $this->_base->send($type, $args);
    }

    /**
     * Delete message
     * حذف پیام
     *
     * @return bool
     */
    function del(){
        return $this->_base->call('deletemessage', ['id' => $this->chat->id, 'msg' => $this->id]);
    }
    
    /*function edit($text, $media=null, $args=[]){
        if($this->type == "text"){
            $args = array_merge($media, $args);
            return new msg($this->_base->call('editmessagetext', array_merge(['id'=>$this->chat->id, 'msg'=>$this->id, 'text'=>$text], $args)), $this->_base);
        }else{
            
        }
    }*/
    
    /**
     * Edit message text
     * ویرایش متن پیام
     *
     * @param string $text
     * @param array $args
     * @return Msg|false
     */
    function editText($text, $args=[]){
        if(gettype($text)=="array")
            $args = array_merge($args, $text);
        else
            $args['text'] = $text;

        if($this->isInline){
            $args['inlineMsg'] = $this->inlineID;
        }
        else{
            $args['id'] = $this->chat->id;
            $args['msg'] = $this->id;
        }

        if($this->type == "text" || !$this->type){
            return $this->_base->editMsgText($args);
        }else{
            return $this->_base->editMsgCaption($args);
        }
    }

    /**
     * Edit message caption
     * ویرایش عنوان پیام
     *
     * @param string $text
     * @param array $args
     * @return Msg|false
     */
    function editCaption($text, $args=[]){
        if(gettype($text)=="array")
            $args = array_merge($args, $text);
        else
            $args['text'] = $text;

        if($this->isInline){
            $args['inlineMsg'] = $this->inlineID;
        }
        else{
            $args['id'] = $this->chat->id;
            $args['msg'] = $this->id;
        }

        return $this->_base->editMsgCaption($args);
    }
    
    /**
     * Edit message keyboard
     * ویرایش دکمه های پیام
     *
     * @param array $newKey
     * @return Msg|false
     */
    function editKey($newKey){
        $ar = [
            'key' => $newKey
        ];
        if($this->isInline){
            $ar['inlineMsg'] = $this->inlineID;
        }
        else{
            $ar['id'] = $this->chat->id;
            $ar['msg'] = $this->id;
        }
        $r = $this->_base->call('editmessagereplymarkup', $ar);
        if($r)
            return new msg($r, $this->_base);
        else
            return false;
    }
    
    /**
     * Forward message
     * باز ارسال پیام
     *
     * @param mixed $id Chat id
     * @return Msg|false
     */
    function forward($id){
        $r = $this->_base->call("forwardmessage", ['id'=>$id, 'msg'=>$this->id, 'from'=>$this->chat->id]);
        if($r)
            return new msg($r, $this->_base);
        else
            return false;
    }
    /**
     * Forward message
     * باز ارسال پیام
     *
     * @param mixed $id Chat id
     * @return Msg|false
     */
    function forwardTo($id){
        $r = $this->_base->call("forwardmessage", ['id'=>$id, 'msg'=>$this->id, 'from'=>$this->chat->id]);
        if($r)
            return new msg($r, $this->_base);
        else
            return false;
    }

    /**
     * Forward message without linking to original message
     * باز ارسال پیام بدون نام
     *
     * @param mixed $id Chat id
     * @return Msg|false
     */
    function copyTo($id){
        $r = $this->_base->copyMsg(['id'=>$id, 'msg'=>$this->id, 'from'=>$this->chat->id]);
        if($r)
            return new msg($r, $this->_base);
        else
            return false;
    }

    /**
     * Pin message in chat
     * پین کردن پیام در چت
     *
     * @return bool
     */
    function pin(){
        return $this->_base->pinMsg(['chat' => $this->chat->id, 'msg'=>$this->id]);
    }

    /**
     * Unpin message from chat
     * برداشتن پین پیام از چت
     *
     * @return bool
     */
    function unpin(){
        return $this->_base->unpinMsg(['chat' => $this->chat->id, 'msg'=>$this->id]);
    }
}

class Poll{
    /**
     * @var MMB
     */
    private $_base;

    /**
     * ID
     * آیدی
     *
     * @var string
     */
    public $id;
    /**
     * Question
     * سوال
     *
     * @var string
     */
    public $text;
    /**
     * Options
     * گزینه ها
     *
     * @var PollOpt[]
     */
    public $options;
    /**
     * Voters count
     * تعداد رای دهندگان
     *
     * @var int
     */
    public $votersCount;
    /**
     * Is poll closed
     * آیا پول بسته است
     *
     * @var bool
     */
    public $isClosed;
    /**
     * Is anonymous
     * آیا ناشناس است
     *
     * @var bool
     */
    public $isAnonymous;
    /**
     * Type
     * نوع
     *
     * @var string
     */
    public $type;
    public const TYPE_REGULAR = 'regular';
    public const TYPE_QUIZ = 'quiz';

    /**
     * Is allowed multiple
     * آیا چند گزینه ای فعال است
     *
     * @var bool
     */
    public $multiple;
    /**
     * Correct option id
     * ایندکس گزینه صحیح
     *
     * @var int
     */
    public $correct;
    /**
     * Explansion
     * توضیحات
     *
     * @var string
     */
    public $explan;
    /**
     * Explansion entities
     * موجودیت های توضیحات
     *
     * @var  Entity[]
     */
    public $explanEntites;
    /**
     * Amount of time in seconds the poll will be active after creation
     * زمان فعال بودن نظرسنجی
     *
     * @var int
     */
    public $openPreiod;
    /**
     * Point in time when the poll will be automatically closed
     * زمانی که نظرسنجی خودکار بسته می شود
     *
     * @var int
     */
    public $closeDate;

    function __construct($b, $base){
        $this->_base = $base;
        $this->id = $b['id'];
        $this->text = $b['question'];
        $this->options = [];
        $this->votersCount = $b['total_voter_count'];
        $_ = $b['options'];
        foreach($_ as $__){
            $this->options[] = new PollOpt($__, $this, $base);
        }
        $this->isClosed = $b['is_closed'];
        $this->isAnonymous = $b['is_anonymous'];
        $this->type = $b['type'];
        $this->multiple = $b['allows_multiple_answers'];
        $this->correct = $b['correct_option_id'];
        $this->explan = $b['explanation'];
        $this->explanation_entities = [];
        $_ = $b['explanation_entities'];
        foreach($_ as $__){
            $this->explanation_entities[] = new Entity($__, $base);
        }
        $this->openPreiod = $b['open_period'];
        $this->closeDate = $b['close_date'];
    }
}

class PollOpt{
    /**
     * @var Mmb
     */
    private $_base;
    /**
     * @var Poll
     */
    private $_basep;

    /**
     * Text
     * متن
     *
     * @var string
     */
    public $text;
    /**
     * Voters count
     * تعداد رای ها به این گزینه
     *
     * @var int
     */
    public $votersCount;
    /**
     * Cent
     * درصد رای این گزینه
     *
     * @var float
     */
    public $cent;
    public function __construct($a, Poll $poll, $base)
    {
        $this->_base = $base;
        $this->_basep = $poll;
        $this->text = $a['text'];
        $this->votersCount = $a['voter_count'];
        $this->cent = $this->votersCount / $poll->votersCount * 100;
    }
}

class PollAnswer{
    /**
     * @var Mmb
     */
    private $_base;

    /**
     * Poll id
     * شناسه نظرسنجی
     *
     * @var string
     */
    public $id;
    /**
     * User
     * کاربر رای دهنده
     *
     * @var User
     */
    public $user;
    /**
     * Chosen options
     * گزینه های انتخاب شده
     *
     * @var int[]
     */
    public $options;
    /**
     * Chosen count
     * تعداد انتخاب ها
     *
     * @var int
     */
    public $chosenCount;

    public function __construct($a, $base)
    {
        $this->_base = $base;
        $this->id = $a['poll_id'];
        $this->user = new User($a['user'], $base);
        $this->options = $a['option_ids'];
        $this->chosenCount = count($this->options);
    }
}

class ChatMemberUpd{
    /**
     * @var Mmb
     */
    private $_base;

    /**
     * Chat
     * چت
     *
     * @var Chat
     */
    public $chat;
    /**
     * User
     * کاربر
     *
     * @var User
     */
    public $from;
    /**
     * Date
     * تاریخ
     *
     * @var int
     */
    public $date;
    /**
     * Old chat member status
     * وضعیت قدیمی کاربر
     *
     * @var ChatMember
     */
    public $old;
    /**
     * New chat member status
     * وضعیت جدید کاربر
     *
     * @var ChatMember
     */
    public $new;
    /**
     * Invite link
     * لینکی که کاربر با آن دعونت شده
     *
     * @var ChatInvite
     */
    public $inviteLink;

    /**
     * Is private chat
     * آیا چت خصوصی است
     *
     * @var bool
     */
    public $isPrivate;
    /**
     * آیا کاربر ربات را شروع کرد
     *
     * @var bool
     */
    public $isStart;
    /**
     * آیا کاربر ربات را بلاک کرد
     *
     * @var bool
     */
    public $isStop;

    public function __construct($a, $base)
    {
        $this->_base = $base;
        $this->chat = new Chat($a['chat'], $base);
        $this->from = new User($a['from'], $base);
        $this->date = $a['date'];
        $this->old = new ChatMember($a['old_chat_member'], $base);
        $this->new = new ChatMember($a['new_chat_member'], $base);
        if($_ = $a['invite_link'])
            $this->inviteLink = new ChatInvite($_, $this->chat->id, $base);

        $this->isPrivate = $this->chat->type == Chat::TYPE_PRIVATE;
        if($this->isPrivate){
            $this->isStart = $this->old->status == ChatMember::STATUS_KICKED &&
                                $this->new->status == ChatMember::STATUS_MEMBER;
            $this->isStop = $this->old->status == ChatMember::STATUS_MEMBER &&
                                $this->new->status == ChatMember::STATUS_KICKED;
        }
        else{
            $this->isStart = false;
            $this->isStop = false;
        }
    }
}

class JoinReq{
    /**
     * @var Mmb
     */
    private $_base;

    /**
     * Chat
     * چت
     *
     * @var Chat
     */
    public $chat;
    /**
     * User
     * کاربر
     *
     * @var User
     */
    public $from;
    /**
     * Date
     * تاریخ
     *
     * @var int
     */
    public $date;
    /**
     * User bio
     * بیوگرافی کاربر
     *
     * @var string
     */
    public $bio;
    /**
     * Invite link
     * لینک دعوت
     *
     * @var ChatInvite
     */
    public $inviteLink;

    public function __construct($a, $base)
    {
        $this->_base = $base;
        $this->chat = new Chat($a['chat'], $base);
        $this->from = new User($a['from'], $base);
        $this->date = $a['date'];
        $this->bio = @$a['bio'];
        if($_ = $a['invite_link'])
            $this->inviteLink = new ChatInvite($_, $this->chat->id, $base);
    }

    /**
     * Approve
     * تایید درخواست عضویت
     *
     * @return bool
     */
    public function approve(){
        return $this->_base->approveJoinReq($this->chat->id, $this->from->id);
    }

    /**
     * Decline
     * رد کردن درخواست عضویت
     *
     * @return bool
     */
    public function decline(){
        return $this->_base->declineJoinReq($this->chat->id, $this->from->id);
    }
}

// Copyright (C): t.me/MMBlib

class Dice{
    /**
     * Dice emoji
     * اموجی
     *
     * @var string
     */
    public $emoji;
    /**
     * Dice value
     * مفدار
     *
     * @var int
     */
    public $val;
    /**
     * @var MMB
     */
    private $_base;
    public function __construct($data, $base){
        $this->_base = $base;
        $this->emoji = $data['emoji'];
        $this->val = $data['value'];
    }
}

class BotCmd{
    /**
     * @var MMB
     */
    private $_base;
    function __construct($b, $base){
        $this->_base = $base;
        $this->cmd = $b['command'];
        $this->des = $b['description'];
    }
    
    function toAr(){
        return ['command'=>$this->cmd, 'description'=>$this->des];
    }
}

class MsgData{
    /**
     * @var Mmb
     */
    private $_base;
    /**
     * File id
     * آیدی فایل
     *
     * @var string
     */
    public $id;
    /**
     * File unique id
     * آیدی یکتای فایل
     *
     * @var string
     */
    public $uniqueID;
    /**
     * File size
     * حجم فایل
     *
     * @var int|null
     */
    public $size;
    /**
     * File name
     * اسم فایل
     *
     * @var string|null
     */
    public $name;
    /**
     * Thump
     *
     * @var MsgData|null
     */
    public $thumb;
    /**
     * Mime type
     * مایم تایپ
     *
     * @var string|null
     */
    public $mime;
    /**
     * Duration (for audios, videos and ...)
     * طول رسانه(برای صوت، ویدیو، ...)
     *
     * @var int|null
     */
    public $duration;
    /**
     * Photo, video or animation width
     * عرض عکس، ویدیو یا گیف
     *
     * @var int|null
     */
    public $width;
    /**
     * Photo, video or animation height
     * ارتفاع عکس، ویدیو یا گیف
     *
     * @var int|null
     */
    public $height;
    /**
     * Audio perfomer
     * ایفا کننده ی صوت
     *
     * @var string|null
     */
    public $perfomer;
    /**
     * Audio title
     * نام صوت
     *
     * @var string|null
     */
    public $title;
    /**
     * File extension
     * پسوند فایل
     *
     * @var string|null
     */
    public $ext;
    function __construct($type, $med, $base){
        $this->_base = $base;
        $this->id = $med['file_id'];
        $this->uniqueID = @$med['file_unique_id'];
        $this->size = @$med['file_size'];
        $this->name = @$med['file_name'];
        if($ext = $this->name){
            $ext = explode('.', $ext);
            $ext = end($ext);
            $this->ext = $ext;
        }
        if(isset($med['thumb']))
            $this->thumb = new msgData("photo", $med['thumb'], $base);
        if(isset($med['mime_type']))
            $this->mime = $med['mime_type'];
        if(isset($med['duration']))
            $this->duration = $med['duration'];
        if($type == "photo"){
            $this->width = $med['width'];
            $this->height = $med['height'];
        }
        elseif($type == "audio"){
            $this->perfomer = @$med['permofer'];
            $this->title = @$med['title'];
        }
        elseif($type == "video"){
            $this->width = $med['width'];
            $this->height = $med['height'];
        }
        elseif($type == "anim"){
            $this->width = $med['width'];
            $this->height = $med['height'];
        }
        elseif($type == "videoNote"){
            $this->duration = $med['length'];
        }
    }
    
    /**
     * Downlaod file
     * دانلود کردن فایل
     *
     * @param string $path Download file to ...
     * @return void
     */
    function download($path){
        return $this->_base->getFile($this->id)->download($path);
    }

    /**
     * Get file
     * دریافت اطلاعات فایل
     *
     * @return File|false
     */
    public function getFile(){
        return $this->_base->getFile($this->id);
    }
}

class Sticker{
    /**
     * @var MMB
     */
    private $_base;

    /**
     * File id
     * شناسه فایل
     *
     * @var string
     */
    public $id;
    /**
     * File unique id
     * شناسه یکتای فایل
     *
     * @var string
     */
    public $uniqueID;
    /**
     * Width
     * عرض
     *
     * @var int
     */
    public $width;
    /**
     * Height
     * ارتفاع
     *
     * @var int
     */
    public $height;
    /**
     * Is animated
     * آیا متحرک است
     *
     * @var bool
     */
    public $isAnim;
    /**
     * Thumb
     * تصویر کوچک
     *
     * @var MsgData
     */
    public $thumb;
    /**
     * Set name
     * نام بسته استیکر
     *
     * @var string
     */
    public $setName;
    /**
     * Mask position
     *
     * @var MaskPos
     */
    public $maskPos;
    /**
     * File size in bytes
     * حجم فایل به بایت
     *
     * @var int
     */
    public $size;
    function __construct($st, $base){
        $this->_base = $base;
        $this->id = $st['file_id'];
        $this->uniqueID = $st['file_unique_id'];
        $this->width = $st['width'];
        $this->height = $st['height'];
        $this->isAnim = $st['is_animated'];
        if(isset($st['thumb']))
            $this->thumb = new MsgData("photo", $st['thumb'], $base);
        $this->emoji = @$st['emoji'];
        $this->setName = @$st['set_name'];
        if(isset($st['mask_position']))
            $this->maskPos = new MaskPos(@$st['mask_position'], $base);
        $this->size = @$st['file_size'];
    }

    /**
     * Downlaod file
     * دانلود کردن فایل
     *
     * @param string $path Download file to ...
     * @return void
     */
    function download($path){
        return $this->_base->getFile($this->id)->download($path);
    }

    /**
     * Get file
     * دریافت اطلاعات فایل
     *
     * @return StickerSet|false
     */
    public function getFile(){
        return $this->_base->getFile($this->id);
    }

    /**
     * Get sticker set
     * دریافت اطلاعات بسته استیکر
     *
     * @return StickerSet|false
     */
    public function getSet(){
        return $this->_base->getStickerSet($this->setName);
    }
}

class MaskPos{
    /**
     * @var MMB
     */
    private $_base;

    /**
     * Point
     * موقعیت
     *
     * @var string
     */
    public $point;
    public const POINT_FOREHEAD = 'forehead';
    public const POINT_EYES = 'eyes';
    public const POINT_MOUTH = 'mouth';
    public const POINT_CHIN = 'chin';
    /**
     * X
     *
     * @var double
     */
    public $x;
    /**
     * Y
     *
     * @var double
     */
    public $y;
    /**
     * Scale
     *
     * @var double
     */
    public $scale;
    public function __construct($a, $base)
    {
        $this->_base = $base;
        $this->point = $a['point'];
        $this->x = $a['x_shift'];
        $this->y = $a['y_shift'];
        $this->scale = $a['scale'];
    }
}

class StickerSet{
    /**
     * @var MMB
     */
    private $_base;

    /**
     * Set name
     * نام
     *
     * @var string
     */
    public $name;
    /**
     * Set title
     * عنوان
     *
     * @var string
     */
    public $title;
    /**
     * Contains animated sticker
     * آیاا استیکر متحرک دارد
     *
     * @var bool
     */
    public $hasAnim;
    /**
     * Contains mask sticker
     * آیا استیکر ماسک دارد
     *
     * @var bool
     */
    public $hasMask;
    /**
     * Pack stickers
     * استیکر ها
     *
     * @var Sticker[]
     */
    public $stickers;
    /**
     * Thumb
     * عکس کوچک
     *
     * @var MsgData
     */
    public $thumb;
    function __construct($a, $base){
        $this->_base = $base;
        $this->name = $a['name'];
        $this->title = $a['title'];
        $this->hasAnim = $a['is_animated'];
        $this->hasMask = $a['contains_masks'];
        $this->stickers = [];
        foreach($a['stickers'] as $once)
            $this->stickers[] = new Sticker($once, $base);
        if(isset($a['thumb']))
            $this->thumb = new MsgData("photo", $a['thumb'], $base);
    }
}

class Contact{
    /**
     * @var MMB
     */
    private $_base;

    /**
     * Contact number
     * شماره کاربر
     *
     * @var string
     */
    public $num;
    /**
     * First name
     * نام کوچک مخاطب
     *
     * @var string
     */
    public $firstName;
    /**
     * Last name
     * نام بزرگ مخاطب
     *
     * @var string
     */
    public $lastName;
    /**
     * Full name
     * نام کامل مخاطب
     *
     * @var string
     */
    public $name;
    /**
     * User id
     * ایدی عددی صاحب مخاطب
     *
     * @var int
     */
    public $userID;
    function __construct($con, $base){
        $this->_base = $base;
        $this->num = $con['phone_number'];
        $this->firstName = @$con['first_name'];
        $this->lastName = @$con['last_name'];
        $this->name = $this->firstName . ($this->lastName ? " " . $this->lastName : "");
        $this->userID = @$con['user_id'];
    }

    /**
     * Check number valid
     * بررسی اعتبار شماره یا کد کشور
     *
     * @param string $country
     * @return boolean
     */
    public function isValid($country = '98'){
        return (bool)preg_match('/^(00|\+|)' . $country . '/', $this->num);
    }
}

class ChatInvite{
    /**
     * @var MMB
     */
    private $_base;

    /**
     * Invite link
     * لینک دعوت
     *
     * @var string
     */
    public $link;

    /**
     * Chat
     * چت - داده ی نا امن
     *
     * @var mixed
     */
    public $chatLink;

    /**
     * Creator
     * سازنده لینک
     *
     * @var User
     */
    public $creator;

    /**
     * Creates join request
     * عضویت با تایید
     *
     * @var bool
     */
    public $joinReq;

    /**
     * Is primary
     *
     * @var bool
     */
    public $primary;

    /**
     * Is revoked
     * آیا لینک باطل شده است
     *
     * @var bool
     */
    public $revoked;

    /**
     * Name
     * اسم
     * 
     * @var string
     */
    public $name;

    /**
     * Expire date
     * تاریخ انقضا
     *
     * @var int
     */
    public $expire;

    /**
     * Member limit
     * محدودیت تعداد
     *
     * @var int
     */
    public $limit;

    /**
     * Pending join requests count
     * تعداد کاربران منتظر برای تایید
     *
     * @var int
     */
    public $pendings;

    public function __construct($inv, $chat, $base){
        $this->_base = $base;
        $this->link = $inv['invite_link'];
        $this->chatLink = $chat;
        $this->creator = new User($inv['creator'], $base);
        $this->primary = $inv['is_primary'];
        $this->revoked = $inv['is_revoked'];
        $this->name = @$inv['name'];
        $this->expire = @$inv['expire_date'];
        $this->limit = @$inv['member_limit'];
        $this->pendings = @$inv['pending_join_request_count'];
    }

    /**
     * Edit link
     * ویرایش لینک دعوت
     *
     * @param array $args
     * @return ChatInvite|false
     */
    public function edit($args){
        if(!$args['chat'])
            $args['chat'] = $this->chatLink;
        $args['link'] = $this->link;
        return $this->_base->editInviteLink($args);
    }

    /**
     * Edit link
     * ویرایش لینک دعوت
     *
     * @param array $args
     * @return ChatInvite|false
     */
    public function revoke(){
        $args = [
            'chat' => $this->chatLink,
            'link' => $this->link
        ];
        return $this->_base->revokeInviteLink($args);
    }
}

// Copyright (C): t.me/MMBlib

class Location{
    /**
     * @var MMB
     */
    private $_base;
    function __construct($loc, $base){
        $this->_base = $base;
        $this->longitude = $loc['longitude'];
        $this->latitude = $loc['latitude'];
    }
}

class Entity{
    /**
     * @var MMB
     */
    private $_base;
    function __construct($e, $base){
        $this->_base = $base;
        $this->type = $e['type'];
        $this->offset = $e['offset'];
        $this->len = $e['length'];
        if($this->type == "text_link")
            $this->url = @$e['url'];
        if($this->type == "text_mention")
            $this->user = new user(@$e['user'], $base);
        $this->lang = @$e['language'];
    }
}

class WebhookInfo{
    /**
     * @var MMB
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

    function __construct($data, $base){
        $this->_base = $base;
        $this->url = $data['url'];
        $this->pendings = $data['pending_update_count'];
        $this->ip = $data['ip_address'];
        $this->lastErrorTime = $data['last_error_date'];
        $this->lastError = $data['last_error_message'];
        $this->maxConnections = $data['max_connections'];
        $this->allowedUpds = $data['allowed_updates'];
    }
}

class UserProfs{
    /**
     * Photos
     * عکس ها
     *
     * @var MsgData[][]
     */
    public $photos;

    /**
     * Total count
     * تعداد کل
     *
     * @var int
     */
    public $count;

    /**
     * @var MMB
     */
    private $_base;
    function __construct($v, $base){
        $this->_base = $base;
        $this->count = $v['total_count'];
        $this->photos = [];
        foreach($v['photos']as$once){
            $a=[];
            foreach($once as $x)
                $a[] = new msgData("photo", $x, $base);
            $this->photos[] = $a;
        }
    }
}

class File{
    /**
     * File id
     * آیدی فایل
     *
     * @var string
     */
    public $id;
    /**
     * File path
     * آدرس فایل
     * 
     * You can download file by following url:
     * https://api.telegram.org/file/bot[TOKEN]/[FILE_PATH]
     * شما با لینک بالا می توانید فایل را دانلود کنید
     * 
     * And you can use download function:
     * $myFile->download("temps/test.txt");
     * همچنین از تابع دانلود نیز می توانید استفاده کنید
     * 
     * @var string
     */
    public $path;
    /**
     * File size
     * حجم فایل
     *
     * @var int
     */
    public $size;
    /**
     * File unique id
     * آیدی یکتای فایل
     *
     * @var int
     */
    public $uniqueID;
    /**
     * @var MMB
     */
    private $_base;
    function __construct($f, $base){
        $this->_base = $base;
        $this->id = $f['file_id'];
        $this->path = $f['file_path'];
        $this->size = $f['file_size'];
        $this->uniqueID = $f['unique_id'];
    }
    
    /**
     * Download file
     * دانلود فایل
     *
     * @param string $path Paste path | محل قرار گیری فایل
     * @return bool
     */
    function download($path){
        return $this->_base->copyByFilePath($this->path, $path);
    }
    
}

class ChatPhoto{
    /**
     * Small photo (160 * 160)
     * تصویر کوچک (160 * 160)
     *
     * @var MsgData
     */
    public $small;
    /**
     * Big photo (640 * 640)
     * تصویر بزرگ (640 * 640)
     *
     * @var MsgData
     */
    public $big;
    /**
     * @var MMB
     */
    private $_base;
    function __construct($v, $base){
        $this->_base = $base;
        $this->small = new msgData("photo", ['file_id'=>$v['small_file_id'], 'width'=>160, 'height'=>160], $base);
        $this->big = new msgData("photo", ['file_id'=>$v['big_file_id'], 'width'=>640, 'height'=>640], $base);
    }
}

class ChatMember{
    /**
     * User info
     * اطلاعات کاربر
     *
     * @var user
     */
    public $user;
    /**
     * User status
     * مقام کاربر
     *
     * @var string
     */
    public $status;
    public const STATUS_CREATOR = 'creator';
    public const STATUS_ADMIN = 'administrator';
    public const STATUS_MEMBER = 'member';
    public const STATUS_LEFT = 'left';
    public const STATUS_RESTRICTED = 'restricted';
    public const STATUS_KICKED = 'kicked';

    /**
     * User title
     * لقب کاربر
     *
     * @var string
     */
    public $title;
    /**
     * Until date
     *
     * @var int
     */
    public $untilDate;
    /**
     * Is join?
     * عضویت کاربر
     *
     * @var bool
     */
    public $isJoin;
    /**
     * Is admin?
     * ادمین بودن کاربر
     *
     * @var bool
     */
    public $isAdmin;
    /**
     * Is anonymous?
     * ناشناس بودن کاربر
     *
     * @var bool
     */
    public $isAnonymous;
    /**
     * Permissions for admins and restricted users
     * دسترسی ها، تنها برای ادمین ها و کاربران محدود شده موجود است
     *
     * @var ChatPer
     */
    public $per;
    /**
     * @var MMB
     */
    private $_base;
    function __construct($v, $base){
        $this->_base = $base;
        $this->user = new user($v['user'], $base);
        $s = $this->status = $v['status'];
        $this->title = @$v['custom_title'];
        $this->untilDate = @$v['until_date'];
        $this->isJoin = $s == "member" || $s == "creator" || $s == "administrator";
        $this->isAdmin = $s == "creator" || $s == "administrator";
        $this->isAnonymous = @$v['is_anonymous'];
        
        if($s == "creator"){
            $this->per = new ChatPer('*', $this->isAnonymous, $base);
        }
        elseif($s == 'restricted'){
            $this->per = new ChatPer($v, $this->isAnonymous, $base);
        }
    }
}

class ChatPer{
    /**
     * Member or admin permission
     * @var bool
     */
    public $sendMsg;
    /**
     * Member or admin permission
     * @var bool
     */
    public $sendMedia;
    /**
     * Member or admin permission
     * @var bool
     */
    public $sendPoll;
    /**
     * Member or admin permission
     * @var bool
     */
    public $sendOther;
    /**
     * Member or admin permission
     * @var bool
     */
    public $webPre;
    /**
     * Member or admin permission
     * @var bool
     */
    public $changeInfo;
    /**
     * Member or admin permission
     * @var bool
     */
    public $invite;
    /**
     * Member or admin permission
     * @var bool
     */
    public $pin;

    /**
     * Admin permission
     *
     * @var bool
     */
    public $manageChat;
    /**
     * Admin permission
     *
     * @var bool
     */
    public $delete;
    /**
     * Admin permission
     *
     * @var bool
     */
    public $manageVoiceChat;
    /**
     * Admin permission
     *
     * @var bool
     */
    public $restrict;
    /**
     * Admin permission
     *
     * @var bool
     */
    public $promote;
    /**
     * Admin permission
     *
     * @var bool
     */
    public $post;
    /**
     * Admin permission
     *
     * @var bool
     */
    public $editPost;
    /**
     * Admin permission
     *
     * @var bool
     */
    public $isAnonymous;

    public function __construct($a, $isAnonymous, $base)
    {
        if($a == '*'){
            $this->sendMsg = true;
            $this->sendMedia = true;
            $this->sendPoll = true;
            $this->sendOther = true;
            $this->webPre = true;
            $this->changeInfo = true;
            $this->invite = true;
            $this->pin = true;

            $this->manageChat = true;
            $this->delete = true;
            $this->manageVoiceChat = true;
            $this->restrict = true;
            $this->promote = true;
            $this->post = true;
            $this->editPost = true;
        }
        else{
            $this->sendMsg = $a['can_send_messages'] ?? false;
            $this->sendMedia = $a['can_send_media_messages'] ?? false;
            $this->sendPoll = $a['can_send_polls'] ?? false;
            $this->sendOther = $a['can_send_other_messages'] ?? false;
            $this->webPre = $a['can_add_web_page_previews'] ?? false;
            $this->changeInfo = $a['can_change_info'] ?? false;
            $this->invite = $a['can_invite_users'] ?? false;
            $this->pin = $a['can_pin_messages'] ?? false;

            $this->manageChat = $a['can_manage_chat'] ?? false;
            $this->delete = $a['can_delete_messages'] ?? false;
            $this->manageVoiceChat = $a['can_manage_voice_chats'] ?? false;
            $this->restrict = $a['can_restrict_members'] ?? false;
            $this->promote = $a['can_promote_members'] ?? false;
            $this->post = $a['can_post_messages'] ?? false;
            $this->editPost = $a['can_edit_messages'] ?? false;
        }
        $this->isAnonymous = $isAnonymous;
    }

}

// Copyright (C): t.me/MMBlib

class User{
    /**
     * User id
     * آیدی کاربر
     *
     * @var int
     */
    public $id;
    /**
     * First name
     * نام کوچک
     *
     * @var string
     */
    public $firstName;
    /**
     * Last name
     * نام بزرگ
     *
     * @var string|null
     */
    public $lastName;
    /**
     * Full name
     * نام کامل
     *
     * @var string
     */
    public $name;
    /**
     * Username
     * یوزرنیم
     *
     * @var string
     */
    public $username;
    /**
     * Is bot?
     * ربات بودن شخص
     *
     * @var bool
     */
    public $isBot;
    /**
     * Language code
     * کد زبان
     *
     * @var string
     */
    public $lang;
    /**
     * @var MMB
     */
    private $_base;
    function __construct($f, $base){
        $this->_base = $base;
        $this->id = $f['id'];
        $this->firstName = $this->first_name = $f['first_name'];
        $this->lastName = $this->last_name = @$f['last_name'];
        $this->name = $f['first_name'].(isset($f['last_name']) ? " ".$f['last_name'] : "");
        $this->username = @$f['username'];
        $this->isBot = $this->bot = @$f['is_bot'];
        $this->lang = @$f['language_code'];
    }
    
    /**
     * Get user profile photos
     * گرفتن تصاویر پروفایل کاربر
     *
     * @param int $offset
     * @param int $limit
     * @return userProfs|null
     */
    function getProfs($offset=null, $limit=null){
        return $this->_base->getUserProfs($this->id, $offset, $limit);
    }
    
    /**
     * Get user data
     * گرفتن دیتای کاربر
     *
     * @return mixed
     */
    function getData(){
        return $this->_base->getData($this->id);
    }
    /**
     * Set user data
     * تنظیم دیتای کاربر
     *
     * @return bool
     */
    function setData($data){
        return $this->_base->setData($this->id, $data);
    }
    /**
     * Check exists user data
     * بررسی موجودیت دیتای کاربر
     *
     * @return bool
     */
    function exData(){
        return $this->_base->exData($this->id);
    }

    /**
     * Get user status in chat
     * گرفتن وضعیت کاربر در چت
     *
     * @param mixed $chat
     * @return ChatMember
     */
    public function getMember($chat){
        if(!is_array($chat))
            $chat = ['chat' => $chat];
        $chat['user'] = $this->id;
    }

    /**
     * Send message to user
     * ارسال پیام به کاربر
     *
     * @param string $text
     * @param array $args
     * @return Msg|false
     */
    function sendMsg($text, $args = []){
        if(gettype($text) == "array"){
            $args = array_merge($text, $args);
        }else{
            $args['text'] = $text;
        }
        $args['id'] = $this->id;
        return $this->_base->sendMsg($args);
    }
    
    /**
     * Send x message
     * ارسال پیام به کاربر با ارسال پیامی با نوع x
     *
     * @param string $type
     * @param array $args
     * @return msg|false
     */
    function send($type, $args=[]){
        if(gettype($type) == "array"){
            $args = array_merge($type, $args);
            $type = @$args['type'];
            unset($args['type']);
        }
        $args['id'] = $this->id;
        return $this->_base->send($type, $args);
    }

}

class Chat{
    /**
     * Chat id
     * آیدی چت
     *
     * @var int
     */
    public $id;
    /**
     * Chat type
     * نوع چت
     *
     * @var string
     */
    public $type;
    public const TYPE_PRIVATE = 'private';
    public const TYPE_GROUP = 'group';
    public const TYPE_SUPERGROUP = 'supergroup';
    public const TYPE_CHANNEL = 'channel';

    /**
     * Chat title
     * عنوان چت
     *
     * @var string|null
     */
    public $title;

    /**
     * Chat username
     * نام کاربری چت
     *
     * @var string|null
     */
    public $username;
    /**
     * First name
     * نام کوچک
     *
     * @var string|null
     */
    public $firstName;
    /**
     * Last name
     * نام بزرگ
     *
     * @var string|null
     */
    public $lastName;
    /**
     * Full namd
     * نام کامل
     *
     * @var string
     */
    public $name;
    /**
     * Bio or description
     * بیوگرافی کاربر یا گروه یا کانال
     *
     * @var string|null
     */
    public $bio;
    /**
     * Photo
     * عکس پروفایل
     *
     * @var ChatPhoto|null
     */
    public $photo;
    /**
     * Invite link
     * لینک دعوت
     *
     * @var string|null
     */
    public $inviteLink;
    /**
     * Pinned message
     * پیغام سنجاق شده در چت
     *
     * @var Msg|null
     */
    public $pinnedMsg;
    /**
     * Slow mode delay
     * تاخیر حالت آهسته
     *
     * @var int|null
     */
    public $slowDelay;
    /**
     * Linked chat id
     * آیدی گروه یا کانال متصل به چت
     *
     * @var int|null
     */
    public $linkedChatID;
    /**
     * @var MMB
     */
    private $_base;
    function __construct($c, $base){
        $this->_base = $base;
        $this->id = $c['id'];
        $this->type = $c['type'];
        $this->title = @$c['title'];
        $this->username = @$c['username'];
        $this->firstName = @$c['first_name'];
        $this->lastName = @$c['last_name'];
        $this->name = $this->firstName . ($this->lastName ? ' ' . $this->lastName : '');
        $this->bio = @$c['bio'];
        if(@$c['des'])
            $this->bio = @$c['des'];
        if($_ = @$c['photo'])
            $this->photo = new ChatPhoto($_, $base);
        $this->inviteLink = @$c['invite_link'];
        $this->pinnedMsg = @$c['pinned_message'];
        $this->slowDelay = @$c['slow_mode_delay'];
        $this->linkedChatID = @$c['linked_chat_id'];
    }
    
    /**
     * Get chat member
     * گرفتن اطلاعات کاربر در چت
     *
     * @param mixed $user
     * @return ChatMember|false
     */
    function getMember($user){
        if(is_array($user)){
            $user['chat'] = $this->id;
            return $this->_base->getChatMember($user);
        }
        return $this->_base->getChatMember($this->id, $user);
    }
    
    /**
     * Get chat members count
     * گرفتن تعداد عضو های چت
     *
     * @return int|false
     */
    function getMemberNum(){
        return $this->_base->getChatMemberNum($this->id);
    }
    
    /**
     * Get chat members count
     * گرفتن تعداد عضو های چت
     *
     * @return int|false
     */
    function getMemberCount(){
        return $this->_base->getChatMemberCount($this->id);
    }
    
    /**
     * Ban member
     * حذف کاربر از چت
     *
     * @param mixed $user
     * @param int $until
     * @return bool|false
     */
    function ban($user, $until=null){
        if(is_array($user)){
            $user['chat'] = $this->id;
            return $this->_base->ban($user);
        }
        return $this->_base->ban($this->id, $user, $until);
    }
    
    /**
     * Unban member
     * رفع مسدودیت کاربر از چت
     *
     * @param mixed $user
     * @return bool|false
     */
    function unban($user){
        if(is_array($user)){
            $user['chat'] = $this->id;
            return $this->_base->unban($user);
        }
        return $this->_base->unban($this->id, $user);
    }
    
    /**
     * Restrict user
     * محدود کردن کاربر
     *
     * @param mixed $user
     * @param array $per
     * @param int $until
     * @return boll
     */
    function restrict($user, $per=[], $until=null){
        if(is_array($user)){
            $user['chat'] = $this->id;
            return $this->_base->restrict($user);
        }
        return $this->_base->restrict($this->id, $user, $per, $until);
    }
    
    /**
     * Promote user
     * ترفیع کاربر
     *
     * @param mixed $user
     * @param array $per
     * @return bool
     */
    function promote($user, $per=[]){
        if(is_array($user)){
            $user['chat'] = $this->id;
            return $this->_base->promote($user);
        }
        return $this->_base->promote($this->id, $user, $per);
    }
    
    /**
     * Set chat permissions
     * تنظیم دسترسی های گروه
     *
     * @param array $per
     * @return bool
     */
    function setPer($per){
        if(isset($per['per'])){
            $per['chat'] = $this->id;
            return $this->_base->setChatPer($per);
        }
        return $this->_base->setChatPer($this->id, $per);
    }
    
    /**
     * Get chat invite link
     * گرفتن لینک دعوت چت
     *
     * @return string
     */
    function getInviteLink(){
        return $this->_base->getInviteLink($this->id);
    }
    
    /**
     * Create invite link
     * ساخت لینک دعوت
     * [chat-name-expire-limit-joinReq]
     *
     * @param array $args
     * @return ChatInvite|false
     */
    function createInviteLink($args){
        $args['chat'] = $this->id;
        return $this->createInviteLink($args);
    }

    /**
     * Edit invite link
     * ویرایش لینک دعوت
     * [chat-link-name-expire-limit-joinReq]
     *
     * @param array $args
     * @return ChatInvite|false
     */
    public function editInviteLink($args){
        $args['chat'] = $this->id;
        return $this->editInviteLink($args);
    }
    
    /**
     * Set chat photo
     * تنظیم عکس چت
     *
     * @param mixed $photo
     * @return bool
     */
    function setPhoto($photo){
        if(is_array($photo)){
            $photo['id'] = $this->id;
            return $this->_base->setChatPhoto($photo);
        }
        return $this->_base->setChatPhoto($this->id, $photo);
    }
    
    /**
     * Delete chat photo
     * حذف عکس چت
     *
     * @return bool
     */
    function delPhoto(){
        return $this->_base->delChatPhoto($this->id);
    }
    
    /**
     * Set chat title
     * تنظیم عنوان چت
     *
     * @param string $title
     * @return bool
     */
    function setTitle($title){
        if(is_array($title)){
            $title['chat'] = $this->id;
            return $this->_base->setChatTitle($title);
        }
        return $this->_base->setChatTitle($this->id, $title);
    }
    
    /**
     * Set chat description
     * تنظیم توضیحات گروه
     *
     * @param string $des Description | توضیحات
     * @return bool
     */
    function setDes($des){
        if(is_array($des)){
            $des['chat'] = $this->id;
            return $this->_base->setChatDes($des);
        }
        return $this->_base->setChatDes($this->id, $des);
    }
    
    /**
     * Pin message
     * سنجاق کردن پیام
     *
     * @param mixed $msg Message id or message object | آیدی یا شئ پیام
     * @return bool
     */
    function pin($msg){
        if(is_array($msg)){
            $msg['chat'] = $this->id;
            return $this->_base->pinMsg($msg);
        }
        return $this->_base->pinMsg($this->id, $msg);
    }
    
    /**
     * Unpin message
     * حذف سنجاق پیام
     *
     * @param mixed $msg
     * @return bool
     */
    function unpin($msg = null){
        if(is_array($msg)){
            $msg['chat'] = $this->id;
            return $this->_base->unpinMsg($msg);
        }
        return $this->_base->unpinMsg($this->id, $msg);
    }
    
    /**
     * Leave chat
     * ترک چت
     *
     * @return bool
     */
    function leave(){
        return $this->_base->leave($this->id);
    }
    
    /**
     * Get admins list
     * گرفتن لیست ادمین ها
     *
     * @return ChatMember[]|false
     */
    function getAdmins(){
        return $this->_base->getChatAdmins($this->id);
    }
    
    function setStickerSet($setName){
        if(is_array($setName)){
            $setName['chat'] = $this->id;
            return $this->_base->setChatStickerSet($setName);
        }
        return $this->_base->setChatStickerSet($this->id, $setName);
    }
    
    function delStickerSet(){
        return $this->_base->delChatStickerSet($this->id);
    }

    /**
     * Send chat action
     * ارسال حالت چت
     *
     * @param mixed $action
     * @return bool
     */
    function action($action){
        if(gettype($action)=="array")
            return $this->_base->call('sendchataction', $action);
        return $this->_base->call('sendchataction', ['id'=>$this->id, 'action'=>$action]);
    }

    /**
     * Send message to chat
     * ارسال پیام به چت
     *
     * @param string $text
     * @param array $args
     * @return Msg|false
     */
    function sendMsg($text, $args = []){
        if(gettype($text) == "array"){
            $args = array_merge($text, $args);
        }else{
            $args['text'] = $text;
        }
        $args['id'] = $this->id;
        return $this->_base->sendMsg($args);
    }
    
    /**
     * Send x message
     * ارسال پیام به چت با ارسال پیامی با نوع x
     *
     * @param string $type
     * @param array $args
     * @return msg|false
     */
    function send($type, $args=[]){
        if(gettype($type) == "array"){
            $args = array_merge($type, $args);
            $type = @$args['type'];
            unset($args['type']);
        }
        $args['id'] = $this->id;
        return $this->_base->send($type, $args);
    }

}

class UserInChat{
    /**
     * @var MMB
     */
    private $_base;
    function __construct($user, $chat, $base){
        $this->_base = $base;
        $this->_u = $user;
        if(gettype($user)=="object")
            $this->_u = $user->id;
        $this->_c = $chat;
        if(gettype($chat)=="object")
            $this->_c = $chat->id;
    }
    
    /**
     * Get chat member
     * گرفتن اطلاعات کاربر در چت
     *
     * @return chatMember
     */
    function getMember(){
        return $this->_base->getChatMember($this->_c, $this->_u);
    }
    
    /**
     * Kick user
     * حذف کاربر از گروه|کانال
     *
     * @param int $until
     * @return bool
     */
    function kick($until=null){
        return $this->_base->kick($this->_c, $this->_u, $until);
    }
    /**
     * Ban user
     * حذف کاربر از گروه|کانال
     *
     * @param int $until
     * @return bool
     */
    function ban($until=null){
        if(is_array($until)){
            $until['chat'] = $this->_c;
            $until['user'] = $this->_u;
            return $this->_base->ban($until);
        }
        return $this->_base->ban($this->_c, $this->_u, $until);
    }
    
    /**
     * Unban user
     * رفع مسدودیت کاربر در گروه|کانال
     *
     * @return bool
     */
    function unban(){
        return $this->_base->unban($this->_c, $this->_u);
    }
    
    /**
     * Restrict user
     * محدود کردن کاربر
     *
     * @param array $per
     * @param int $until
     * @return bool
     */
    function restrict($per = [], $until = null){
        if(isset($per['per'])){
            $per['chat'] = $this->_c;
            $per['user'] = $this->_u;
            return $this->_base->restrict($per);
        }
        return $this->_base->restrict($this->_c, $this->_u, $per, $until);
    }
    
    /**
     * Promote user
     * ترفیع دادن به کاربر
     *
     * @param array $per
     * @return bool
     */
    function promote($per = []){
        if(isset($per['per'])){
            $per['chat'] = $this->_c;
            $per['user'] = $this->_u;
            return $this->_base->promote($per);
        }
        return $this->_base->promote($this->_c, $this->_u, $per);
    }
}

class Callback{
    /**
     * @var MMB
     */
    private $_base;
    /**
     * From user
     * از طرف کاربر
     *
     * @var user
     */
    public $from;
    /**
     * Message or fake message(for inline mode)
     * پیام اصلی، یا پیام فیک(بدون اطلاعات، فقط جهت استفاده از توابع) در حالت اینلاین
     *
     * @var msg
     */
    public $msg;
    /**
     * Callback button data
     * دیتای دکمه
     *
     * @var string
     */
    public $data;
    /**
     * Callback query id
     * آیدی کالبک
     *
     * @var string
     */
    public $id;
    /**
     * Is inline message
     * آیا پیام مربوط به حالت اینلاین است
     *
     * @var bool
     */
    public $isInline;
    function __construct($cl, $base){
        $this->_base = $base;
        $this->from = new user($cl['from'], $base);
        $this->data = $cl['data'];
        $this->id = $cl['id'];
        if(isset($cl['message'])){
            $this->msg = new msg($cl['message'], $base);
            $this->isInline = false;
        }
        if(isset($cl['inline_message_id'])){
            $this->isInline = true;
            $this->msg = new msg($cl['inline_message_id'], $base, true);
            /*$this->msg->isInline = true;
            $this->msg->inlineID = $cl['inline_message_id'];*/
        }
    }
    
    /**
     * Answer callback query
     * پاسخ به کالبک (نمایش پیغام و پایان دادن به انتظار تلگرام)
     * اگر شما از این تابع در کالبک های خود استفاده نکنید، در صورت استفاده ی زیاد از کالبک های ربات شما، تلگرام به شما اخطاری می دهد که پاسخ به کالبک ها بسیار طول می کشد!
     *
     * @param string $text
     * @param bool $alert Show alert | نمایش پنجره هنگام نمایش 
     * @return bool
     */
    function answer($text = null, $alert = false){
        if(is_array($text)){
            $text['id'] = $this->id;
            return $this->_base->answerCallback($text);
        }
        return $this->_base->answerCallback(['id'=>$this->id, 'text'=>$text, 'alert'=>$text ? $alert : null]);
    }
}

class Inline{
    /**
     * @var MMB
     */
    private $_base;
    /**
     * Inline query id
     * آیدی اینلاین کوئری
     *
     * @var string
     */
    public $id;
    /**
     * From user
     * از طرف کاربر
     *
     * @var User
     */
    public $from;
    /**
     * Query
     * کوئری
     *
     * @var string
     */
    public $query;
    /**
     * Offset
     * 
     *
     * @var 
     */
    public $offset;
    /**
     * Chat type
     * نوع چت
     *
     * @var string
     */
    public $type;
    public const TYPE_PRIVATE = 'private';
    public const TYPE_GROUP = 'group';
    public const TYPE_SUPERGROUP = 'supergroup';
    public const TYPE_CHANNEL = 'channel';
    /**
     * Is chat type, private
     * آیا در چت خصوصی درخواست انجام شده
     *
     * @var bool
     */
    public $isPrivate;
    /**
     * Is chat type, group or supergroup
     * آیا در گروه یا سوپر گروه درخواست انجام شده
     *
     * @var bool
     */
    public $isGroup;
    /**
     * Is chat type, channel
     * آیا در کانال درخواست انجام شده
     *
     * @var bool
     */
    public $isChannel;

    function __construct($in, $base){
        $this->_base = $base;
        $this->id = $in['id'];
        $this->from = new user($in['from'], $base);
        $this->query = $in['query'];
        $this->offset = $in['offset'];
        $this->type = @$in['type'];
        $this->isPrivate = $this->type == self::TYPE_PRIVATE;
        $this->isGroup = $this->type == self::TYPE_GROUP || $this->type == self::TYPE_SUPERGROUP;
        $this->isChannel = $this->type == self::TYPE_CHANNEL;
    }
    
    /**
     * Answer inline query
     * پاسخ به اینلاین کوئری
     *
     * @param array $results
     * @param array $args
     * @return bool
     */
    function answer($results, $args=[]){
        if(isset($results['results']))
            $args = array_merge($results, $args);
        else
            $args['results'] = $results;
        $args['id'] = $this->id;
        return $this->_base->call('answerinlinequery', $args);
    }
}

class ChosenInline{
    /**
     * @var Mmb
     */
    private $_base;
    /**
     * Selected id
     * آیدی ایتم انتخاب شده
     *
     * @var string
     */
    public $id;
    /**
     * User
     * کاربر
     *
     * @var User
     */
    public $from;
    /**
     * Message id
     * شناسه پیام
     *
     * @var string
     */
    public $msgID;
    /**
     * Fake message
     * پیام فیک
     *
     * @var Msg
     */
    public $msg;
    /**
     * Query
     * پیام درخواست اینلاین
     *
     * @var string
     */
    public $query;
    public function __construct($r, $base)
    {
        $this->_base = $base;
        $this->id = $r['result_id'];
        $this->from = new User($r['from'], $base);
        $this->msgID = @$r['inline_message_id'];
        if($this->msgID)
            $this->msg = new Msg($this->msgID, $base, true);
        $this->query = $r['query'];
    }
}

// Copyright (C): t.me/MMBlib

class MmbMySql{
    private $_ = null;
    /**
     * Create mmbmysql object and connect to mysql database
     * ساخت شئ mmbmysql و اتصال به دیتابیس مای اس کیو ال
     *
     * @param string $host Host address (Localhost: '127.0.0.1') آدرس سرور حاوی دیتابیس
     * @param string $username Username | نام کاربری
     * @param string $password Password | رمز عبور
     * @param string $dbname Database name | نام دیتابیس
     */
    function __construct(string $host, string $username, string $password, string $dbname){
        $this->_ = mysqli_connect($host, $username, $password, $dbname);
    }
    /**
     * Query
     * ارسال کوئری
     *
     * @param string $query
     * @return mysqli_result|bool
     */
    function query(string $query){
        return mysqli_query($this->_, $query);
    }

    /**
     * Get where query with array
     * گرفتن کد بعد از where ایمن با آرایه
     * 
     * Example:
     * مثال:
     * $query = "SELECT * FROM `users` where " . $mmbMySql->getWhere(['name' => "Mahdi"]);
     * $res = $mmbMySql->query($query); // mixed value(isn't array)
     *
     * @param array $where
     * @param string $oper
     * @return string
     */
    function getWhere($where, $oper = "AND"){
        if(gettype($where) == "string"){
            return $where;
        }else{
            $r = "";
            foreach($where as $a => $b){
                $r .= $r==""?"":" $oper ";
                $r .= "`".$a."`='".addslashes($b)."'";
            }
            return $r;
        }
    }

    /**
     * Select and return first result(or false)
     * انتخاب و برگرداندن اولین نتیجه(یا false)
     *
     * @param string $table
     * @param array|string|false $where
     * @param string $oper
     * @return array|false
     */
    function selectOnce($table, $where = false, $oper = "AND"){
        $q = $this->query("SELECT * FROM `$table`" . ($where ? " WHERE ".$this->getWhere($where, $oper) : "") . " LIMIT 1");
        if(mysqli_num_rows($q)){
            return mysqli_fetch_assoc($q);
        }else{
            return false;
        }
    }

    /**
     * Select and return all results
     * انتخاب و برگرداندن تمامی نتیجه ها
     *
     * @param string $table
     * @param array|string|false $where
     * @param string $oper
     * @return array
     */
    function selectAll($table, $where = false, $oper = "AND"){
        $q = $this->query("SELECT * FROM `$table`" . ($where ? " WHERE ".$this->getWhere($where, $oper) : ""));
        $r = [];
        $len = mysqli_num_rows($q);
        for ($i=0; $i < $len; $i++) {
            $r[] = mysqli_fetch_assoc($q);
        }
        return $r;
    }
    
    /**
     * Select each result rows and return as iterable
     * انتخاب تک تک سطر های نتیجه ها و برگردادن بصورت iterable
     * 
     * Example: مثال:
     * foreach($db->selectEach("users") as $user){
     *  if($user['id'] == 123456){
     *   echo "Found: ";
     *   print_r($user);
     *   break;
     *  }
     * }
     *
     * @param string $table
     * @param array|string|false $where
     * @param string $oper
     * @return Generator
     */
    function selectEach($table, $where = false, $oper = "AND"){
        $q = $this->query("SELECT * FROM `$table`" . ($where ? " WHERE ".$this->getWhere($where, $oper) : ""));
        $len = mysqli_num_rows($q);
        for ($i=0; $i < $len; $i++) {
            yield mysqli_fetch_assoc($q);
        }
    }
    
    /**
     * Select and return $max results
     * انتخاب و برگرداندن $max نتیجه
     *
     * @param string $table
     * @param int $max
     * @param array|string|false $where
     * @param string $oper
     * @return array
     */
    function select2($table, $max, $where = false, $oper = "AND"){
        $q = $this->query("SELECT * FROM `$table`" . ($where ? " WHERE ".$this->getWhere($where, $oper) : "") . " LIMIT $max");
        $r = [];
        $len = mysqli_num_rows($q);
        for ($i=0; $i < $len; $i++) {
            $r[] = mysqli_fetch_assoc($q);
        }
        return $r;
    }
    
    /**
     * Select and return n results with custom offset and limit
     * انتخاب و برگرداندن n نتیجه با افست و تعداد دستی
     *
     * @param string $table
     * @param int $offset Offset (Can is negation) | آفست (می تواند منفی هم باشد)
     * @param int $limit Limit | تعداد
     * @param array|string|false $where
     * @param string $oper
     * @return array
     */
    function select3($table, $offset, $limit, $where = false, $oper = "AND"){
        $q = $this->query("SELECT * FROM `$table`" . ($where ? " WHERE ".$this->getWhere($where, $oper) : ""));
        $r = [];
        $len = mysqli_num_rows($q);
        if($offset < 0){
            $offset = $len + $offset;
        }
        if($offset < 0){
            $offset = 0;
        }
        for ($i=0;$i<$offset;$i++){
            mysqli_fetch_assoc($q);
        }
        $max = $offset + $limit;
        for ($i=$offset; $i < $len && $i < $max; $i++) {
            $r[] = mysqli_fetch_assoc($q);
        }
        return $r;
    }

    /**
     * Select and return results num
     * انتخاب و برگرداندن تعداد نتیجه ها
     *
     * @param string $table
     * @param array|string|false $where
     * @param string $oper
     * @return int
     */
    function selectNum($table, $where = false, $oper = "AND"){
        $q = $this->query("SELECT * FROM `$table`" . ($where ? " WHERE ".$this->getWhere($where, $oper) : ""));
        return mysqli_num_rows($q);
    }

    /**
     * Insert row in table
     * افزودن یک ردیف به تیبل
     *
     * @param string $table
     * @param array $values
     * @return bool
     */
    function insert($table, $values){
        $names = "";
        $vals = "";
        foreach ($values as $name => $val) {
            $names .= ($names == "" ? "" : ", ")."`$name`";
            $vals .= ($vals == "" ? "" : ", ")."'".addslashes($val)."'";
        }
        return $this->query("INSERT INTO `$table` ($names) VALUES ($vals)");
    }

    /**
     * Update the row(s) values
     * بروزرسانی(ویرایش) سطر در تیبل
     *
     * @param string $table
     * @param array $values
     * @param array|string|false $where
     * @param string $oper
     * @return void
     */
    function update($table, $values, $where = false, $oper = "AND"){
        $set = "";
        foreach ($values as $name => $val) {
            $set .= ($set == "" ? "" : ", ")."`$name`='".addslashes($val)."'";
        }
        return $this->query("UPDATE `$table` SET $set" . ($where ? " WHERE ".$this->getWhere($where, $oper) : ""));
    }

    /**
     * Delete row(s)
     * حذف سطر(ها)
     *
     * @param string $table
     * @param array|string|false $where
     * @param string $oper
     * @return void
     */
    function delete($table, $where = false, $oper = "AND"){
        return $this->query("DELETE FROM `$table`" . ($where ? " WHERE ".$this->getWhere($where, $oper) : ""));
    }
}

class MmbJson{
    private $_;

    /**
     * Create new mmbjson object
     * ساخت یک شئ mmbjson
     *
     * @param string $dir Data directory | محل دیتا
     */
    public function __construct($dir){
        if(!file_exists($dir))
            mkdir($dir);
        $this->_ = $dir;
    }
    
    /**
     * Get number of rows in table
     * گرفتن تعداد سطر ها در تیبل
     *
     * @param string $table
     * @return int
     */
    public function number($table){
        return count(glob($this->_ . "/" . $table . "/*.json"));
    }
    
    /**
     * Select and return row value
     * انتخاب و گرفتن مقدار یک ردیف
     *
     * @param string $table
     * @param string $name
     * @return mixed|false
     */
    public function select($table, $name){
        return file_exists($this->_."/$table/$name.json") ?
            json_decode(file_get_contents($this->_."/$table/$name.json"), true) :
            false;
    }
    
    /**
     * Select all and return assoc array
     * انتخاب تمامی ردیف ها و برگرداندن آنها به صورت آرایه کلید دار
     *
     * @param string $table
     * @return array
     */
    public function selectAll($table){
        $r = [];
        $n = strlen($this->_) + strlen($table) + 2;
        foreach(glob($this->_."/$table/*.json") as $name_dir){
            $r[substr($name_dir, $n, strlen($name_dir) - $n - 4)] = json_decode(file_get_contents($name_dir), true);
        }
        return $r;
    }
    
    /**
     * Select and return all names
     * انتخاب و برگرداندن تمامی نام های ردیف ها
     *
     * @param string $table
     * @return array
     */
    public function selectNames($table){
        $r = [];
        $l = strlen($this->_) + strlen($table) + 2;
        foreach(glob($this->_."/$table/*.json") as $name_dir){
            $r[] = substr($name_dir, $l, strlen($name_dir) - $l - 5);
        }
        return $r;
    }
    
    /**
     * Select each name and return iterable object
     * انتخاب تمامی نام های ردیف ها و برگرداندن به صورت iterable
     * 
     * Example:
     * مثال:
     * foreach($mmbJson->selectEachNames("users") as $name){
     *   echo $name . PHP_EOL;
     * }
     *
     * @param string $table
     * @return 
     */
    public function selectEachNames($table){
        $l = strlen($this->_) + strlen($table) + 2;
        foreach(glob($this->_."/$table/*.json") as $name_dir){
            yield substr($name_dir, $l, strlen($name_dir) - $l - 5);
        }
    }
    
    /**
     * Insert(or replace) data in row with name
     * افزودن(یا جایگزینی) دیتای ردیف با اسم آن
     *
     * @param string $table
     * @param string $name
     * @param mixed $data
     * @return bool
     */
    public function insert($table, $name, $data){
        return file_put_contents($this->_."/$table/$name.json", json_encode($data)) !== false;
    }
    
    /**
     * Replace(or insert) data in row with name
     * جایگزینی(یا افزودن) دیتای ردیف با اسم آن
     *
     * @param string $table
     * @param string $name
     * @param mixed $newData
     * @return bool
     */
    public function replace($table, $name, $newData){
        return $this->insert($table, $name, $newData);
    }
    
    /**
     * Delete row
     * حذف ردیف
     *
     * @param string $table
     * @param string $name
     * @return bool
     */
    public function delete($table, $name){
        return @unlink($this->_."/$table/$name.json");
    }
    
    /**
     * Update row value keys(if value is array)
     * بروزرسانی کلید های ردیف(باید مقدار ردیف آرایه باشد)
     * 
     * Example:
     * مثال:
     * // Data, before updating: ['score' => 0, 'step' => "none"]
     * $mmbJson->update("users", $id, ['step' => "working"]);
     * // Data, after updating: ['score' => 0, 'step' => "working"]
     *
     * @param string $table
     * @param string $name
     * @param array $editVals
     * @return bool
     */
    public function update($table, $name, $editVals){
        $last = $this->select($table, $name);
        foreach($editVals as $key => $val){
            $last[$key] = $val;
        }
        return $this->replace($table, $name, $last);
    }
    
    /**
     * Create table
     * ساخت تیبل
     *
     * @param string $table
     * @return bool
     */
    public function createTable($table){
        return @mkdir($this->_ . "/$table");
    }

    /**
     * Create tables
     * ساخت چند تیبل
     *
     * @param array $tables
     * @return bool
     */
    public function createTables($tables){
        $successFull = true;
        foreach($tables as $table)
            if(!$this->createTable($table))
                $successFull = false;
        return $successFull;
    }

    /**
     * Select all rows(only values) and return iterable object
     * انتخاب تمامی ردیف ها(تنها مقدار آنها) و برگردادن iterable
     * 
     * Example:
     * مثال:
     * foreach($mmbJson->selectEach("users") as $val){
     *   var_dump($val);
     * }
     *
     * @param string $table
     * @return 
     */
    public function selectEach($table){
        foreach(glob($this->_."/$table/*.json") as $name_dir){
            yield json_decode(file_get_contents($name_dir), true);
        }
    }

    /**
     * Check exists table
     * بررسی وجود تیبل
     *
     * @param string $table
     * @return bool
     */
    public function existsTable($table){
        return file_exists($this->_."/$table/");
    }

    /**
     * Is database initialized
     * آیا دیتابیس مقدار دهی شده است
     *
     * @return boolean
     */
    public function isInit(){
        return count(glob($this->_)) ? true : false;
    }
    
}

class Keys{
    
    /**
     * Create require contact [single] button
     * ساخت تک دکمه درخواست شماره
     *
     * @param string $text
     * @return array
     */
    public static function reqContact($text){
        return ['text' => $text, 'contact' => true];
    }

    /**
     * Create require location [single] button
     * ساخت تک دکمه درخواست موقعیت
     *
     * @param string $text
     * @return array
     */
    public static function reqLocation($text){
        return ['text' => $text, 'location' => true];
    }

    /**
     * Create require poll [single] button
     * ساخت تک دکمه درخواست ساخت نظرسنجی
     *
     * @param string $text
     * @param string $type
     * @return array
     */
    public static function reqPoll($text, $type = Poll::TYPE_REGULAR){
        return ['text' => $text, 'poll' => ['type' => $type]];
    }

    /**
     * Create remove key action
     * ساخت حالت حذف دکمه ها
     *
     * @return string
     */
    public static function removeKey(){
        return '{"remove_keyboard": true}';
    }

    /**
     * Create force reply action
     * ساخت حالت ریپلای اجباری
     *
     * @return string
     */
    public static function forceRep($placeholder = null, $selective = null){
        $ar = [
            'force_reply' => true
        ];
        if($placeholder)
            $ar['input_field_placeholder'] = $placeholder;
        if($selective !== null)
            $ar['selective'] = $selective;
        return json_encode($ar);
    }
}

class Plugin{

    /**
     * MMB referenced
     *
     * @var MMB
     */
    public $mmb;
    public function __set_mmb(MMB $mmb){
        $this->mmb = $mmb;
    }

    private $_enabledListener = false;
    private $_listener = '';
    /**
     * Set update listener
     * Your function args: (Upd $upd)
     * تنظیم شنونده ی آپدیت
     *
     * @param string $funcName
     * @return void
     */
    protected function setUpdListener($funcName){
        $this->_listener = $funcName;
        if(!$this->_enabledListener){
            $this->_enabledListener = true;
            $this->mmb->addUpdListener($this, '__listener');
        }
    }

    public function __listener($upd){
        $f = $this->_listener;
        return $this->$f($upd);
    }

    
    public function init(array $settings){}

    /**
     * Override settings to your variant
     * مقدار های تنظیمات را در متغیر شما قرار میدهد
     *
     * @param array $variant
     * @param array $newSettings
     * @return void
     */
    protected function applySettings(array &$variant, array $newSettings){
        foreach($newSettings as $key => $value){
            if(isset($variant[$key])){
                $variant[$key] = $value;
            }
        }
    }

}

class Args{

    /**
     * Create sendDice method args
     * ساخت ورودی های تابع ارسال تاس
     *
     * @param mixed $chat
     * @param string $emoji
     * @param mixed $reply
     * @param array $key
     * @param bool $disNotif
     * @return array
     */
    public static function sendDice($chat, $emoji = null, $reply = null, $key = null, $disNotif = null){
        return [
            'chat_id' => $chat,
            'emoji' => $emoji,
            'reply' => $reply,
            'disNotif' => $disNotif,
            'key' => $key
        ];
    }

    /**
     * Create sendPoll method args
     * ساخت ورودی های تابع ارسال نظرسنجی
     *
     * @param mixed $chat
     * @param string $text
     * @param string[] $options
     * @param bool $isAnonymous
     * @param mixed $reply
     * @param array $key
     * @param bool $disNotif
     * @return array
     */
    public static function sendPoll($chat, $text, $options, $isAnonymous = null, $reply = null, $key = null, $disNotif = null){
        return [
            'chat_id' => $chat,
            'text' => $text,
            'type' => Poll::TYPE_REGULAR,
            'options' => $options,
            'isAnonymous' => $isAnonymous,
            'reply' => $reply,
            'key' => $key,
            'disNotif' => $disNotif,
        ];
    }

    /**
     * Create sendPoll method args (Quiz mode)
     * ساخت ورودی های تابع ارسال نظرسنجی (حالت امتحان)
     *
     * @param mixed $chat
     * @param string $text
     * @param string[] $options
     * @param int $correct
     * @param string $explan
     * @param string $explanMode
     * @param bool $isAnonymous
     * @param mixed $reply
     * @param array $key
     * @param bool $disNotif
     * @return array
     */
    public static function sendPollQuiz($chat, $text, $options, $correct, $explan = null, $explanMode = null, $isAnonymous = null, $reply = null, $key = null, $disNotif = null){
        return [
            'chat_id' => $chat,
            'text' => $text,
            'type' => Poll::TYPE_QUIZ,
            'options' => $options,
            'correct' => $correct,
            'explan' => $explan,
            'explanMode' => $explanMode,
            'isAnonymous' => $isAnonymous,
            'reply' => $reply,
            'key' => $key,
            'disNotif' => $disNotif,
        ];
    }

    /**
     * Create chat permission array
     * ساخت آرایه تنظیمات دسترسی گروه
     *
     * @param boolean $sendmsg
     * @param boolean $sendmedia
     * @param boolean $sendpoll
     * @param boolean $sendother
     * @param boolean $webpre
     * @param boolean $changeinfo
     * @param boolean $invite
     * @param boolean $pin
     * @return array
     */
    public static function chatPer(bool $sendmsg = true, bool $sendmedia = true, bool $sendpoll = true, bool $sendother = true, bool $webpre = true, bool $changeinfo = false, bool $invite = false, bool $pin = false){
        return [
            'sendmsg' => $sendmsg,
            'sendmedia' => $sendmedia,
            'sendpoll' => $sendpoll,
            'sendother' => $sendother,
            'webpre' => $webpre,
            'changeinfo' => $changeinfo,
            'invite' => $invite,
            'pin' => $pin,
        ];
    }

    /**
     * Create promote permission array
     * ساخت آرایه تنظیمات دسترسی های ادمین
     *
     * @param boolean $changeinfo
     * @param boolean $invite
     * @param boolean $pin
     * @param boolean $managechat
     * @param boolean $delete
     * @param boolean $managevoicechat
     * @param boolean $restrict
     * @param boolean $promote
     * @param boolean $post
     * @param boolean $editpost
     * @param boolean $anonymous
     * @return array
     */
    public static function promotePer(bool $changeinfo = true, bool $invite = true, bool $pin = true, bool $managechat = true, bool $delete = true, bool $managevoicechat = true, bool $restrict = true, bool $promote = true, bool $post = true, bool $editpost = true, bool $anonymous = false){
        return [
            'changeinfo' => $changeinfo,
            'invite' => $invite,
            'pin' => $pin,
            'managechat' => $managechat,
            'delete' => $delete,
            'managevoicechat' => $managevoicechat,
            'restrict' => $restrict,
            'promote' => $promote,
            'post' => $post,
            'editpost' => $editpost,
            'anonymous' => $anonymous,
        ];
    }

    /**
     * Create promote permission array, for channel promote
     * ساخت آرایه تنظیمات دسترسی های ادمین برای کانال
     *
     * @param boolean $changeinfo
     * @param boolean $post
     * @param boolean $editpost
     * @param boolean $delete
     * @param boolean $invite
     * @param boolean $managevoicechat
     * @param boolean $managechat
     * @param boolean $restrict
     * @param boolean $promote
     * @return array
     */
    public static function promoteChannelPer(bool $changeinfo = true, bool $post = true, bool $editpost = false, bool $delete = false, bool $invite = false, bool $managevoicechat = false, bool $managechat = false, bool $restrict = true, bool $promote = false){
        return [
            'changeinfo' => $changeinfo,
            'invite' => $invite,
            'managechat' => $managechat,
            'delete' => $delete,
            'managevoicechat' => $managevoicechat,
            'restrict' => $restrict,
            'promote' => $promote,
            'post' => $post,
            'editpost' => $editpost,
        ];
    }

    /**
     * Create promote permission array, for group promote
     * ساخت آرایه تنظیمات دسترسی های ادمین برای گروه
     *
     * @param boolean $changeinfo
     * @param boolean $invite
     * @param boolean $pin
     * @param boolean $managechat
     * @param boolean $delete
     * @param boolean $managevoicechat
     * @param boolean $restrict
     * @param boolean $promote
     * @param boolean $anonymous
     * @return array
     */
    public static function promotePerGroup(bool $changeinfo = true, bool $invite = true, bool $pin = true, bool $managechat = true, bool $delete = false, bool $managevoicechat = false, bool $restrict = false, bool $promote = false, bool $anonymous = false){
        return [
            'changeinfo' => $changeinfo,
            'invite' => $invite,
            'pin' => $pin,
            'managechat' => $managechat,
            'delete' => $delete,
            'managevoicechat' => $managevoicechat,
            'restrict' => $restrict,
            'promote' => $promote,
            'anonymous' => $anonymous,
        ];
    }

    /**
     * Get none permission array
     * گرفتن آزایه دسترسی خالی(بدون دسترسی)
     *
     * @return array
     */
    public static function nonePer(){
        return [
            'sendmsg' => false,
            'sendmedia' => false,
            'sendpoll' => false,
            'sendother' => false,
            'webpre' => false,
            'changeinfo' => false,
            'invite' => false,
            'pin' => false,
            'managechat' => false,
            'delete' => false,
            'managevoicechat' => false,
            'restrict' => false,
            'promote' => false,
            'post' => false,
            'editpost' => false,
        ];
    }

}


if (PHP_MAJOR_VERSION === 5 || (PHP_MAJOR_VERSION === 7 && PHP_MINOR_VERSION === 0)) {
    echo "Your php version not supported, please set version to 7.1+";
    mmb_error_throw("Php version not supported, only 7.1+ supported");
}

/**
 * Make keyboard
 * ساخت کیبورد
 *
 * @param array $key Keys
 * @param bool|null $inline is inline(null = auto)
 * @param boolean $resize Resize keyboard
 * @param boolean $encode Encode result with json
 * @param boolean $once One time keyboard
 * @param boolean $selective Selective
 * @return string|array
 */
function mkey($key, $inline=null, $resize=true, $encode=true, $once=false, $selective=false){
    if(isset($key['key'])){
        return mkey(
            $key['key'],
            $key['inline'] ?? null,
            $key['resize'] ?? true,
            $key['encode'] ?? true,
            $key['once'] ?? false,
            $key['selective'] ?? false
        );
    }
    if(($key = filterArray3D($key, [
        'text',
        'data'=>"callback_data",
        'text'=>"text",
        'callback_data'=>"callback_data",
        'url'=>"url",
        'switch_inline_query'=>"switch_inline_query",
        'inline'=>"switch_inline_query",
        'switch_inline_query_current_chat' => 'switch_inline_query_current_chat',
        'inline_this' => 'switch_inline_query_current_chat',
        'inlinethis' => 'switch_inline_query_current_chat',
        'inlineThis' => 'switch_inline_query_current_chat',
        'request_contact' => 'request_contact',
        'contact' => "request_contact",
        "request_location" => "request_location",
        "location" => "request_location",
        "request_poll" => "requset_poll",
        "poll" => "request_poll"
    ], null)) === false)
        mmb_error_throw("Invalid keyboard");
    if($inline === null){
        if($key != null)
            $inline = @isset($key[0][0]['callback_data']) || @isset($key[0][0]['url']) || @isset($key[0][0]['switch_inline_query']) || @isset($key[0][0]['switch_inline_query_current_chat']);
    }
    $a = [($inline?"inline_":"")."keyboard" => $key];
    if(!$inline && $resize) $a['resize_keyboard'] = $resize;
    if($once) $a['one_time_keyboard'] = true;
    if($selective) $a['selective'] = true;
    if($encode)
        $a = json_encode($a);
    return $a;
}

function mPers($ar){
    if(($ar = filterArray($ar, [
        'sendmsg' => 'can_send_messages',
        'sendmedia' => 'can_send_media_messages',
        'sendpoll' => 'can_send_polls',
        'sendother' => 'can_send_other_messages',
        'webpre' => 'can_add_web_page_previews',
        'changeinfo' => 'can_change_info',
        'invite' => 'can_invite_users',
        'pin' => 'can_pin_messages',

        'managechat' => 'can_manage_chat',
        'delete' => 'can_delete_messages',
        'managevoicechat' => 'can_manage_voice_chats',
        'restrict' => 'can_restrict_members',
        'promote' => 'can_promote_members',
        'post' => 'can_post_messages',
        'editpost' => 'can_edit_messages',
        'edit' => 'can_edit_messages',
        'anonymous' => 'is_anonymous',

        'can_send_messages' => 'can_send_messages',
        'can_send_media_messages' => 'can_send_media_messages',
        'can_send_polls' => 'can_send_polls',
        'can_send_other_messages' => 'can_send_other_messages',
        'can_add_web_page_previews' => 'can_add_web_page_previews',
        'can_change_info' => 'can_change_info',
        'can_invite_users' => 'can_invite_users',
        'can_pin_messages' => 'can_pin_messages',
        'can_manage_chat' => 'can_manage_chat',
        'can_delete_messages' => 'can_delete_messages',
        'can_manage_voice_chats' => 'can_manage_voice_chats',
        'can_restrict_members' => 'can_restrict_members',
        'can_promote_members' => 'can_promote_members',
        'can_post_messages' => 'can_post_messages',
        'can_edit_messages' => 'can_edit_messages',
        'is_anonymous' => 'is_anonymous',
    ])) === false)
        mmb_error_throw("Invalid permission array");
    return $ar;
}

function mInlineRes($results){
    $r = [];
    foreach($results as $res){
        $r[] = mInlineRes_A($res);
    }
    return json_encode($r);
}

function mInlineRes_A($data){
    if(($data = filterArray($data, [
        'id' => "id",
        'title' => "title",
        'msg' => "msg",
        'message' => "msg",
        'thumb' => "thumb",
        'cache' => "thumb",
        'des' => "des",
        'description' => "des",
        'photo' => "photo",
        'gif' => "gif",
        'mpeg4' => "mpeg4",
        'video' => "video",
        'audio' => "audio",
        'voice' => "voice",
        'doc' => "doc",
        'document' => "doc",
        'file' => "doc",
        //'location' => "location',"
        'contact' => "contact",
        'first' => 'first',
        'last' => 'last',
        'name' => "name",
    ])) === false)
        mmb_error_throw("Invalid inline query results data");
    $id = $data['id'] ?? rand(100000, 999999);
    $type = '';
    $media = '';
    $media_id = false;
    if(isset($data['photo'])){
        $type = 'photo';
        $media = $data['photo'];
    }
    elseif(isset($data['gif'])){
        $type = 'gif';
        $media = $data['gif'];
    }
    elseif(isset($data['mpeg4'])){
        $type = 'mpeg4_gif';
        $media = $data['mpeg4'];
    }
    elseif(isset($data['video'])){
        $type = 'video';
        $media = $data['video'];
    }
    elseif(isset($data['audio'])){
        $type = 'audio';
        $media = $data['audio'];
    }
    elseif(isset($data['voice'])){
        $type = 'voice';
        $media = $data['voice'];
    }
    elseif(isset($data['doc'])){
        $type = 'doc';
        $media = $data['doc'];
    }
    elseif(isset($data['contact'])){
        $type = 'contact';
    }
    else{
        $type = 'article';
    }
    if($media){
        if(is_string($media) && strpos($media, "://") === false){
            $media_id = true;
        }
    }

    $res = [
        'id' => $id,
        'type' => $type,
        'description' => $data['des'] ?? ""
    ];
    if($type == 'article'){
        $res['title'] = $data['title'] ?? "Untitled";
    }
    if($media){
        if(isset($data['title']))
            $res['title'] = $data['title'];
        if($type == 'mpeg4_gif'){
            $res['mpeg4' . ($media_id ? '_file_id' : '_url')] = $media;
        }
        else{
            $res[$type . ($media_id ? '_file_id' : '_url')] = $media;
        }
    }
    elseif($type == 'contact'){
        $res['contact'] = $data['contact'];
        if($data['name']){
            $f = $data['name'];
            $_ = strpos($f, " ");
            if($_ === false){
                $l = null;
            }
            else{
                $l = substr($f, $_ + 1);
                $f = substr($f, 0, $l);
            }
        }
        else{
            if(isset($data['first'])){
                $f = $data['first'];
                $l = $data['last'] ?? null;
            }
            elseif(isset($data['last'])){
                $f = $data['last'];
                $l = null;
            }
            else{
                $f = "Untitled";
                $l = null;
            }
        }
        $res['first_name'] = $f;
        $res['last_name'] = $l;
    }

    $msg = $data['msg'] ?? [];
    if(($msg = filterArray($msg, [
        'text' => "text",
        'caption' => "text",
        'mode' => "mode",
        'parse_mode' => "mode",
        'parsemode' => "mode",
        'diswebpre' => "disw",
        "disable_web_page_preview" => "disw",
        'key' => 'key',
    ])) === false)
        mmb_error_throw("Invalid inline query results message data");
    if($media){
        $res['caption'] = $msg['text'] ?? "";
        if($_ = $msg['parse_mode'] ?? null){
            $res['parse_mode'] = $_;
        }
    }
    elseif($type == 'article'){
        $cn = [
            'message_text' => $msg['text'] ??  "Untitled"
        ];
        if($_ = $msg['disw'] ?? null){
            $cn['disable_web_page_preview'] = $_;
        }
        if($_ = $msg['parse_mode'] ?? null){
            $cn['parse_mode'] = $_;
        }
        $res['input_message_content'] = $cn;
    }
    if($_ = $msg['key'] ?? false)
        $res['reply_markup'] = mkey($_, true, true, false);

    if($media){
        if(!$media_id){
            $res['thumb_url'] = $data['thumb'] ?? $media;
        }
    }
    elseif(isset($data['thumb'])){
        $res['thumb_url'] = $data['thumb'];
    }

    return $res;
}

function filterArray($array, $keys, $vals=null){
    if($keys == null)
        $a = "n";
    elseif(gettype($keys) == "array")
        $a = "a";
    else
        $a = "c";
    if($vals == null)
        $b = "n";
    elseif(gettype($vals) == "array")
        $b = "a";
    else
        $b = "c";
    $r = [];
    foreach($array as $key => $val){
        if($a == "a"){
            if(isset($keys[$key]))
                $key = $keys[$key];
            elseif(($_ = strtolower($key)) && isset($keys[$_]))
                $key = $keys[$_];
            else
                return false;
        }elseif($a == "c"){
            $key = $keys($key);
            if($key === false)
                return false;
        }
        if($b == "a"){
            if(isset($vals[$val]))
                $val = $vals[$val];
        }elseif($b == "c"){
            $val = $vals($key, $val);
        }
        $r[$key] = $val;
    }
    return $r;
}

function filterArray2D($array, $keys, $vals=null){
    foreach($array as $i => $val){
        if(($a = filterArray($val, $keys, $vals)) === false)
            return false;
        $array[$i] = $a;
    }
    return $array;
}

function filterArray3D($array, $keys, $vals=null){
    foreach($array as $i => $val){
        if(($a = filterArray2D($val, $keys, $vals)) === false)
            return false;
        $array[$i] = $a;
    }
    return $array;
}

function mmb_log($text){
    if(Mmb::$LOG)
        file_put_contents("mmb_log", @file_get_contents("mmb_log")."\n[" . date("Y/m/d H:i:s") . "] $text");
    return $text;
}

function mmb_error_throw($des, $must_throw_error = false){
    if(Mmb::$LOG)
        mmb_log($des);
    if($must_throw_error || Mmb::$HARD_ERROR)
        throw new Exception($des);
}

// Copyright (C): t.me/MMBlib
        

?>
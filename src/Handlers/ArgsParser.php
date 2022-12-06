<?php

namespace Mmb\Handlers; #auto

use Mmb\Controller\Menu;
use Mmb\Exceptions\TypeException;

class ArgsParser {

    use Defaultable;


    /**
     * تحلیل و تجزیه ورودی های ام ام بی
     *
     * @param Request $request
     * @return void
     */
    public function parse(Request $request) {

        $args = [];
        $smallWords = $this->smallWords();

        // Start parsing
        foreach($request->args as $key => $value) {

            $before_key = $key;
            $skip = false;

            // Ignore errors
            if(eqi($key, 'ignore')) {

                $request->ignoreError = $value;
                continue;

            }

            // -- Key parse --
            $key = strtolower($key);
            $replace = $smallWords[$key] ?? false;
            if($replace) {

                // Multi type
                if(is_array($replace)) {

                    $replace = $replace[$request->lowerMethod()]
                            ?? $replace[0]
                            ?? false;
                    if(!$replace) goto Next;

                }


                // Advanced replace
                if(startsWith($replace, '@')) {

                    // Run
                    $function = substr($replace, 1);
                    $add = $this->$function($request, $value, $key);
                    
                    // Add
                    foreach($add as $key2 => $value2) {
                        if(is_array($value2))
                            $value2 = json_encode($value2);
                        $args[$key2] = $value2;
                    }

                    // Skip
                    $skip = true;

                }

                // Basic replace
                else {
                    $key = $replace;
                }

            }
            // Invalid
            else {

                throw new \Mmb\Exceptions\MmbException("Invalid arg '$key' in method '{$request->method}'");

            }


            // -- Value parse --
            $interfaces = $this->interfaces();

            if(
                ($interface = $interfaces[$before_key] ?? false) &&
                $value instanceof $interface
            ) {

                $function = explode("\\", $interface);
                $function = end($function);
                $value = $value->$function();

            }


            // Add
            Next:
            if(!$skip) {
                if(is_array($value))
                    $value = json_encode($value);
                $args[$key] = $value;
            }

        }

        $request->args = $args;
        
    }



    /**
     * لیست اسامی کوتاه قابل قبول و جایگزین شونده ی درخواست ام ام بی
     *
     * @return array
     */
    public function smallWords() {

        static $value = [
            "id" => [
                "chat_id",
                'answercallbackquery' => "callback_query_id",
                'answerinlinequery' => "inline_query_id",
                'getfile' => "file_id"
            ],
            'chat' => "chat_id",
            "chatid" => "chat_id",
            'text' => "@text", // $this->text
            // "text" => [
            //     "text",
            //     "copymessage" => "caption",
            //     "sendpoll" => 'question'
            // ],
            'key' => "@parseKey", // $this->parseKey
            'menu' => "@parseMenu", // $this->parseMenu
            "msg" => "message_id",
            "msgid" => "message_id",
            "messageid" => "message_id",
            "mode" => "parse_mode",
            "parsemode" => "parse_mode",
            "reply" => "reply_to_message_id",
            "replytomsg" => "reply_to_message",
            'filter' => "allowed_updates",
            'offset' => "offset",
            'limit' => [
                "limit",
                'createchatinvitelink' => "member_limit",
                'editchatinvitelink' => "member_limit"
            ],
            "link" => 'invite_link',
            "invite" => 'invite_link',
            "invitelink" => "invite_link",
            "memberlimit" => "member_limit",
            'alert' => "show_alert",
            "showalert" => "show_alert",
            'from' => "from_chat_id",
            "fromchat" => "from_chat_id",
            'user' => "user_id",
            'caption' => "caption",
            "results"=>"@results",
            'url' => "url",
            "until"=>"until_date",
            'per' => "@per",
            'action' => "action",
            'photo' => "photo",
            'doc' => "document",
            'document' => "document",
            'voice' => "voice",
            'audio' => "audio",
            'video' => "video",
            'media' => "@media", // $this->media
            'medias' => "@media", // $this->media
            'anim' => "animation",
            'animation' => "animation",
            'sticker' => "sticker",
            'videonote' => "video_note",
            'diswebpre' => "disable_web_page_preview",
            'disnotif' => "disable_notification",
            'phone' => "phone_number",
            'name' => "first_name",
            'firstname' => "first_name",
            'first' => "first_name",
            'lastname' => "last_name",
            'last' => "last_name",
            'title' => "title",
            "performer" => "performer",
            "perf" => "performer",
            'des' => "description",
            'setname' => "sticker_set_name",
            'set_name' => "sticker_set_name",
            'cache' => "cache_time",
            'cachetime' => "cache_time",
            "personal" => "is_personal",
            "ispersonal" => "is_personal",
            "nextoffset" => "next_offset",
            "switchpmtext" => "switch_pm_text",
            "switchpmparameter" => "switch_pm_parameter",
            "cmds"=>"commands",
            "inlinemsg"=>"inline_message_id",
            // 'name' => "name", // Note
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

        return $value;

    }

    public function interfaces() {

        static $value = [
            'from_chat_id'          =>      \Mmb\Update\Interfaces\IChatID::class,
            'chat_id'               =>      \Mmb\Update\Interfaces\IChatID::class,
            'user_id'               =>      \Mmb\Update\Interfaces\IUserID::class,
            'message_id'            =>      \Mmb\Update\Interfaces\IMsgID::class,
            'reply_to_message_id'   =>      \Mmb\Update\Interfaces\IMsgID::class,
            'photo'                 =>      \Mmb\Update\Interfaces\IMsgDataID::class,
            'doc'                   =>      \Mmb\Update\Interfaces\IMsgDataID::class,
            'document'              =>      \Mmb\Update\Interfaces\IMsgDataID::class,
            'voice'                 =>      \Mmb\Update\Interfaces\IMsgDataID::class,
            'audio'                 =>      \Mmb\Update\Interfaces\IMsgDataID::class,
            'video'                 =>      \Mmb\Update\Interfaces\IMsgDataID::class,
            'media'                 =>      \Mmb\Update\Interfaces\IMsgDataID::class,
            'anim'                  =>      \Mmb\Update\Interfaces\IMsgDataID::class,
            'animation'             =>      \Mmb\Update\Interfaces\IMsgDataID::class,
            'sticker'               =>      \Mmb\Update\Interfaces\IMsgDataID::class,
            'videonote'             =>      \Mmb\Update\Interfaces\IMsgDataID::class,
            'file_id'               =>      \Mmb\Update\Interfaces\IMsgDataID::class,
            'callback_query_id'     =>      \Mmb\Update\Interfaces\ICallbackID::class,
            'inline_query_id'       =>      \Mmb\Update\Interfaces\IInlineID::class,
        ];

        return $value;

    }

    /**
     * ساخت کلید
     *
     * @param Request $request
     * @param string|array $value
     * @param string $key
     * @return array
     */
    public function parseKey(Request $request, $value, $key)
    {
        
        return [
            'reply_markup' => is_array($value) ? mkey($value) : $value,
        ];

    }

    /**
     * ساخت کلید از منو
     *
     * @param Request $request
     * @param string|array $value
     * @param string $key
     * @return array
     */
    public function parseMenu(Request $request, $value, $key)
    {

        if($value instanceof Menu)
        {
            return $this->parseKey($request, $value->getKey(), null);
        }
        else
        {
            throw new TypeException("Argument '$key' required Menu type, given " . typeOf($value));
        }

    }

    /**
     * متن
     *
     * @param Request $request
     * @param string $value
     * @param string $key
     * @return array
     */
    public function text(Request $request, $value, $key) {

        $method = $request->lowerMethod();

        // Normal message
        if($method == 'sendmessage')
            return [ $key => $value ];

        // Copy message
        if($method == 'copymessage')
            return [ 'caption' => $value ];

        // Send poll
        if($method == 'sendpoll')
            return [ 'question' => $value ];

        // Media message
        if(startsWith($method, 'send'))
            return [ 'caption' => $value ];
        
        // Else
        return [ $key => $value ];
        
    }

    public function media(Request $request, $value, $key) {

        static $fil = [
            'type' => "type",
            'text' => "caption",
            'media' => 'media',
            'mode' => "parse_mode",
            'thumb' => "thumb",
            'duration' => "duration",
            'title' => "title",
            'performer' => "permorfer"
        ];

        if($request->lowerMethod() == "sendmedia" || $request->lowerMethod() == "editmessagemedia"){
            $value = filterArray($value, $fil);
            $key = 'media';
        }else{
            $value = filterArray2D($value, $fil);
            $key = 'medias';
        }

        return [ $key => $value ];

    }

    public function per(Request $request, $value, $key) {

        if(gettype($value) == "array")
            $value = mPers($value);

        if(eqi($request->method, "promoteChatMember")){
            if(!is_array($value)){
                if($value instanceof \JsonSerializable)
                    $value = mPers($value->jsonSerialize());
                else
                    mmb_error_throw("Key '$key' is object! Array required");
            }

            return $value;
        }

        return [ $key => $value ];

    }

    public function results(Request $request, $value, $key) {

        if($key == "results" && gettype($value) == "array") {
            
            $value = mInlineRes($value);

        }

        return [ $key => $value ];

    }

}

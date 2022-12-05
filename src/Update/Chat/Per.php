<?php

namespace Mds\Mmb\Update\Chat; #auto

use Mds\Mmb\MmbBase;

class Per extends MmbBase implements \JsonSerializable
{
    
    /**
     * دسترسی ادمین یا عضو
     * @var bool
     */
    public $sendMsg;
    /**
     * دسترسی ادمین یا عضو
     * @var bool
     */
    public $sendMedia;
    /**
     * دسترسی ادمین یا عضو
     * @var bool
     */
    public $sendPoll;
    /**
     * دسترسی ادمین یا عضو
     * @var bool
     */
    public $sendOther;
    /**
     * دسترسی ادمین یا عضو
     * @var bool
     */
    public $webPre;
    /**
     * دسترسی ادمین یا عضو
     * @var bool
     */
    public $changeInfo;
    /**
     * دسترسی ادمین یا عضو
     * @var bool
     */
    public $invite;
    /**
     * دسترسی ادمین یا عضو
     * @var bool
     */
    public $pin;

    /**
     * دسترسی ادمین
     *
     * @var bool
     */
    public $manageChat;
    /**
     * دسترسی ادمین
     *
     * @var bool
     */
    public $delete;
    /**
     * دسترسی ادمین
     *
     * @var bool
     */
    public $manageVoiceChat;
    /**
     * دسترسی ادمین
     *
     * @var bool
     */
    public $restrict;
    /**
     * دسترسی ادمین
     *
     * @var bool
     */
    public $promote;
    /**
     * دسترسی ادمین
     *
     * @var bool
     */
    public $post;
    /**
     * دسترسی ادمین
     *
     * @var bool
     */
    public $editPost;
    /**
     * دسترسی ادمین
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
            $this->sendMsg = $a['can_send_messages'] ?? null;
            $this->sendMedia = $a['can_send_media_messages'] ?? null;
            $this->sendPoll = $a['can_send_polls'] ?? null;
            $this->sendOther = $a['can_send_other_messages'] ?? null;
            $this->webPre = $a['can_add_web_page_previews'] ?? null;
            $this->changeInfo = $a['can_change_info'] ?? null;
            $this->invite = $a['can_invite_users'] ?? null;
            $this->pin = $a['can_pin_messages'] ?? null;

            $this->manageChat = $a['can_manage_chat'] ?? null;
            $this->delete = $a['can_delete_messages'] ?? null;
            $this->manageVoiceChat = $a['can_manage_voice_chats'] ?? null;
            $this->restrict = $a['can_restrict_members'] ?? null;
            $this->promote = $a['can_promote_members'] ?? null;
            $this->post = $a['can_post_messages'] ?? null;
            $this->editPost = $a['can_edit_messages'] ?? null;
        }
        $this->isAnonymous = $isAnonymous;
    }

    /**
     * تبدیل شی به آرایه
     *
     * @return array
     */
    public function toArray(){
        $list = [
            'sendMsg',
            'sendMedia',
            'sendPoll',
            'sendOther',
            'webPre',
            'changeInfo',
            'invite',
            'pin',
            'manageChat',
            'delete',
            'manageVoiceChat',
            'restrict',
            'promote',
            'post',
            'editPost',
        ];
        $ar = [];
        foreach($list as $i){
            $value = $this->$i;
            if($value !== null){
                $ar[$i] = $value;
            }
        }
        return $ar;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }


    public static function makePers($ar)
    {
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

}

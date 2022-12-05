<?php

namespace Mds\Mmb\Tools; #auto

use Mds\Mmb\Update\Message\Data\Poll;

class Args
{

    /**
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

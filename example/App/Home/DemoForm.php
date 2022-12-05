<?php

namespace App\Home; #auto

class DemoForm 
{

    public function startForm()
    {
        
    }

    public function endForm()
    {
        
    }

    public function cancelForm()
    {
        replyText("عملیات لغو شد");
        return Start::invoke('start');
    }

    public function form()
    {
        $this->require('name');
        $this->optional('avatar');
        $this->require('username');
    }

    public function error($error)
    {
        replyText($error);
    }

    public function finish()
    {
        replyText("فرم پایان یافت :)\nنام: {$this->name}\nنام کاربری: @{$this->username}");
    }

    public function key($key)
    {
        return [
            $key->options(),
            $key->skipRow("رد کردن"),
            $key->cancelRow("لغو"),
        ];
    }

    public function name($input)
    {
        $input
            ->string()
            ->min(2)
            ->max(190)
            ->unique(User::class, 'name')
            ->request(function($key) {
                replyText("نام خود را وارد کنید:", [
                    'key' => $key,
                ]);
            })
            ->error(function($error) {
                replyText($error);
            })
            ->then(function(&$value, $input) {
                if($value == 'مهدی صارمی') {
                    $input->error("نام شما نمی تواند مهدی صارمی باشد!");
                }
            });
    }
    
    public function avatar($input)
    {
        $input
            ->photo()
            ->request(function($key) {
                replyText("عکس آواتار خود را ارسال کنید:", [
                    'key' => $key,
                ]);
            })
            ->error(function($error) {
                replyText($error);
            });
    }

    public function username($input)
    {
        if(user()->username)
            $input->fill(user()->username);
        else
            $input
                ->error(function($error) {
                    replyText($error)
                })
                ->error("نام کاربری ای برای خود تنظیم کنید و دوباره امتحان کنید!");
    }

}

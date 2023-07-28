<?php

namespace Mmb\Controller\StepHandler; #auto

use Closure;
use Mmb\Controller\Handler\HandlerCurrentStep;
use Mmb\Controller\Menu;
use Mmb\Exceptions\MmbException;
use Mmb\Exceptions\TypeException;
use Mmb\Listeners\Listeners;
use Mmb\Tools\Text;
use Mmb\Update\Upd;

class MenuHandler extends StepHandler
{
    
    public $keys;
    public $inlines;

    public $fix;

    public $target;

    public $with;

    public $other;

    public $other_args;

    public function __sleep()
    {
        return $this->getSleepNotNull();
    }

    /**
     * تنظیم دکمه ها برای ذخیره سازی نهایی
     *
     * @param array|Closure $keys
     * @return void
     */
    public function setKeys(&$keys, $isInline, array &$inlines)
    {
        $res = [];
        if($keys instanceof Closure)
            $keys = $keys();
        if(!$isInline)
            foreach($keys as $y => $row)
            {
                if(is_null($row))
                    continue;
                if(!is_array($row))
                    throw new TypeException("Invalid key at [$y] value: " . json_encode($row));

                foreach($row as $x => $key)
                {
                    if(is_null($key))
                        continue;
                    if(!is_array($key))
                        throw new TypeException("Invalid key at [$y][$x] value: " . json_encode($key));
                    if(!isset($key['text']))
                        throw new TypeException("Invalid key text at [$y][$x] value: " . json_encode($key));

                    $text = '.' . $key['text'];
                    $data = [ @$key['method'], @$key['args'] ];

                    // Compress data with $target:
                    // Old                                          New
                    // [ [ A::class, 'profile' ], [] ]              'profile'
                    // [ [ A::class, 'project' ], [ $id ] ]         [ 'project', [ $id ] ]
                    if($this->target)
                    {
                        if(@$data[0][0] == $this->target)
                        {
                            $data[0] = $data[0][1];
                            if(!$data[1])
                            {
                                $data = $data[0];
                            }
                        }
                    }

                    if (isset($key['contact']))
                        $text = 'contact';
                    elseif (isset($key['location']))
                        $text = 'location';
                    elseif (isset($key['poll']))
                        $text = 'poll';

                    $res[trim($text)] = $data;
                }
            }
        else
            $inlines['_'] = $keys;
        $this->keys = $res ?: null;

        $res = [];
        foreach($inlines as $name => $keys)
        {
            if($keys instanceof Closure)
                $keys = $keys();
            foreach($keys as $y => $row)
            {
                if(is_null($row))
                    continue;
                if(!is_array($row))
                    throw new TypeException("Invalid inline key at {$name}[$y] value: " . json_encode($row));

                foreach($row as $x => $key)
                {
                    if(is_null($key))
                        continue;
                    if(!is_array($key))
                        throw new TypeException("Invalid inline key at {$name}[$y][$x] value: " . json_encode($key));
                    if(!isset($key['text']))
                        throw new TypeException("Invalid inline key text at {$name}[$y][$x] value: " . json_encode($key));

                    $data = [ @$key['method'], @$key['args'] ];

                    // Compress data with $target:
                    // Old                                          New
                    // [ [ A::class, 'profile' ], [] ]              'profile'
                    // [ [ A::class, 'project' ], [ $id ] ]         [ 'project', [ $id ] ]
                    if($this->target)
                    {
                        if(@$data[0][0] == $this->target)
                        {
                            $data[0] = $data[0][1];
                            if(!$data[1])
                            {
                                $data = $data[0];
                            }
                        }
                    }

                    $res[trim($key['text'])] = $data;
                }
            }
        }
        $this->inlines = $res ?: null;
    }

    /**
     * تنظیم دیتا های همراه
     *
     * @param array $with
     * @return void
     */
    public function setWiths(array $with)
    {
        if(!$with)
            return;

        if(!$this->target)
        {
            throw new MmbException("Menu 'with()' required to set target class with 'target(target::class)'");
        }

        $instance = app($this->target);
        $this->with = [];
        foreach($with as $var)
        {
            $this->with[$var] = &$instance->$var;
        }
    }

    /**
     * پیدا کردن دکمه ای که روی آن کلیک شده است
     *
     * @param Upd $upd
     * @return mixed|false
     */
    public function findSelectedKey(Upd $upd)
    {
        if($msg = $upd->msg)
        {
            if($this->keys)
            {
                $text = $msg->text;
                $contact = $msg->contact;
                $location = $msg->location;
                $poll = $msg->poll;

                $check = $contact ? 'contact' : (
                    $location ? 'location' : (
                        $poll ? 'poll' : '.' . $text
                    )
                );

                // Find selected key
                if(isset($this->keys[$check]))
                {
                    return $this->keys[$check];
                }
            }
        }
        elseif($callback = $upd->callback)
        {
            if($this->inlines)
            {
                $data = $callback->data;

                if(Text::startsWith($data, 'MENU:'))
                {
                    $data = substr($data, 5);
                    // Find selected key
                    if(isset($this->inlines[$data]))
                    {
                        return $this->inlines[$data];
                    }
                }
            }
        }

        return false;
    }

    /**
     * اجرای عملیات کلیک شدن روی دکمه
     *
     * @param mixed $keyEvent
     * @return Handlable|null
     */
    public function runKeyEvent($keyEvent)
    {
        // 'method'
        if(!is_array($keyEvent))
        {
            $method = [ $this->target, $keyEvent ];
            $args = [];
        }
        // [ 'method', [args] ]
        elseif(!is_array($keyEvent[0]))
        {
            $method = [ $this->target, @$keyEvent[0] ];
            $args = @$keyEvent[1];
        }
        // [ [ 'class', 'method' ], [args] ]
        else
        {
            $method = @$keyEvent[0];
            $args = @$keyEvent[1] ?: [];
        }

        return Listeners::invokeMethod2($method, $args);
    }

    /**
     * اجرای عملیات کلیک نشدن روی دکمه ای
     *
     * @return Handlable|null
     */
    public function runOtherEvent()
    {
        if($this->other)
        {
            return Listeners::invokeMethod2([ $this->target, $this->other ], $this->other_args);
        }
        else
        {
            HandlerCurrentStep::ignoreStepBreak();
        }
    }

    public $withLoaded = false;
    public function loadWiths($setMark = true)
    {
        if($setMark)
            $this->withLoaded = true;
        if($this->target && $this->with)
        {
            $instance = app($this->target);
            foreach($this->with as $var => $value)
            {
                $instance->$var = $value;
            }
        }
    }

    /**
     * اجرا و مدیریت پاسخ منو
     *
     * @return Handlable|null
     */
    public function handle()
    {
        // Fixed menu
        if($this->fix)
        {
            $menu = Listeners::invokeMethod2([$this->target, $this->fix]);
            if(!($menu instanceof Menu))
                throw new TypeException("Method {$this->target}::{$this->fix} must return Menu, returned '".typeOf($menu)."'");

            return $menu->forceRunFixedHandler($this->with ?: []);
        }
        // Dynamic menu
        else
        {
            // Load 'with' variants
            if($this->withLoaded)
                $this->withLoaded = false;
            else
                $this->loadWiths(false);

            $key = $this->findSelectedKey(upd());

            if($key)
                return $this->runKeyEvent($key);
            else
                return $this->runOtherEvent();
        }
    }

}

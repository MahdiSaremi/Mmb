<?php

namespace Mmb\Controller\StepHandler; #auto

use Mmb\Exceptions\TypeException;
use Mmb\Listeners\Listeners;
use Mmb\Update\Upd;

class MenuHandler extends StepHandler
{
    
    public $keys;

    public $other_method;

    public $other_args;

    public function eachKeysRow()
    {
        
        foreach($this->keys as $y => $row)
        {

            if(is_null($row))
                continue;
            
            if(!is_array($row))
                throw new TypeException("Invalid key at [$y] value: " . json_encode($row));


            yield $y => $row;

        }
        
    }

    public function eachKeysCol($row, $y)
    {

        foreach($row as $x => $key)
        {
                
            if(is_null($key))
                continue;
            
            if(!is_array($key))
                throw new TypeException("Invalid key at [$y][$x] value: " . json_encode($key));


            yield $x => $key;

        }

    }

    public function eachKeys()
    {
        
        // foreach($this->keys as $y => $row)
        // {

        //     if(is_null($row))
        //         continue;
            
        //     if(!is_array($row))
        //         throw new TypeException("Invalid key at [$y] value: " . json_encode($row));


        //     foreach($row as $x => $key)
        //     {
                    
        //         if(is_null($key))
        //             continue;
                
        //         if(!is_array($key))
        //             throw new TypeException("Invalid key at [$y][$x] value: " . json_encode($key));


        //         yield $key;

        //     }

        // }
        
        foreach($this->eachKeysRow() as $y => $row)
        {
            foreach($this->eachKeysCol($row, $y) as $key)
            {
                yield $key;
            }
        }

    }

    public function findSelectedKey(Upd $upd)
    {
        if($msg = $upd->msg)
        {
            $text = $msg->text;
            $contact = $msg->contact;
            $location = $msg->location;
            $poll = $msg->poll;

            $check = $contact ? 'contact' : (
                $location ? 'location' : (
                    $poll ? 'poll' : 'text'
                )
            );
            $value = $check == 'text' ? $text : true;

            // Find selected key
            foreach($this->eachKeys() as $key)
            {

                if(@$key[$check] == $value)
                {

                    // Skip poll for other type
                    // if ($check == 'poll' && @$key['poll']['type'] && $key['poll']['type'] != $poll->type)
                    //     continue;

                    return $key;

                }

            }

        }

        return false;

    }

    public function runKeyEvent($key)
    {

        $method = @$key['method'];
        $args = @$key['args'] ?: [];

        return Listeners::invokeMethod2($method, $args);

    }

    public function runOtherEvent()
    {
        if($this->other_method)
        {

            return Listeners::invokeMethod2($this->other_method, $this->other_args);

        }
    }

    public function getKey()
    {

        $res = [];
        foreach($this->eachKeysRow() as $y => $row)
        {
            $keyr = [];

            foreach($this->eachKeysCol($row, $y) as $key)
            {
                if(@$key['text'])
                    $keyr[] = $this->getSingleKey($key);
            }

            if($keyr)
                $res[] = $keyr;
        }

        return $res;
    }

    public function getSingleKey($key)
    {

        unset($key['method']);
        unset($key['args']);

        return $key;

    }


    public function handle()
    {

        $key = $this->findSelectedKey(upd());

        if($key)
        {
            return $this->runKeyEvent($key);
        }
        else
        {
            return $this->runOtherEvent();
        }

    }

}

<?php

namespace Providers; #auto

use Mds\Mmb\Provider\UpdProvider as Provider;
use Mds\Mmb\Controller\Handler\Handler;
use Mds\Mmb\Controller\Handler\HandlerStep;
use Mds\Mmb\Controller\StepHandler\StepHandler;
use Mds\Mmb\Update\Chat\Chat;
use Mds\Mmb\Update\Chat\JoinReq;
use Mds\Mmb\Update\Inline\ChosenInline;
use Mds\Mmb\Update\Inline\Inline;
use Mds\Mmb\Update\Upd;

class UpdProvider extends Provider
{

    public function register()
    {
    }
    
    
    public function getUpdate()
    {
        return mmb()->getUpd();
    }
    
    public function getHandlers()
    {

        $this->onInstance('step', function () {
            
            $step = StepHandler::get();

            if (!$step)
                return null;

            return new HandlerStep($step);

        });

        $file = false;

        if(Inline::$this)
        {
            $file = 'inline';
        }
        // elseif(ChosenInline::$this)
        // {
        //     $config = 'chosenInline';
        // }
        // elseif(JoinReq::$this)
        // {
        //     $config = 'joinReq';
        // }
        elseif($chat = Chat::$this)
        {
            switch($chat->type)
            {

                case Chat::TYPE_PRIVATE:
                    $file = 'pv';
                break;

                case Chat::TYPE_GROUP:
                case Chat::TYPE_SUPERGROUP:
                    $file = 'group';
                break;

                case Chat::TYPE_CHANNEL:
                    $file = 'channel';
                break;

            }
        }

        if ($file)
        {
            $this->loadConfigFrom(__DIR__ . "/../Handles/$file.php", 'handle');
            return config('handle.handlers');
        }

        return [];

    }

}

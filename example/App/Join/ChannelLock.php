<?php

namespace App\Join; #auto

use Mmb\Controller\Handler\Handler;
use Mmb\Update\User\User;

class ChannelLock extends Handler
{

    public $channel = '@MmbLib';
    
	public function handle()
    {

        $user = User::$this;
        
        $mem = mmb()->getChatMember([
            'chat' => $this->channel,
            'user' => $user->id,
            'ignore' => true,
        ]);

        if($mem && !$mem->isJoin)
        {
            $this->invoke('notJoined');
        }

	}

    public function notJoined()
    {
        replyText("ابتدا عضو کانال شوید و سپس امتحان کنید: @{$this->channel}");
    }

}

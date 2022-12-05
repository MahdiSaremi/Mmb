<?php

namespace Providers; #auto
use Mds\Mmb\Provider\Provider;
use Mds\Mmb\Update\Chat\Chat;
use Mds\Mmb\Update\Upd;
use Mds\Mmb\Update\User\User;

class UserProvider extends Provider
{

    /**
     * نوع چت هایی که برای آنها دیتای یوزر ساخته خواهد شد
     *
     * @var array
     */
    protected $chatTypesToCreate = [

        Chat::TYPE_PRIVATE,

    ];

    
    public function boot()
    {
        // Load user
        $this->update(function (Upd $upd) {

            if (self::$updateCanceled)
                return;

            if ( $upd->msg &&
                in_array(
                    $upd->msg->chat->type,
                    $this->chatTypesToCreate
                )
            ) {
                
                $user = User::$this;
                $userRow = \Models\User::find($user->id);

                if(!$userRow)
                {
                    $userRow = \Models\User::createUser($user->id);
                }

                \Models\User::$this = $userRow;
                
            }

            if(!\Models\User::$this)
            {
                $this->userIsNotExists();
            }

        });

        // Save user
        $this->updateHandled(function () {

            if($user = \Models\User::$this)
            {
                $user->save();
            }

        });

        // User constant
        $this->onInstance('user', function() {
            return \Models\User::$this;
        });
    }

    public function userIsNotExists()
    {

        $this->cancelUpdate();

    }

}

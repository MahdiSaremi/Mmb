<?php

namespace App\Home; #auto
use Mds\Mmb\Controller\Controller;
use Mds\Mmb\Controller\Menu;

class Start extends Controller
{

    public function startCommand()
    {
        return $this('start');
    }

    public function start()
    {
        replyText("خوش آمدید", [
            'menu' => $this->menu,
        ]);

        return $this->menu;
    }

    public function menu()
    {
        return Menu::new([

            [ self::key("تست", 'test') ],

        ]);
    }
    
}

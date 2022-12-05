<?php

return [

    'handlers' => [

        /**
         * Before handlers
         */
        App\Home\Start::command('/start', 'startCommand'),
        App\Join\ChannelLock::instance(),


        /**
         * Current handlers
         */
        app('step'),



        /**
         * After handles
         */


    ]

];

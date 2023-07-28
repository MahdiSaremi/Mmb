<?php
#auto-name
namespace Mmb\Listeners;

use Mmb\Guard\Guard;
use Mmb\Mapping\Arr;
use Models\User;

class A
{

    public function test()
    {
    }

    /**
     * 
     *
     * @return B<A>
     */
    public function b()
    {
        return new B();
    }

    /**
     * Undocumented function
     *
     * @return Arr<User>
     */
    public function ar()
    {
        return new Arr([ 1, 2, 3 ]);
    }
    
}

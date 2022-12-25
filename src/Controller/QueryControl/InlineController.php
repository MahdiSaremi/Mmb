<?php

namespace Mmb\Controller\QueryControl; #auto

use Mmb\Controller\Controller;

abstract class InlineController extends Controller
{

    public abstract function bootInline($query);
    
}

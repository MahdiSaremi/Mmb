<?php

namespace Mmb\Db; #auto

use Mmb\Exceptions\MmbException;
use Mmb\Handlers\Defaultable;

class Driver {

    use Defaultable;

    /**
     * @var string
     */
    public $queryCompiler;
    
    /**
     * ارسال کوئری با کامپایلر
     *
     * @param QueryCompiler $queryCompiler
     * @return QueryResult
     */
    public function query($queryCompiler) {

        throw new MmbException("No database driver found");

    }

}

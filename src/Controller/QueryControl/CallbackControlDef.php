<?php

namespace Mmb\Controller\QueryControl; 
use Mmb\Exceptions\MmbException;#auto

trait CallbackControlDef
{

    use CallbackControl;

    public function bootCallback(QueryBooter $booter)
    {
        if (!isset($this->callBy) || !isset($this->supportedTypes))
            throw new MmbException("Class '" . static::class . "' required properties \"protected \$callBy = 'name';\" and \"protected \$supportedTypes = ['methods'];\"");

        $booter
            ->pattern($this->callBy . ":{method}:{args}")
            ->any('method', $this->supportedTypes)
            ->method('method')
            ->argsJson('args');
    }

}

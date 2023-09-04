<?php
#auto-name
namespace Mmb\Db;

class SingleIndex
{

    public function __construct(
        public string $type,
        public array $columns,
        public string $name,
    )
    {
    }
    
}

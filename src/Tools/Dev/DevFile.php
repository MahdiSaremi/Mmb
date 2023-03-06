<?php
#auto-name
namespace Mmb\Tools\Dev;

class DevFile
{

    /**
     * @var Dev
     */
    public $addon;
    public $path;

    public $namespace;
    public $name;

    public function __construct($addon, $namespace, $name)
    {
        $this->addon = $addon;
        $this->namespace = $namespace;
        $this->name = $name;
        $this->path = str_replace("\\", "/", "$namespace/$name.php");
    }

    public function exists()
    {
        return file_exists($this->path);
    }

    public function php()
    {
        $content = @file_get_contents($this->path);
        if(!$content)
        {
            return (new DevPhp($this))->namespace($this->namespace)->name($this->name);
        }
        else
        {
            $this->addon->error("Can't parse php files!");
        }
    }

    public function save($content)
    {
        if($this->exists())
            if(!$this->addon->inputYN("Overwrite '{$this->path}'? (Y/n) "))
                die;

        file_put_contents($this->path, $content);
    }
    
}

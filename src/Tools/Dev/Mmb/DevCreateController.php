<?php
#auto-name
namespace Mmb\Tools\Dev\Mmb;
use Mmb\Tools\Dev\Dev;

class DevCreateController extends Dev
{

    public static function boot()
    {
        static::set('controller');
        static::set('c');
    }
    
    public function run()
    {
        if($this->getCons('r'))
        {
            (new DevCreateControllerR)->addInputs($this)->run();
            return;
        }

        [$namespace, $class] = $this->inputClass("Enter class name: ");

        $file = $this->findClass($namespace, $class);

        $file->php()
            ->extends('Controller')
            ->use('Mmb\Controller\Controller')
            ->method('main', [], 'replyText("This is main");')
            ->save();

        echo "Successfully created controller!\n";
        
        if($this->getCons('m') || $this->getCons('model'))
        {
            (new DevCreateModel)->addInput($class)->run();
        }

    }

}

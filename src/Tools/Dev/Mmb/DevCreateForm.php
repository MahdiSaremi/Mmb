<?php
#auto-name
namespace Mmb\Tools\Dev\Mmb;
use Mmb\Tools\Dev\Dev;

class DevCreateForm extends Dev
{

    public static function boot()
    {
        static::set('form');
        static::set('f');
    }
    
    public function run()
    {
        [$namespace, $class] = $this->inputClass("Enter class name: ");

        if(!$namespace)
            $namespace = "App\\Forms";

        $file = $this->findClass($namespace, $class);

        $php = $file->php()
            ->extends('Form')
            ->use('Mmb\Controller\Form\Form')
            ->use('Mmb\Controller\Form\FormInput')
            ->method('cancelForm', [], "return Home::invoke('start');");

        $inits = [];
        $methods = [];
        while(true)
        {
            $inp = $this->input("Enter next input name: (skip to end) ");
            if(!$inp) break;

            $type = $this->input("Enter input type: ");

            $methods[$inp] = "\$input\n" .
                "\t\t\t->{$type}()\n" .
                "\t\t\t->request(function() {\n" .
                "\t\t\t\tresponse('Enter {$inp}:', [\n" .
                "\t\t\t\t\t'key' => \$this->key,\n" .
                "\t\t\t\t]);\n" .
                "\t\t\t});";

            $inits[] = "\$this->required('{$inp}');";
        }

        $php->method('form', [], join("\n\t\t", $inits));

        foreach($methods as $inp => $method)
        {
            $php->method($inp, ['FormInput $input'], $method);
        }

        $php
            ->method('finish', [], "return Home::invoke('start');")
            ->save();

        echo "Successfully created form!\n";
        
    }
    
}

<?php
#auto-name
namespace Mmb\Tools\Dev\Mmb;
use Mmb\Tools\Dev\Dev;

class DevCreateModel extends Dev
{

    public static function boot()
    {
        static::set('model');
        static::set('m');
    }
    
    public function run()
    {
        [$namespace, $class] = $this->inputClass("Enter class name: ");

        if(!$namespace)
            $namespace = "Models";

        $table = strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $class)) . 's';
        
        $file = $this->findClass($namespace, $class);

        $file->php()
            ->extends('Table')
            ->use('Mmb\Db\Table\Table')
            ->use('Mmb\Db\QueryCol')
            ->staticMethod('getTable', [], "return \"$table\";")
            ->staticMethod('generate', ['QueryCol $table'], "\$table->id();\n\t\t\$table->timestamps();")
            ->save();

        echo "Successfully created model!\n";
        
    }
    
}

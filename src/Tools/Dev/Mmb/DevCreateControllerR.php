<?php
#auto-name
namespace Mmb\Tools\Dev\Mmb;
use Mmb\Tools\Dev\Dev;

class DevCreateControllerR extends Dev
{

    public static function boot()
    {
        static::set('controller-r');
        static::set('c-r');
        static::set('cr');
    }
    
    public function run()
    {
        [$namespace, $class] = $this->inputClass("Enter class name: ");

        $file = $this->findClass($namespace, $class);

        $file->php()
            ->extends('Controller')
            ->use('Mmb\Controller\Controller')
            ->use('Mmb\Controller\Menu')
            ->method('main', [],
                "return \$this('list');"
            )
            ->method('list', [], 
                "replyText('List:', [\n" .
                "\t\t\t'menu' => \$this->listMenu,\n" .
                "\t\t]);\n" .
                "\t\treturn \$this->listMenu;"
            )
            ->method('listMenu', [], 
                "return Menu::new(aParse([\n" .
                "\t\t\t[ static::key('Add', 'add') ],\n" .
                "\t\t\taEach(\\Models\\$class::all(), function(\$data) {\n" .
                "\t\t\t\treturn [ static::key(\$data->title, 'info', \$data->id) ];\n" .
                "\t\t\t}),\n" .
                "\t\t\t[ Home::key('Back', 'start') ],\n" .
                "\t\t]));\n"
            )
            ->method('add', ['$id'],
                "return Form\\NewData::request();"
            )
            ->method('addConfirm', ['$form'],
                "\\Models\\$class::create([\n" .
                "\t\t\t'title' => \$form->title,\n" .
                "\t\t]);\n" .
                "\t\treplyText('Added');\n" .
                "\t\treturn \$this('list');"
            )
            ->method('init', ['$id', "\\Models\\$class &\$data = null"], 
                "if(\$data = \\Models\\$class::find(\$id))\n" .
                "\t\t\treturn true;\n" .
                "\t\treplyText('Not found');\n" .
                "\t\treturn false;"
            )
            ->method('info', ['$id'], 
                "if(!\$this->init(\$id, \$data))\n" .
                "\t\t\treturn;\n" .
                "\t\treplyText('List:', [\n" .
                "\t\t\t'menu' => \$menu = Menu::new([\n" .
                "\t\t\t\t[ static::key('Edit Title', 'editTitle', \$id) ],\n" .
                "\t\t\t\t[ static::key('Delete', 'delete', \$id) ],\n" .
                "\t\t\t\t[ static::key('Back', 'list') ],\n" .
                "\t\t\t])\n" .
                "\t\t]);\n" .
                "\t\treturn \$this->menu;"
            )
            ->method('editTitle', ['$id'],
                "return Form\\EditTitle::request();"
            )
            ->method('editTitleConfirm', ['$form'],
                "if(!\$this->init(\$form->id, \$data))\n" .
                "\t\t\treturn;\n" .
                "\t\t\$data->title = \$form->title;\n" .
                "\t\t\$data->save();\n" .
                "\t\treplyText('Edited');\n" .
                "\t\treturn \$this('info', \$form->id);\n"
            )
            ->method('delete', ['$id'],
                "return Form\\Delete::request();"
            )
            ->method('deleteConfirm', ['$form'],
                "if(!\$this->init(\$form->id, \$data))\n" .
                "\t\t\treturn;\n" .
                "\t\t\$data->title = \$form->title;\n" .
                "\t\t\$data->delete();\n" .
                "\t\treplyText('Delete');\n" .
                "\t\treturn \$this('list');\n"
            )
            ->save();

        echo "Successfully created controller!\n";
        
        if($this->getCons('m') || $this->getCons('model'))
        {
            (new DevCreateModel)->addInput($class)->run();
        }

    }
    
}

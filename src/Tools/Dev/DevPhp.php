<?php
#auto-name
namespace Mmb\Tools\Dev;

class DevPhp
{

    /**
     * @var DevFile
     */
    private $file;
    public function __construct($file)
    {
        $this->file = $file;
    }
    
    private $type = 'class';
    public function type($name)
    {
        $this->type = $name;
        return $this;
    }
    
    private $name;
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }
    public function class($name)
    {
        return $this->type('class')->name($name);
    }
    public function interface($name)
    {
        return $this->type('interface')->name($name);
    }
    public function trait($name)
    {
        return $this->type('trait')->name($name);
    }
    
    private $namespace;
    public function namespace($name)
    {
        $this->namespace = $name;
        return $this;
    }
    
    private $extends;
    public function extends($name)
    {
        $this->extends = $name;
        return $this;
    }
    
    private $implements = [];
    public function implements($name)
    {
        $this->implements[] = $name;
        return $this;
    }
    
    private $use = [];
    public function use($name)
    {
        $this->use[] = $name;
        return $this;
    }
    
    private $methods = [];
    public function method($name, $args, $inner)
    {
        $this->methods[$name] = ['public', $args, $inner];
        return $this;
    }
    public function publicMethod($name, $args, $inner)
    {
        $this->methods[$name] = ['public', $args, $inner];
        return $this;
    }
    public function privateMethod($name, $args, $inner)
    {
        $this->methods[$name] = ['private', $args, $inner];
        return $this;
    }
    public function protectedMethod($name, $args, $inner)
    {
        $this->methods[$name] = ['protected', $args, $inner];
        return $this;
    }
    public function staticMethod($name, $args, $inner)
    {
        $this->methods[$name] = ['public static', $args, $inner];
        return $this;
    }
    public function publicStaticMethod($name, $args, $inner)
    {
        $this->methods[$name] = ['public static', $args, $inner];
        return $this;
    }
    public function privateStaticMethod($name, $args, $inner)
    {
        $this->methods[$name] = ['private static', $args, $inner];
        return $this;
    }
    public function protectedStaticMethod($name, $args, $inner)
    {
        $this->methods[$name] = ['protected static', $args, $inner];
        return $this;
    }
    
    private $vars = [];
    public function var($name, $default)
    {
        $this->vars[$name] = ['public', $default];
        return $this;
    }
    public function publicVar($name, $default)
    {
        $this->vars[$name] = ['public', $default];
        return $this;
    }
    public function privateVar($name, $default)
    {
        $this->vars[$name] = ['private', $default];
        return $this;
    }
    public function protectedVar($name, $default)
    {
        $this->vars[$name] = ['protected', $default];
        return $this;
    }
    


    public function __toString()
    {
        $php = "<?php\n";
        $tb = "    ";

        // Namespace
        if($this->namespace)
        {
            $php .= "#auto-name\nnamespace {$this->namespace};";
        }

        // Use
        foreach($this->use as $use)
        {
            $php .= "\nuse {$use};";
        }

        $php .= "\n\n";

        // Type
        $php .= "{$this->type} {$this->name}";

        // Extends
        if($this->extends)
            $php .= " extends {$this->extends}";

        // Implements
        if($this->implements)
            $php .= " implements";
        foreach($this->implements as $i => $imp)
        {
            if($php > 0)
                $php .= " ,";
            $php .= " {$imp}";
        }

        // Start
        $php .= "\n{\n";

            // Vars
            foreach($this->vars as $name => $var)
            {
                $php .= "\n$tb{$var[0]} \${$name}";
                if($var[1])
                {
                    $php .= " = {$var[1]}";
                }
                $php .= ";\n";
            }

            // Methods
            foreach($this->methods as $name => $method)
            {
                $php .= "\n$tb{$method[0]} function {$name}(";
                    foreach($method[1] as $i => $arg)
                    {
                        if($i > 0)
                            $php .= ", ";
                        $php .= "{$arg}";
                    }
                $php .= ")\n$tb{\n$tb$tb{$method[2]}\n$tb}\n";
            }


        // End
        $php .= "\n}\n";

        return $php;
    }

    public function save()
    {
        $this->file->save($this->__toString());
    }
    
}

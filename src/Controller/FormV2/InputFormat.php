<?php
#auto-name
namespace Mmb\Controller\FormV2;

use Mmb\Exceptions\MmbException;
use ReflectionMethod;
use ReflectionNamedType;

class InputFormat
{

    public function __construct(public Form2 $form, public string $name, public string $type = 'auto')
    {
        if(strtolower($type) == 'auto')
        {
            if(method_exists($form, $name))
            {
                $method = new ReflectionMethod($form, $name);
                if($tp = ($method->getParameters()[0] ?? null)?->getType())
                {
                    if($tp instanceof ReflectionNamedType && $type = $tp->getName())
                    {
                        $this->type = $type;
                        return;
                    }
                }
            }
            
            $this->type = Input::class;
        }
    }

    private ?Input $input;
    /**
     * گرفتن اینپوت مربوطه
     *
     * @return Input
     */
    public function getInput()
    {
        if(isset($this->input))
        {
            return $this->input;
        }

        return $this->getNewInput();
    }

    public function getNewInput()
    {
        $type = match(strtolower($this->type))
        {
            'normal' => Input::class,
            'multi select' => Inputs\MultiSelectInput::class,
            'multiselect' => Inputs\MultiSelectInput::class,
            'confirm' => Inputs\ConfirmInput::class,
            default => $this->type,
        };

        return $this->input = new $type($this->form, $this->name);
    }

    /**
     * بررسی می کند که اینپوت لود شده است یا خیر
     *
     * @return boolean
     */
    public function isLoaded()
    {
        return isset($this->input);
    }

    /**
     * اینپوت لود شده را فراموش می کند
     *
     * @return void
     */
    public function forgot()
    {
        unset($this->input);
    }
    
}

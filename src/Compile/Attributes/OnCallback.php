<?php
#auto-name
namespace Mmb\Compile\Attributes;

use Attribute;
use Mmb\Mapping\Arr;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class OnCallback extends CompilerAttribute
{
    
    public function __construct()
    {
    }

    public function multiApply(Arr $all)
    {
        $settingsList = @$this->group[OnCallbackSettings::class];
        if(!$settingsList)
            throw new \Exception("Attribute ".static::class." required OnCallbackSettings on class '{$this->class->getName()}'");
        /** @var OnCallbackSettings $settings */
        $settings = $settingsList[0];
        
        /** @var static $inline */
        foreach($all as $inline)
        {
            $settings->methods[] = $inline->method->getName();
        }
    }

}

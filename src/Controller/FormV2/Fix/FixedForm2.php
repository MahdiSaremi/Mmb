<?php
#auto-name
namespace Mmb\Controller\FormV2\Fix;

use Closure;
use Mmb\Calling\Caller;
use Mmb\Controller\Controller;
use Mmb\Controller\FormV2\Form2;
use Mmb\Controller\FormV2\Input;
use Mmb\Controller\StepHandler\Handlable;

class FixedForm2 implements Handlable
{
    
    public FixedForm2Handler $handler;

    public function __construct(
        public array $fixOn,
        ?FixedForm2Handler $handler = null,
    )
    {
        if(is_null($handler))
        {
            $handler = new FixedForm2Handler;
        }
        $this->handler = $handler;

        $handler->setFixOn($fixOn);
    }

    /**
     * گرفتن کلاس مرتبط شده
     *
     * @return Controller|mixed
     */
    public function getFixController()
    {
        return app($this->fixOn[0]);
    }

    private array $inputs = [];

    /**
     * تعریف اینپوت جدید
     *
     * @param string $name
     * @param Closure $init `fn(Input $input) => $input->text()->request("متن را وارد کنید")`
     * @param string $type
     * @return $this
     */
    public function input(string $name, Closure $init, string $type = '')
    {
        if(isset($this->inputs[$name]))
        {
            $this->inputs[$name]['callback'][] = $init;
            if($type)
            {
                $this->inputs[$name]['type'] = $type;
            }
            return $this;
        }

        $this->inputs[$name] = [ 'callback' => [ $init ], 'type' => $type ?: 'Normal' ];
        return $this;
    }
    /**
     * تعریف اینپوت جدید بدون کالبک
     *
     * @param string $name
     * @param string $type
     * @return $this
     */
    public function inputOf(string $name, string $type)
    {
        if(isset($this->inputs[$name]))
        {
            $this->inputs[$name]['type'] = $type;
            return $this;
        }

        $this->inputs[$name] = [ 'callback' => [ ], 'type' => $type ];
        return $this;
    }

    /**
     * گرفتن لیست اینپوت ها
     *
     * @return array
     */
    public function getInputList()
    {
        return array_keys($this->inputs);
    }
    /**
     * گرفتن نوع اینپوت
     *
     * @param string $name
     * @return ?string
     */
    public function getTypeOf(string $name)
    {
        return $this->inputs[$name]['type'] ?? null;
    }

    /**
     * تابع مقدار دهی را برای اینپوت صدا می زند
     *
     * @param Input $input
     * @return void
     */
    public function fireInput(Input $input)
    {
        $name = $input->name;
        if(isset($this->inputs[$name]) && isset($this->inputs[$name]['callback']))
        {
            foreach($this->inputs[$name]['callback'] as $callback)
            {
                $callback($input);
            }
        }
    }

    /**
     * ایجاد فرم اصلی
     *
     * @return FixedForm2Form
     */
    public function getForm2()
    {
        return new FixedForm2Form($this);
    }

    private bool $cancelMethod = false;
    /**
     * تابع لغو را مشخص می کند
     * 
     * با این تابع دیگر دسترسی به فرم و مقادیر آن ندارید
     *
     * @param string $method
     * @param mixed ...$args
     * @return $this
     */
    public function cancelMethod(string $method, ...$args)
    {
        $this->cancelMethod = true;
        $this->cancelCallback = [ $method, ...$args ];
        return $this;
    }

    private $cancelCallback;
    /**
     * تنظیم متد یا تابعی که در زمان لغو فرم اجرا می شود
     *
     * @param array|string|Closure $callback fn(Form2 $form)
     * @return $this
     */
    public function cancel(array|string|Closure $callback)
    {
        $this->cancelMethod = false;
        $this->cancelCallback = $callback;
        return $this;
    }
    /**
     * اجرا کردن متد تنظیم شده برای لغو فرم
     *
     * @return mixed
     */
    public function fireCancel(FixedForm2Form $form)
    {
        // Variable method
        if($this->handler->issetValueOf('#c'))
        {
            $callback = $this->handler->getValueOf('#c');
            return Caller::invoke2($callback, [ $form ]);
        }

        // Cancel method
        if($this->cancelMethod)
        {
            return $this->invoke(...$this->cancelCallback);
        }

        // Not set
        if(!isset($this->cancelCallback))
        {
            return $this->fireBack($form, $form->getCancelMessage());
        }

        // Default method
        return $this->invoke($this->cancelCallback, $form);
    }

    private bool $finishMethod = false;
    /**
     * تابع پایان را مشخص می کند
     * 
     * با این متد دیگر به فرم و مقادیر آن دسترسی ندارید
     *
     * @param string $method
     * @param mixed ...$args
     * @return $this
     */
    public function finishMethod(string $method, ...$args)
    {
        $this->finishMethod = true;
        $this->finishCallback = [ $method, ...$args ];
        return $this;
    }

    private $finishCallback;
    /**
     * تنظیم متد یا تابعی که در زمان پایان فرم اجرا می شود
     *
     * @param array|string|Closure $callback fn(Form2 $form)
     * @return $this
     */
    public function finish(array|string|Closure $callback)
    {
        $this->finishMethod = false;
        $this->finishCallback = $callback;
        return $this;
    }
    /**
     * اجرا کردن متد تنظیم شده برای پایان فرم
     *
     * @return mixed
     */
    public function fireFinish(FixedForm2Form $form)
    {
        // Variable method
        if($this->handler->issetValueOf('#f'))
        {
            $callback = $this->handler->getValueOf('#f');
            return Caller::invoke2($callback, [ $form ]);
        }

        // Finish method
        if($this->finishMethod)
        {
            return $this->invoke(...$this->finishCallback);
        }

        // Not set
        if(!isset($this->finishCallback))
        {
            return $this->fireBack($form, null);
        }

        // Default method
        return $this->invoke($this->finishCallback, $form);
    }

    private bool $backMethod = false;
    /**
     * تابع بازگشت را مشخص می کند
     *
     * @param string $method
     * @param mixed ...$args
     * @return $this
     */
    public function backMethod(string $method, ...$args)
    {
        $this->backMethod = true;
        $this->backCallback = [ $method, ...$args ];
        return $this;
    }

    private $backCallback;
    /**
     * تنظیم متد یا تابعی که در زمان بازگشت از فرم اجرا می شود
     *
     * @param array|string|Closure $callback fn($message, Form2 $form)
     * @return $this
     */
    public function back(array|string|Closure $callback)
    {
        $this->backMethod = false;
        $this->backCallback = $callback;
        return $this;
    }
    /**
     * اجرا کردن متد تنظیم شده برای لغو فرم
     *
     * @return mixed
     */
    public function fireBack(FixedForm2Form $form, $message)
    {
        // Variable method
        if($this->handler->issetValueOf('#b'))
        {
            $callback = $this->handler->getValueOf('#b');
            return Caller::invoke2($callback, [ $form ]);
        }
    
        // Back method
        if($this->backMethod)
        {
            return $this->invoke(...$this->backCallback);
        }

        // Not set
        if(!isset($this->backCallback))
        {
            return $this->invoke('main');
        }

        // Default method
        return $this->invoke($this->backCallback, $message, $form);
    }

    /**
     * تابعی را وابسته به نوع آن صدا می زند
     *
     * @param array|string|Closure $callback
     * @param mixed ...$args
     * @return mixed
     */
    public function invoke(array|string|Closure $callback, ...$args)
    {
        if($callback instanceof Closure)
        {
            return $callback(...$args);
        }
        elseif(is_array($callback))
        {
            return Caller::invoke2($callback, $args);
        }
        else
        {
            return Caller::invoke($this->getFixController(), $callback, $args);
        }
    }

    private $withProperties = [];

    /**
     * متغیر هایی از کلاس تارگت را در خود ذخیره و لود می کند
     *
     * @param string ...$properties
     * @return $this
     */
    public function with(string ...$properties)
    {
        array_push($this->withProperties, ...$properties);
        return $this;
    }
    /**
     * لیست متغیر هایی که ذخیره و لود می شوند را برمیگرداند
     *
     * @return array
     */
    public function getWithList()
    {
        return $this->withProperties;
    }

    /**
     * متغیر هایی که همراه فرم ذخیره می شوند را دوباره به کنترلر برمیگرداند و لود می کند
     *
     * @return void
     */
    public function loadProperties()
    {
        $controller = $this->getFixController();
        foreach($this->getWithList() as $prop)
        {
            $controller->$prop = $this->handler->getValueOf('>' . $prop);
        }
    }

    /**
     * متغیر هایی که همراه با فرم ذخیره می شوند را ذخیره می کند
     *
     * @return void
     */
    public function saveProperties()
    {
        $controller = $this->getFixController();
        foreach($this->getWithList() as $prop)
        {
            $this->handler->setValueOf('>' . $prop, $controller->$prop);
        }
    }

    /**
     * درخواست پر کردن فرم
     *
     * @return void
     */
    public function request(array $datas = [], array|string $onFinish = null, array|string $onBack = null, array|string $onCancel = null)
    {
        if($onFinish)
        {
            $datas['#f'] = $onFinish;
        }
        if($onBack)
        {
            $datas['#b'] = $onBack;
        }
        if($onCancel)
        {
            $datas['#c'] = $onCancel;
        }
        $this->handler->addValues($datas);

        $this->saveProperties();

        $this->getForm2()->startForm();
    }

    
    public function getHandler()
    {
        return $this->handler->getHandler();
    }

    /**
     * این متد بصورت خودکار، زمانی که فرم را ریترن می کنید اجرا می شود
     *
     * @return void
     */
    public function __autoHandle()
    {
        $this->request();
    }
    
}

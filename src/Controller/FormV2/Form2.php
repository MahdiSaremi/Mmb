<?php
#auto-name
namespace Mmb\Controller\FormV2;

use Mmb\Calling\DynCall;
use Mmb\Controller\Form\FormInput;
use Mmb\Controller\StepHandler\Handlable;
use Mmb\Controller\StepHandler\StepHandler;
use Mmb\Exceptions\MmbException;
use Mmb\Exceptions\TypeException;
use Mmb\Guard\Guard;
use Mmb\Guard\HasGuard;
use Mmb\Tools\ATool\AIter;
use Mmb\Update\Message\Msg;

abstract class Form2 implements Handlable
{

    public Form2Handler $handler;

    public function __construct(?Form2Handler $handler)
    {
        if(is_null($handler))
        {
            $handler = new Form2Handler;
        }
        $this->handler = $handler;

        $handler->setForm(static::class);
    }

    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * تعریف لیست اینپوت ها
     *
     * @return void
     */
    public abstract function form();

    private $formList = null;
    /**
     * گرفتن لیست اینپوت ها
     *
     * @return InputFormat[]
     */
    public function getFormList()
    {
        if(isset($this->formList))
        {
            return $this->formList;
        }

        $this->formList = [];
        $this->form();
        return $this->formList;
    }
    private $formListNames = null;
    /**
     * گرفتن لیست اسم های اینپوت ها
     *
     * @return string[]
     */
    public function getFormListNames()
    {
        if(isset($this->formListNames))
        {
            return $this->formListNames;
        }

        return $this->formListNames = array_keys($this->getFormList());
    }
    /**
     * گرفتن تعداد اینپوت های فرم
     *
     * @return int
     */
    public function getFormListCount()
    {
        return count($this->getFormList());
    }
    /**
     * لیست کش اینپوت ها را حذف می کند تا در دفعه بعد دوباره تابع فرم اجرا شود
     *
     * @return void
     */
    public function reform()
    {
        $this->formList = null;
        $this->formListNames = null;
    }
    /**
     * افزودن یک اینپوت
     * 
     * `$this->required('first');`
     * 
     * نوع های پشتیبانی شده رشته ای:
     * 
     * `Auto`
     * `Normal`
     * `Multi Select`
     * 
     * نوع اتو، از طریق ورودی تابع مقدار دهی تشخیص می دهد چه نوعی شود
     *
     * @param string $name
     * @param string $type
     * @return void
     */
    public function required(string $name, string $type = 'auto')
    {
        $this->formList[$name] = new InputFormat($this, $name, $type);
    }
    /**
     * افزودن لیستی از اینپوت ها بصورت یکجا
     * 
     * `$this->requiredAll([ 'a', 'b', 'c' => CheckBox::class ]);`
     *
     * @param array $list
     * @return void
     */
    public function requiredAll(array $list)
    {
        foreach($list as $name => $type)
        {
            if(is_numeric($name))
            {
                $name = $type;
                $type = Input::class;

                if(!is_string($name))
                {
                    throw new TypeException("Form name must be string, " . typeOf($name) . " given");
                }
            }

            if(!is_string($type))
            {
                throw new TypeException("Form type must be string, " . typeOf($type) . " given");
            }

            $this->required($name, $type);
        }
    }


    /**
     * شروع فرم
     *
     * @return void
     */
    public function startForm()
    {
        $this->requiredPermissions();
        try
        {
            $this->requestFirst();
        }
        catch(FormForceFinish $e)
        {
            if($e->errorMessage)
            {
                $this->displayError(null, $e->errorMessage);
            }
        }
    }
    /**
     * ادامه فرم
     *
     * @return void
     */
    public function continueForm()
    {
        $this->requiredPermissions();
        try
        {
            $this->answerCurrentInput();
            $this->requestNext();
        }
        catch(FormForceFinish $e)
        {
            if($e->errorMessage)
            {
                $this->displayError(null, $e->errorMessage);
            }
        }
    }

    use HasGuard;

    /**
     * فرم را به اتمام می رساند و از آن خارج می شود
     *
     */
    public function finish()
    {
        if($step = $this->onFinish())
        {
            StepHandler::set($step);
        }

        $this->stop();
    }

    /**
     * فرم را با تنظیم پیغامی به اتمام می رساند و از آن خارج می شود
     *
     * @param array|string $message
     */
    public function finishWith(array|string $message)
    {
        setMessage($message);
        $this->finish();
    }

    /**
     * اونت پایان فرم
     *
     * @return mixed
     */
    public abstract function onFinish();


    /**
     * زمانی که فرم کنسل می شود، این مقدار ترو می شود
     *
     * @var boolean
     */
    public $isCanceled = false;

    /**
     * فرم را لغو می کند
     *
     */
    public function cancel()
    {
        $this->isCanceled = true;

        if($step = $this->onCancel())
        {
            StepHandler::set($step);
        }

        $this->stop();
    }

    /**
     * پیامی که توسط متد کنسل تنظیم می شود
     *
     * @var string|array|null
     */
    public $cancelMessage;

    /**
     * فرم را با تنظیم پیغامی لغو می کند
     *
     * @param array|string $message
     */
    public function cancelWith(array|string $message)
    {
        $this->cancelMessage = $message;
        setMessage($message);
        $this->cancel();
    }

    /**
     * پیام لغو را می گیرد
     *
     * @param string|array|null $default
     * @return string|array|null
     */
    public function getCancelMessage(string|array|null $default = null)
    {
        return $this->cancelMessage ?? $default;
    }
    /**
     * پیام لغو را می گیرد
     * 
     * `function onCancel() { Home::invokeWith($this->cMsg("عملیات لغو شد")); }`
     *
     * @param string|array|null $default
     * @return string|array|null
     */
    public function cMsg(string|array|null $default = null)
    {
        return $this->cancelMessage ?? $default;
    }

    /**
     * اونت لغو فرم
     *
     * @return mixed
     */
    public function onCancel()
    {
        $this->back($this->cancelMessage);
    }

    /**
     * برگشت از فرم به منوی قبل
     * 
     * توجه: عملیات فعلی فرم را لغو نمی کند
     * 
     * @param string|array|null $message
     * @return void
     */
    public function back(string|array|null $message = null)
    {
        $this->onBack($message);
    }

    /**
     * اونت برگشت از فرم
     * 
     * تنظیم کنید از فرم چگونه برگردد به منوی اصلی
     *
     * @param string|array|null $message
     * @return mixed
     */
    public function onBack(string|array|null $message = null)
    {
    }

    
    /**
     * اونت قبل از درخواست اینپوت
     *
     * @param Input $input
     * @return void
     */
    public function onRequesting(Input $input)
    {
    }
    /**
     * اونت بعد از درخواست اینپوت
     *
     * @param Input $input
     * @return void
     */
    public function onRequested(Input $input)
    {
    }
    /**
     * اونت کلیک روی دکمه اینپوت قبل از عملیات اصلی اینپوت
     *
     * @param Input $input
     * @return void
     */
    public function onClicking(Input $input)
    {
    }
    /**
     * اونت کلیک روی دکمه اینپوت بعد از عملیات اصلی اینپوت
     *
     * @param Input $input
     * @return void
     */
    public function onClicked(Input $input)
    {
    }
    /**
     * اونت وارد کردن مقدار اینپوت قبل از عملیات اصلی اینپوت
     *
     * @param Input $input
     * @return void
     */
    public function onFilling(Input $input)
    {
    }
    /**
     * اونت وارد کردن مقدار اینپوت بعد از عملیات اصلی اینپوت
     *
     * @param Input $input
     * @return void
     */
    public function onFilled(Input $input)
    {
    }
    /**
     * اونت لود شدن اینپوت قبل از مقداردهی
     *
     * @param Input $input
     * @return void
     */
    public function onInputCreating(Input $input)
    {
    }
    /**
     * اونت لود شدن اینپوت بعد از مقداردهی
     *
     * @param Input $input
     * @return void
     */
    public function onInputCreated(Input $input)
    {
    }
    /**
     * اونت لود شدن اینپوت قبل از مقدار دهی پیشفرض
     *
     * @param Input $input
     * @return void
     */
    public function onInputInitializing(Input $input)
    {
    }
    /**
     * اونت لود شدن اینپوت بعد از مقدار دهی پیشفرض
     *
     * @param Input $input
     * @return void
     */
    public function onInputInitialized(Input $input)
    {
    }


    /**
     * به یک اینپوت می پرد و کد را متوقف می کند
     *
     * @param string $name
     */
    public function goto(string $name)
    {
        $this->requestInput($name);
        $this->stop();
    }

    /**
     * به اینپوت بعدی میرود
     *
     */
    public function next()
    {
        $this->requestNext();
        $this->stop();
    }

    /**
     * به اینپوت قبلی می رود
     *
     */
    public function before()
    {
        $this->requestBefore();
        $this->stop();
    }

    /**
     * به اینپوت اول می رود
     *
     */
    public function first()
    {
        $this->requestFirst();
        $this->stop();
    }

    /**
     * اینپوت فعلی را نال می کند و به اینپوت بعدی می رود
     * 
     */
    public function skip()
    {
        $this->set($this->getCurrentInput()->name, null);
        $this->next();
    }

    /**
     * اینپوت فعلی را دوباره درخواست می کند
     *
     */
    public function reinput()
    {
        $current = $this->getCurrentInput(true);
        $this->requestInput($current);
        $this->stop();
    }

    /**
     * بررسی می کند که اینپوت مد نظر اولین اینپوت فرم است یا خیر
     * 
     * اگر ورودی نداشته باشد، اینپوت فعلی را مد نظر قرار می دهد
     *
     * @param null|string|Input|null $target
     * @return boolean
     */
    public function isFirst(null|string|Input $target = null)
    {
        if($target === null)
        {
            $target = $this->getCurrentInputName();
        }
        elseif($target instanceof Input)
        {
            $target = $target->name;
        }

        $first = $this->getFirstInputName();
        if($first === false)
        {
            return false;
        }

        return $first === $target;
    }

    /**
     * بررسی می کند که اینپوت مد نظر آخرین اینپوت فرم است یا خیر
     * 
     * اگر ورودی نداشته باشد، اینپوت فعلی را مد نظر قرار می دهد
     *
     * @param null|string|Input|null $target
     * @return boolean
     */
    public function isLast(null|string|Input $target = null)
    {
        if($target === null)
        {
            $target = $this->getCurrentInputName();
        }
        elseif($target instanceof Input)
        {
            $target = $target->name;
        }

        $first = $this->getLastInputName();
        if($first === false)
        {
            return false;
        }

        return $first === $target;
    }

    /**
     * بررسی می کند اولین اینپوت را دارد
     *
     * @return boolean
     */
    public function hasFirst()
    {
        return $this->getFormListNames() ? true : false;
    }
    
    /**
     * بررسی می کند آخرین اینپوت را دارد
     *
     * @return boolean
     */
    public function hasLast()
    {
        return $this->getFormListNames() ? true : false;
    }

    /**
     * بررسی می کند این ایندکس اینپوت وجود دارد
     *
     * @param integer $index
     * @return boolean
     */
    public function hasIndex(int $index)
    {
        return $index >= 0 && $index < $this->getFormListCount();
    }

    /**
     * بررسی می کند اینپوت بعدی وجود دارد
     *
     * @return boolean
     */
    public function hasNext()
    {
        if(($index = $this->getIndexOfCurrent()) === false)
        {
            return $this->hasFirst();
        }
        else
        {
            return $this->hasIndex($index + 1);
        }
    }
    
    /**
     * بررسی می کند اینپوت قبلی وجود دارد
     *
     * @return boolean
     */
    public function hasBefore()
    {
        if(($index = $this->getIndexOfCurrent()) === false)
        {
            return false;
        }
        else
        {
            return $this->hasIndex($index - 1);
        }
    }


    /**
     * عملیات پر کردن اینپوت
     *
     * @param string|Input $input
     * @return void
     */
    public function answerInput(string|Input $input)
    {
        if($input instanceof Input)
        {
            $input->startAnswer();
        }
        else
        {
            $this->getInput($input)->startAnswer();
        }
    }
    /**
     * عملیات پر کردن اینپوت برای اینپوت فعلی اجرا می شود
     *
     * @return void
     */
    public function answerCurrentInput()
    {
        if($current = $this->getCurrentInput())
        {
            $current->startAnswer();
        }
    }

    /**
     * درخواست اینپوت
     *
     * @param string|Input $input
     * @return void
     */
    public function requestInput(string|Input $input)
    {
        if($input instanceof Input)
        {
            $input->startRequest();
        }
        else
        {
            $this->getNewInput($input)->startRequest();
        }
        $this->saveStep();
    }
    /**
     * درخواست اولین اینپوت
     *
     * @return void
     */
    public function requestFirst()
    {
        $input = $this->getFirstInput();
        if($input)
        {
            $this->requestInput($input);
        }
        else
        {
            $this->finish();
        }
    }
    /**
     * درخواست اینپوت بعدی
     *
     * @return void
     */
    public function requestNext()
    {
        $input = $this->getNextInput();
        if($input)
        {
            $this->requestInput($input);
        }
        else
        {
            $this->finish();
        }
    }
    /**
     * درخواست اینپوت قبلی
     *
     * @return void
     */
    public function requestBefore()
    {
        $input = $this->getBeforeInput();
        if($input)
        {
            $this->requestInput($input);
        }
        else
        {
            $this->requestFirst();
        }
    }
    /**
     * درخواست اینپوت آخر
     *
     * @return void
     */
    public function requestLast()
    {
        $input = $this->getLastInput();
        if($input)
        {
            $this->requestInput($input);
        }
        else
        {
            $this->finish();
        }
    }

    /**
     * اینپوت فعلی را تنظیم می کند
     *
     * @param string|Input $input
     * @return void
     */
    public function setCurrentInput(string|Input $input)
    {
        if($input instanceof Input)
        {
            $input = $input->name;
        }

        $this->handler->input = $input;
    }
    /**
     * گرفتن اینپوت فعلی
     * 
     * **Returns:**
     * 
     * `Input`: موفق
     * 
     * `false`: ناموفق
     * 
     * `null`: بدون اینپوت
     *
     * @return Input|false|null
     */
    public function getCurrentInput($new = false)
    {
        if(($name = $this->getCurrentInputName()) !== false)
        {
            return $new ? $this->getNewInput($name, true) : $this->getInput($name, true);
        }
        else
        {
            return null;
        }
    }
    /**
     * گرفتن اسم اینپوت فعلی
     *
     * @return string|false
     */
    public function getCurrentInputName()
    {
        return $this->handler->input ?? false;
    }
    /**
     * گرفتن اینپوت بعدی
     *
     * @return Input|false
     */
    public function getNextInput()
    {
        if(($next = $this->getNextInputName()) !== false)
        {
            return $this->getNewInput($next);
        }
        else
        {
            return false;
        }
    }
    /**
     * گرفتن اسم اینپوت بعدی
     *
     * @return string|false
     */
    public function getNextInputName()
    {
        if(is_null($this->handler->input))
        {
            return $this->getFirstInputName();
        }
        
        $listNames = $this->getFormListNames();
        $current = $this->handler->input;
        $index = array_search($current, $listNames);

        if($index === false)
        {
            // Try to repair
            foreach($this->getFormList() as $input => $if)
            {
                if(!$this->handler->issetValueOf($input))
                {
                    return $input;
                }
            }
            return false;
        }

        $index++;
        if(!isset($listNames[$index]))
        {
            // Finished
            return false;
        }

        return $listNames[$index];
    }
    /**
     * گرفتن اینپوت قبلی
     *
     * @return Input|false
     */
    public function getBeforeInput()
    {
        if(($name = $this->getBeforeInputName()) !== false)
        {
            return $this->getNewInput($name);
        }
        else
        {
            return false;
        }
    }
    /**
     * گرفتن اسم اینپوت قبلی
     *
     * @return string|false
     */
    public function getBeforeInputName()
    {
        if(is_null($this->handler->input))
        {
            return false;
        }
        
        $listNames = $this->getFormListNames();
        $current = $this->handler->input;
        $index = array_search($current, $listNames);

        if($index === false)
        {
            // Try to repair
            $last = false;
            foreach($this->getFormList() as $input => $if)
            {
                if($this->handler->issetValueOf($input))
                {
                    $last = $input;
                }
                else
                {
                    return $last;
                }
            }
            return $last;
        }

        $index--;
        if(!isset($listNames[$index]))
        {
            return false;
        }

        return $listNames[$index];
    }
    /**
     * گرفتن اولین اینپوت
     *
     * @return Input|false
     */
    public function getFirstInput()
    {
        if(($name = $this->getFirstInputName()) !== false)
        {
            return $this->getNewInput($name);
        }
        else
        {
            return false;
        }
    }
    /**
     * گرفتن اسم اولین اینپوت
     *
     * @return string|false
     */
    public function getFirstInputName()
    {
        $list = $this->getFormListNames();
        if(!$list)
        {
            return false;
        }

        return $list[0];
    }
    /**
     * گرفتن آخرین اینپوت
     *
     * @return Input|false
     */
    public function getLastInput()
    {
        if(($name = $this->getLastInputName()) !== false)
        {
            return $this->getNewInput($name);
        }
        else
        {
            return false;
        }
    }
    /**
     * گرفتن اسم آخرین اینپوت
     *
     * @return string|false
     */
    public function getLastInputName()
    {
        $list = $this->getFormListNames();
        if(!$list)
        {
            return false;
        }

        return end($list);
    }
    /**
     * گرفتن اینپوت
     *
     * @param string $name
     * @param bool $try
     * @return Input|false
     */
    public function getInput(string $name, bool $try = false)
    {
        $list = $this->getFormList();
        if(!isset($list[$name]))
        {
            if($try)
                return false;
            else
                throw new MmbException("Input '{$name}' not defined");
        }

        return $list[$name]->getInput();
    }
    /**
     * گرفتن اینپوت
     *
     * @param string $name
     * @param bool $try
     * @return Input|false
     */
    public function getNewInput(string $name, bool $try = false)
    {
        $list = $this->getFormList();
        if(!isset($list[$name]))
        {
            if($try)
                return false;
            else
                throw new MmbException("Input '{$name}' not defined");
        }

        return $list[$name]->getNewInput();
    }
    /**
     * گرفتن فرمت اینپوت
     *
     * @param string $name
     * @param bool $try
     * @return InputFormat|false
     */
    public function getInputFormat(string $name, bool $try = false)
    {
        $list = $this->getFormList();
        if(!isset($list[$name]))
        {
            if($try)
                return false;
            else
                throw new MmbException("Input '{$name}' not defined");
        }

        return $list[$name];
    }

    
    /**
     * گرفتن ایندکس اینپوت در فرم
     *
     * @param string|Input $input
     * @return int|false
     */
    public function getIndexOfInput(string|Input $input)
    {
        if($input instanceof Input)
        {
            $input = $input->name;
        }

        return array_search($input, $this->getFormListNames());
    }

    /**
     * گرفتن ایندکس اینپوت فعلی در فرم
     *
     * @param string|Input $input
     * @return int|false
     */
    public function getIndexOfCurrent()
    {
        if(($name = $this->getCurrentInputName()) === false)
        {
            return false;
        }

        return array_search($name, $this->getFormListNames());
    }


    use DynCall
    {
        __get as private __dyn_get;
        __set as private __dyn_set;
    }

    /**
     * مقداری را تنظیم می کند
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set(string $name, $value)
    {
        $this->handler->setValueOf($name, $value);
    }

    /**
     * مقدار اینپوتی را می گیرد
     *
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->handler->getValueOf($name);
    }

    public function __get($name)
    {
        if($this->handler->issetValueOf($name))
        {
            return $this->get($name);
        }
        else
        {
            return $this->__dyn_get($name);
        }
    }

    public function __set($name, $value)
    {
        if(in_array($name, $this->getFormListNames()))
        {
            $this->set($name, $value);
        }
        else
        {
            $this->__dyn_set($name, $value);
        }
    }
    public function __set_proto($name, $value)
    {
        $this->set($name, $value);
        return true;
    }
    
    /**
     * مقدار اینپوتی را فراموش می کند
     *
     * @param string $name
     * @return void
     */
    public function forgot(string ...$names)
    {
        foreach($names as $name)
        {
            $this->handler->forgotValueOf($name);
        }
    }

    /**
     * همه مقادیر را فراموش می کند
     *
     * توجه: تمامی مقادیر را فراموش می کند حتی غیر از اینپوت ها را!
     * 
     * @return void
     */
    public function forgotAll()
    {
        $this->reform();
        $this->handler->inputs = [];
    }

    /**
     * همه مقادیر اینپوت ها را فراموش می کند
     *
     * @return void
     */
    public function forgotAllInputs()
    {
        foreach($this->getFormListNames() as $name)
        {
            $this->handler->forgotValueOf($name);
        }
        
        $this->reform();
    }

    /**
     * مقادیر جدید استپ را ذخیره می کند
     *
     * @return void
     */
    public function saveStep()
    {
        $this->handler->options = null;
        if($input = $this->getCurrentInput())
        {
            if($input->isStoreEnabled())
            {
                $this->handler->options = $input->getOptionsMap();
            }
        }
        
        StepHandler::set(clone $this->handler);
    }

    /**
     * تابع پیشفرض نمایش خطا
     * 
     * این تابع صرفا برای نمایش خطا است و باعث متوقف شدن عملیات ها نمی شود
     *
     * @param string|array $message
     * @return ?Msg
     */
    public function displayError(?Input $input, $message)
    {
        return response($message);
    }

    /**
     * تابع پیشفرض نمایش درخواست
     *
     * @param Input $input
     * @param string|array $message
     * @return ?Msg
     */
    public function displayReqeuest(Input $input, $message)
    {
        return response($message, [
            'key' => $input->getKey(),
        ]);
    }
    
    /**
     * فرمت کردن کلی دکمه ها
     *
     * @param AIter $options
     * @return array
     */
    public function formatOptions(Input $input, AIter $options)
    {
        return [
            $options,
            static::opsCancel("لغو"),
        ];
    }

    /**
     * متوقف کردن ادامه فرم
     *
     * این تابع یک خطا را ترو می کند
     * 
     * @param string $error
     * @throws FormForceFinish
     */
    public function stop($error = null)
    {
        throw new FormForceFinish($error);
    }

    /**
     * فرم را شروع می کند
     *
     * @param array $datas
     * @return void
     */
    public static function request(array $datas = [])
    {
        $form = new static(new Form2Handler);
        $form->handler->addValues($datas);
        $form->startForm();
    }

    #region Options shortcut

    /**
     * تبدیل متن به یک تک دکمه
     * 
     * اگر مقدار دوم را وارد کنید، مقدار جایگزین آن می شود. حتی اگر نال وارد کنید!
     *
     * @param mixed $text
     * @return array
     */
    public static function op($text, $value = null)
    {
        return Input::op(...func_get_args());
    }
    /**
     * تبدیل یک متن با مقدار متفاوت به یک تک دکمه
     *
     * @param mixed $text
     * @param mixed $value
     * @return array
     */
    public static function opRep($text, $value)
    {
        return Input::opRep($text, $value);
    }
    /**
     * تبدیل یک متن یک تک دکمه ای که با کلیک روی آن متدی اچرا می شود
     *
     * @param mixed $text
     * @param mixed $method
     * @return array
     */
    public static function opMore($text, $method)
    {
        return Input::opMore($text, $method);
    }
    /**
     * تبدیل چند متن به یک ردیف دکمه
     *
     * @param mixed ...$options
     * @return array
     */
    public static function ops(...$options)
    {
        return Input::ops(...$options);
    }
    /**
     * تبدیل چند متن با مقدار های متفاوت به یک ردیف دکمه
     *
     * @param mixed ...$options
     * @return array
     */
    public static function opsRep(...$options)
    {
        return Input::opsRep(...$options);
    }
    /**
     * تبدیل چند متن به یک ردیف دکمه که با کلیک روی آنها متد هایی اجرا می شود
     *
     * @param mixed ...$options
     * @return array
     */
    public static function opsMore(...$options)
    {
        return Input::opsMore(...$options);
    }
    /**
     * گرفتن تک دکمه کنسل کردن
     *
     * @return array
     */
    public static function opCancel($text)
    {
        return Input::opMore($text, '@cancel');
    }
    /**
     * گرفتن یک ردیف دکمه کنسل کردن
     *
     * @return array
     */
    public static function opsCancel($text)
    {
        return Input::opsMore($text, '@cancel');
    }

    #endregion

}

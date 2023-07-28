<?php
#auto-name
namespace Mmb\Controller\FormV2;

use Closure;
use Generator;
use InvalidArgumentException;
use Mmb\Exceptions\MmbException;
use Mmb\Exceptions\TypeException;
use Mmb\Mapping\Arr;
use Mmb\Mapping\Arrayable;
use Mmb\Update\Message\Msg;
use Mmb\Update\Upd;
use Traversable;

class Input
{

    public function __construct(public Form2 $form, public string $name)
    {
        $form->onInputCreating($this);
        $this->initialize();
        $form->onInputCreated($this);
    }

    public function initialize()
    {
        $this->form->onInputInitializing($this);
        $name = $this->name;
        if(method_exists($this->form, $name))
        {
            $this->form->$name($this);
        }
        $this->form->onInputInitialized($this);
    }

    use HasInputFilter;

    public function startRequest()
    {
        try
        {
            $this->form->setCurrentInput($this);
            $this->fireRequest();
        }
        catch(InputForceFinish $e)
        {
            if(!is_null($e->errorMessage))
            {
                $this->displayError($e->errorMessage);
                $this->form->stop();
            }
        }
    }

    /**
     * مقدار مور دکمه ای که روی آن کلیک شده
     *
     * @var string
     */
    public $clickedOnMore;

    public function startAnswer()
    {
        try
        {
            if($this->isStoreEnabled())
            {
                $this->loadOptionsMap();
            }

            $this->clickedOnMore = null;

            $data = $this->applyFilters(upd(),
                function($upd)
                {
                    if($upd->msg && $this->findPressedOption($upd->msg, $value, $more))
                    {
                        $this->skipType();
                        if($more)
                        {
                            if($more && $more[0] == '@')
                            {
                                $more = substr($more, 1);
                                return $this->form->$more();
                            }
                            else
                            {
                                $this->clickedOnMore = $more;
                            }
                        }
                        return $value;
                    }

                    return $upd;
                });

            $this->setValue($data);

            if($this->isSkipedType())
            {
                if($this->clickedOnMore && ($this->fireClickOn($this->clickedOnMore, $found) || $found))
                {
                }
                else
                {
                    $this->fireClick();
                }
            }
            else
            {
                $this->fireFilled();
            }
            $this->fireThen();
        }
        catch(InputForceFinish $e)
        {
            if(!is_null($e->errorMessage))
            {
                $this->displayError($e->errorMessage);
                $this->form->stop();
            }
        }
    }

    /**
     * نمایش خطا
     * 
     * این تابع تنها برای نمایش خطا می باشد و باعث متوقف شدن عملیات اینپوت نمی شود
     *
     * @param string|array $message
     * @return ?Msg
     */
    public function displayError($message)
    {
        if($callback = $this->errorCallback)
        {
            return $callback($message);
        }
        else
        {
            return $this->form->displayError($this, $message);
        }
    }

    private ?Closure $errorCallback = null;

    /**
     * تعریف نوع خطا / ایجاد خطا
     * 
     * اگر یک تابع وارد کنید، تعریف می کنید که زمان خطا جه چیزی صدا شود
     * 
     * اگر یک پیام وارد کنید، تعریف می کنید که این خطا اجرا شود
     * 
     * بعد از اجرای خطا، یک خطا ترو می شود که کد شما را متوقف می کند
     *
     * @param Closure|string|array $message
     * @throws InputForceFinish
     * @return $this
     */
    public function error($message)
    {
        // Define callback
        if($message instanceof Closure)
        {
            $this->errorCallback = $message;
            return $this;
        }
        // Throw error
        else
        {
            throw new InputForceFinish($message);
        }
    }

    /**
     * نمایش درخواست
     * 
     * زمانی که نوبت این اینپوت می رسد صدا زده می شود و دکمه ها را همراه با خود نمایش می دهد
     *
     * @param string|array|null $message
     * @return ?Msg
     */
    public function displayRequest(string|array|null $message = null)
    {
        if(!is_null($message))
        {
            return $this->form->displayReqeuest($this, $message);
        }
        elseif($this->requestCallback instanceof Closure)
        {
            $callback = $this->requestCallback;
            return $callback();
        }
        elseif(is_null($this->requestCallback))
        {
            $this->form->next();
        }
        else
        {
            return $this->form->displayReqeuest($this, $this->requestCallback);
        }
    }

    private $requestCallback = null;

    /**
     * تعریف متن/تابع درخواست اینپوت
     *
     * مقدار درون این تابع، زمانی که نوبت به این اینپوت می رسد به کاربر همراه با دکمه های مربوطه نمایش داده می شود
     * 
     * توجه: اگر از تابع استفاده می کنید و یک پیام شخصی سازی شده ارسال می کنید، باید کلید ها در انتهای آن قرار دهید یا از این تابع استفاده کنید: **displayRequest**
     * 
     * @param Closure|string|array $message
     * @return $this
     */
    public function request($message)
    {
        $this->requestCallback = $message;
        return $this;
    }

    /**
     * درخواست را اجرا می کند
     *
     * @return void
     */
    public function fireRequest()
    {
        $this->fireRequesting();
        $this->displayRequest();
        $this->fireRequested();
    }

    private $requestingCallback = [];
    /**
     * در زمان درخواست اینپوت، قبل از درخواست صدا زده می شود
     * 
     * `$input->requesting(fn() => $input->hasValue() && $this->next());`
     * 
     * کد بالا باعث می شود تنها یک بار اینپوت درخواست شود و حتی با درخواست مجدد پر کردن اجرا نشود. مگر اینکه دیتای آن حذف شود
     *
     * @param Closure $callback
     * @return $this
     */
    public function requesting(Closure $callback)
    {
        $this->requestingCallback[] = $callback;
        return $this;
    }
    /**
     * کالبک های قبل درخواست را صدا می زند
     *
     * @return void
     */
    public function fireRequesting()
    {
        $this->form->onRequesting($this);
        foreach($this->requestingCallback as $callback)
        {
            $callback();
        }
    }

    private $requestedCallback = [];
    /**
     * در زمان درخواست اینپوت، بعد از درخواست صدا زده می شود
     * 
     * `$input->request("یک عدد وارد کنید:")->requested(fn() => responseIt("راهنما: تنها اعداد مثبت قابل قبول است"));`
     *
     * @param Closure $callback
     * @return $this
     */
    public function requested(Closure $callback)
    {
        $this->requestedCallback[] = $callback;
        return $this;
    }
    /**
     * کالبک های بعد درخواست را صدا می زند
     *
     * @return void
     */
    public function fireRequested()
    {
        foreach($this->requestedCallback as $callback)
        {
            $callback();
        }
        $this->form->onRequested($this);
    }

    /**
     * این اینپوت تنها به شرطی خواسته می شود که این شرط برقرار باشد
     * 
     * در صورتی که شرط برقرار نباشد، به اینپوت بعدی می رود
     * 
     * `$input->requestIf(fn() => $this->type == 'A')`
     *
     * @param Closure|bool|mixed $condition
     * @return $this
     */
    public function requestIf($condition)
    {
        if($condition instanceof Closure)
        {
            $this->requesting(fn() => $condition() ? null : $this->form->next());
        }
        else
        {
            if(!$condition)
            {
                $this->requesting(fn() => $this->form->next());
            }
        }

        return $this;
    }

    /**
     * پایان دادن به عملیات اینپوت
     * 
     * این تابع یک خطا را ترو می کند
     *
     * @throws InputForceFinish
     */
    public function finish()
    {
        throw new InputForceFinish();
    }

    /**
     * مقدار اینپوت را تنظیم می کند
     *
     * @param mixed $value
     * @return void
     */
    public function setValue($value)
    {
        $this->form->handler->setValueOf($this->name, $value);
    }
    /**
     * مقدار اینپوت را می گیرد
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->form->handler->getValueOf($this->name);
    }
    /**
     * بررسی می کند این اینپوت مقداری برایش تنظیم شده است
     *
     * @return boolean
     */
    public function issetValue()
    {
        return $this->form->handler->issetValueOf($this->name);
    }
    /**
     * بررسی می کند این اینپوت مقداری برایش تنظیم شده است
     *
     * @return boolean
     */
    public function hasValue()
    {
        return $this->form->handler->issetValueOf($this->name);
    }
    /**
     * مقدار قبلی ای که تنظیم شده است را فراموش می کند
     *
     * @return void
     */
    public function forgotValue()
    {
        $this->form->handler->forgotValueOf($this->name);
    }

    
    private array $filledCallback = [];
    /**
     * زمانی که یک ورودی از سمت کاربر پر می شود و از فیلتر ها میگذرد صدا زده می شود
     * 
     * `$input->filled(function() {  });`
     *
     * @param Closure $callback
     * @return $this
     */
    public function filled(Closure $callback)
    {
        $this->filledCallback[] = $callback;
        return $this;
    }
    /**
     * شنونده های قبلی این تابع را حذف می کند
     *
     * @return $this
     */
    public function resetFilled()
    {
        $this->filledCallback = [];
        return $this;
    }
    /**
     * شنونده های این تابع را صدا می زند
     *
     * @return void
     */
    public function fireFilled()
    {
        $this->form->onFilling($this);
        foreach($this->filledCallback as $callback)
        {
            $callback();
        }
        $this->form->onFilled($this);
    }


    private array $thenCallback = [];
    /**
     * زمانی که یک ورودی از فیلتر ها می گذرد این تابع صدا زده می شود می شود
     * 
     * `$input->then(function() {  });`
     *
     * @param Closure $callback
     * @return $this
     */
    public function then(Closure $callback)
    {
        $this->thenCallback[] = $callback;
        return $this;
    }
    /**
     * شنونده های قبلی این تابع را حذف می کند
     *
     * @return $this
     */
    public function resetThen()
    {
        $this->thenCallback = [];
        return $this;
    }
    /**
     * شنونده های این تابع را صدا می زند
     *
     * @return void
     */
    public function fireThen()
    {
        foreach($this->thenCallback as $callback)
        {
            $callback();
        }
    }


    private array $clickCallback = [];
    /**
     * زمانی که روی یک دکمه کلیک می شود و از فیلتر های اولیه می گذرد صدا زده می شود
     * 
     * `$input->click(function() {  });`
     *
     * @param Closure $callback
     * @return $this
     */
    public function click(Closure $callback)
    {
        $this->clickCallback[] = $callback;
        return $this;
    }
    /**
     * شنونده های قبلی این تابع را حذف می کند
     *
     * @return $this
     */
    public function resetClick()
    {
        $this->clickCallback = [];
        return $this;
    }
    /**
     * شنونده های این تابع را صدا می زند
     *
     * @return void
     */
    public function fireClick()
    {
        $this->form->onClicking($this);
        foreach($this->clickCallback as $callback)
        {
            $callback();
        }
        $this->form->onClicked($this);
    }

    private array $clickOnCallback = [];
    /**
     * زمانی که روی یک دکمه ای به شکل مور کلیک شود، این بخش صدا زده می شود
     * 
     * `$input->options([ $input->opsMore("Test", 'test') ])->clickOn('test', function() {  });`
     *
     * @param Closure $callback
     * @return $this
     */
    public function clickOn(string $name, Closure $callback)
    {
        if(!isset($this->clickOnCallback[$name]))
        {
            $this->clickOnCallback[$name] = [];
        }

        $this->clickOnCallback[$name][] = $callback;
        return $this;
    }
    /**
     * شنونده های قبلی این تابع را حذف می کند
     *
     * @return $this
     */
    public function resetClickOn(?string $name = null)
    {
        if(is_null($name))
        {
            $this->clickOnCallback = [];
        }
        else
        {
            unset($this->clickOnCallback[$name]);
        }
        return $this;
    }
    /**
     * شنونده های این تابع را صدا می زند
     *
     * @return void
     */
    public function fireClickOn(string $name, &$found = null)
    {
        foreach($this->clickOnCallback[$name] ?? [] as $callback)
        {
            $found = true;
            $callback();
        }
    }


    /**
     * درخواست پر کردن این اینپوت توسط فرم
     *
     */
    public function input()
    {
        $this->form->requestInput($this->name);
        $this->form->stop();
    }

    /**
     * باعث می شود از این اینپوت هیچوقت رد نشود و بعد از هر عملیات دوباره این اینپوت را درخواست کند
     * 
     * تنها راه خروج از اینپوت استفاده از توابع گذشتن است:
     * 
     * `$input->addOptionMore("بعدی", '@next')`
     * 
     * یا با عملیات دلخواه:
     * 
     * `$input->addOptionMore("بعدی", 'next')->clickOn('next', fn() => $this->next());`
     *
     * @return $this
     */
    public function reinputCycle()
    {
        return $this->then(fn() => $this->input());
    }


    private $optionsDirection = 'ltr';
    /**
     * نوع چینش دکمه ها را راست چین می کند
     * 
     * به این معنی که دکمه ها از سمت راست شروع به چیده شدن می کنند
     *
     * @return $this
     */
    public function rtlOptions()
    {
        $this->optionsDirection = 'rtl';
        return $this;
    }
    /**
     * نوع چینش دکمه ها را چپ چین می کند
     * 
     * این حالت پیشفرض است
     * 
     * به این معنی که دکمه ها از سمت چپ شروع به چیده شدن می کنند
     *
     * @return $this
     */
    public function ltrOptions()
    {
        $this->optionsDirection = 'ltr';
        return $this;
    }

    private $optionsPerLine = null;

    /**
     * دکمه هایی که از این به بعد اضافه می شوند، محدود به این تعداد در یک خط می شوند
     *
     * `$input->optionsPerLine(2)->addOptionsLine('A', 'B', 'C', 'D')->optionsPerLineReset();`
     * 
     * اگر ورودی دوم وارد شود، آن تابع اجرا می شود و محدودیت تنها تا زمان اجرای آن اعمال می شود
     * 
     * `$input->optionsPerLine(2, fn() => $input->addOptionsLine('A', 'B', 'C', 'D'))`
     * 
     * @param integer|null $max
     * @param Closure $callback
     * @return $this
     */
    public function optionsPerLine(?int $max, ?Closure $callback = null)
    {
        if($max < 0)
        {
            throw new InvalidArgumentException('maximum must be bigger than 0');
        }

        // Callback limit
        if($callback)
        {
            $oldMax = $this->optionsPerLine;
            $this->optionsPerLine = $max;

            $result = $callback();
            if($result instanceof Traversable)
            {
                foreach($result as $row)
                {
                    if(is_array($row))
                        throw new TypeException("Option line must be array in result of Closure");

                    $this->addOpLine($row);
                }
            }

            $this->optionsPerLine = $oldMax;
            return $this;
        }

        // Normal
        $this->optionsPerLine = $max;
        return $this;
    }
    /**
     * محدودیت دکمه بر خط را ریست می کند و به حالت بدون محدودیت بر می گردد
     *
     * @return $this
     */
    public function optionsPerLineReset()
    {
        $this->optionsPerLine(null);
        return $this;
    }
    
    private $options = [];

    /**
     * تنظیم تمامی دکمه ها
     *
     * @param array $lines
     * @return $this
     */
    public function setOptions(array $lines)
    {
        $this->options = $lines;
        return $this;
    }
    private $op_filter_on = false;
    protected function opFilterOn()
    {
        $this->op_filter_on = true;
    }
    protected function opFilterOff()
    {
        $this->op_filter_on = false;
    }
    /**
     * تک دکمه را در زمان افزودن نهایی فیلتر می کند
     *
     * @param array $op
     * @return array
     */
    protected function opFilter(array $op)
    {
        return $op;
    }
    /**
     * یک ردیف دکمه را در زمان افزودن نهایی فیلتر می کند
     *
     * @param array $line
     * @return array
     */
    protected function opFilterLine(array $line)
    {
        foreach($line as $i => $op)
        {
            if(!is_array($op))
                throw new TypeException("Invalid options");
            
            $line[$i] = $this->opFilter($op);
        }

        if($this->optionsDirection == 'rtl')
        {
            $line = array_reverse($line);
        }

        return $line;
    }
    /**
     * افزودن یک ردیف دکمه
     *
     * @param array $line
     * @return $this
     */
    public function addOpLine(array $line, bool $ignoreFilter = false)
    {
        if($this->op_filter_on && !$ignoreFilter)
        {
            $line = $this->opFilterLine($line);
        }

        // Option per line limit
        if($this->optionsPerLine && count($line) > $this->optionsPerLine)
        {
            foreach(array_chunk($line, $this->optionsPerLine) as $chunk)
            {
                $this->addOpLine($chunk, true);
            }
            return $this;
        }

        $this->options[] = $line;
        return $this;
    }
    /**
     * افزودن یک ردیف دکمه به آخرین ردیفی که ایجاد شده
     *
     * @param array $line
     * @return $this
     */
    public function addOpLineToLastLine(array $line, bool $ignoreFilter = false)
    {
        if($this->op_filter_on && !$ignoreFilter)
        {
            $line = $this->opFilterLine($line);
        }
        
        if($this->options)
        {
            $lastLine = end($this->options);
            array_push($lastLine, ...$line);
            unset($this->options[array_key_last($this->options)]);
            $line = $lastLine;
        }

        $this->addOpLine($line, true);
        return $this;
    }
    /**
     * افزودن یک تک دکمه به یک سطر جدید
     *
     * @param mixed $text
     * @return $this
     */
    public function addOption($text)
    {
        return $this->addOpLine([ $this->op($text) ]);
    }
    /**
     * افزودن یک تک دکمه با مقدار متفاوت به یک سطر جدید
     *
     * @param mixed $text
     * @param mixed $value
     * @return $this
     */
    public function addOptionRep($text, $value)
    {
        return $this->addOpLine([ $this->opRep($text, $value) ]);
    }
    /**
     * افزودن یک تک دکمه مور به یک سطر جدید
     *
     * @param mixed $text
     * @param mixed $value
     * @return $this
     */
    public function addOptionMore($text, $value)
    {
        return $this->addOpLine([ $this->opMore($text, $value) ]);
    }
    /**
     * افزودن چند دکمه هر کدام در یک سطر جدا
     *
     * @param mixed ...$options
     * @return $this
     */
    public function addOptions(...$options)
    {
        foreach($options as $text)
        {
            $this->addOption($text);
        }
        return $this;
    }
    /**
     * افزودن چند دکمه با مقدار متفاوت هر کدام در یک سطر جدا
     *
     * @param mixed ...$options
     * @return $this
     */
    public function addOptionsRep(...$options)
    {
        if(count($options) % 2 != 0)
        {
            throw new MmbException("addOptionsRep() arguments count must be even");
        }

        for($i = 0, $len = count($options); $i < $len; $i++)
        {
            $this->addOptionRep($options[$i], $options[++$i]);
        }
        return $this;
    }
    /**
     * افزودن چند دکمه مور هر کدام در یک سطر جدا
     *
     * @param mixed ...$options
     * @return $this
     */
    public function addOptionsMore(...$options)
    {
        if(count($options) % 2 != 0)
        {
            throw new MmbException("addOptionsMore() arguments count must be even");
        }

        for($i = 0, $len = count($options); $i < $len; $i++)
        {
            $this->addOptionMore($options[$i], $options[++$i]);
        }
        return $this;
    }
    /**
     * افزودن چند دکمه هم ردیف در یک ردیف جدید
     *
     * @param mixed ...$options
     * @return $this
     */
    public function addOptionsLine(...$options)
    {
        $line = [];
        foreach($options as $text)
        {
            if(is_array($text))
                $line[] = $text;
            else
                $line[] = $this->op($text);
        }
        return $this->addOpLine($line);
    }
    /**
     * افزودن چند دکمه هم ردیف با مقدار های متفاوت در یک ردیف جدید
     *
     * @param mixed ...$options
     * @return $this
     */
    public function addOptionsRepLine(...$options)
    {
        if(count($options) % 2 != 0)
        {
            throw new MmbException("addOptionsRepLine() arguments count must be even");
        }

        $line = [];
        for($i = 0, $len = count($options); $i < $len; $i++)
        {
            $line[] = $this->opRep($options[$i], $options[++$i]);
        }
        return $this->addOpLine($line);
    }
    /**
     * افزودن چند دکمه مور هم ردیف در یک ردیف جدید
     *
     * @param mixed ...$options
     * @return $this
     */
    public function addOptionsMoreLine(...$options)
    {
        if(count($options) % 2 != 0)
        {
            throw new MmbException("addOptionsMoreLine() arguments count must be even");
        }

        $line = [];
        for($i = 0, $len = count($options); $i < $len; $i++)
        {
            $line[] = $this->opMore($options[$i], $options[++$i]);
        }
        return $this->addOpLine($line);
    }
    /**
     * افزودن چند دکمه به آخرین ردیفی که ایجاد شده
     *
     * @param mixed ...$options
     * @return $this
     */
    public function addOptionsLastLine(...$options)
    {
        $line = [];
        foreach($options as $text)
        {
            if(is_array($text))
                $line[] = $text;
            else
                $line[] = $this->op($text);
        }
        return $this->addOpLineToLastLine($line);
    }
    /**
     * افزودن چند دکمه با مقدار متفاوت به آخرین ردیفی که ایجاد شده
     *
     * @param mixed ...$options
     * @return $this
     */
    public function addOptionsRepLastLine(...$options)
    {
        if(count($options) % 2 != 0)
        {
            throw new MmbException("addOptionsRepLastLine() arguments count must be even");
        }

        $line = [];
        for($i = 0, $len = count($options); $i < $len; $i++)
        {
            $line[] = $this->opRep($options[$i], $options[++$i]);
        }
        return $this->addOpLineToLastLine($line);
    }
    /**
     * افزودن چند دکمه مور به آخرین ردیفی که ایجاد شده
     *
     * @param mixed ...$options
     * @return $this
     */
    public function addOptionsMoreLastLine(...$options)
    {
        if(count($options) % 2 != 0)
        {
            throw new MmbException("addOptionsMoreLastLine() arguments count must be even");
        }

        $line = [];
        for($i = 0, $len = count($options); $i < $len; $i++)
        {
            $line[] = $this->opMore($options[$i], $options[++$i]);
        }
        return $this->addOpLineToLastLine($line);
    }

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
        $text = trim($text);
        if(func_num_args() < 2)
        {
            return [ 'text' => $text ];
        }
        elseif(func_num_args() > 2)
        {
            throw new InvalidArgumentException("op() required 1 or 2 arguments, given " . func_num_args());
        }
        else
        {
            return [ 'text' => $text, 'value' => $value ];
        }
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
        $text = trim($text);
        return [ 'text' => $text, 'value' => $value ];
    }
    /**
     * تبدیل یک متن به دکمه ای که با فشردن آن تابعی صدا می شود
     *
     * @param mixed $text
     * @param mixed $method
     * @return array
     */
    public static function opMore($text, $method)
    {
        $text = trim($text);
        return [ 'text' => $text, 'more' => $method ];
    }
    /**
     * تبدیل چند متن به یک ردیف دکمه
     *
     * @param mixed ...$options
     * @return array
     */
    public static function ops(...$options)
    {
        $line = [];
        foreach($options as $option)
        {
            if(is_array($option))
                $line[] = $option;
            else
                $line[] = static::op($option);
        }
        return $line;
    }
    /**
     * تبدیل چند متن با مقدار های متفاوت به یک ردیف دکمه
     *
     * @param mixed ...$options
     * @return array
     */
    public static function opsRep(...$options)
    {
        if(count($options) % 2 != 0)
        {
            throw new MmbException("opsRep() arguments count must be even");
        }

        $line = [];
        for($i = 0, $len = count($options); $i < $len; $i++)
        {
            $line[] = static::opRep($options[$i], $options[++$i]);
        }
        return $line;
    }
    /**
     * تبدیل چند متن به یک ردیف دکمه که با فشردن آنها تابعی صدا می شود
     *
     * @param mixed $options
     * @return array
     */
    public static function opsMore(...$options)
    {
        if(count($options) % 2 != 0)
        {
            throw new MmbException("opsMore() arguments count must be even");
        }

        $line = [];
        for($i = 0, $len = count($options); $i < $len; $i++)
        {
            $line[] = static::opMore($options[$i], $options[++$i]);
        }
        return $line;
    }

    /**
     * تنظیم دکمه ها
     * 
     * **Example 1**:
     * 
     * `$input->options([ [ $input->op('A'), $input->op('B', 'result b') ], [ static::op('C') ], $input->ops('D', 'E'), $input->opsRep('F', 'result f', 'G', 'result g') ]);`
     * 
     * **Example 2** (Add option):
     * 
     * `$input->addOptions('A', 'B')->addOptionsLine('C', 'D');`
     * 
     * ----
     * 
     * **Examples of closure mode**:
     * 
     * `$input->options(fn() => [[ $input->op('Closure option') ]]);`
     * 
     * `$input->options(function() { yield static::ops('Yield option'); });`
     * 
     * `$input->options(function() use($input) { $input->addOptions('Add option') });`
     * 
     * `$input->options(fn() => $input->addOptions('Easy way'));`
     *
     * @param array|Arrayable|Closure $options
     * @return $this
     */
    public function options($options)
    {
        if($options instanceof Closure)
        {
            $this->options[] = $options;
        }
        else
        {
            if($options instanceof Arrayable)
                $options = $options->toArray();

            foreach($options as $option)
            {
                $this->addOpLine($option);
            }
        }
        return $this;
    }

    private array $finalOptions;

    /**
     * گرفتن مقدار نهایی دکمه ها
     *
     * از این مقدار به عنوان کلید استفاده نکنید!
     * 
     * @return array
     */
    public function getOptions()
    {
        if(isset($this->finalOptions))
        {
            return $this->finalOptions;
        }

        $this->opFilterOn();

        $oldOptions = $this->options;
        $this->options = [];
        
        foreach($oldOptions as $line)
        {
            if($line instanceof Closure)
            {
                $result = $line();
                if($result instanceof Arrayable)
                {
                    $result = $result->toArray();
                }

                if(is_array($result) || $result instanceof Traversable)
                {
                    foreach($result as $op)
                    {
                        if(!$op)
                        {
                            continue;
                        }
                        if(!is_array($op))
                        {
                            throw new MmbException("Invalid input options (Closure result)");
                        }
                        $this->addOpLine($op);
                    }
                }
            }
            elseif(!$line);
            elseif(!is_array($line))
            {
                throw new MmbException("Invalid input options");
            }
            else
            {
                $this->addOpLine($line);
            }
        }

        $this->opFilterOff();

        $final = $this->options;
        $this->options = $oldOptions;

        $final = aParse($this->form->formatOptions($this, aIter($final)));

        return $this->finalOptions = $final;
    }

    /**
     * گرفتن کلید های نهایی برای نمایش به کاربر
     *
     * @return array
     */
    public function getKey()
    {
        $key = [];
        foreach($this->getOptions() as $line)
        {
            if(!$line)
                continue;
            if(!is_array($line))
                throw new MmbException("Invalid input options (line must be an array)");

            $row = [];
            
            foreach($line as $op)
            {
                if(!$op)
                    continue;
                if(!is_array($op))
                    throw new MmbException("Invalid input options (option must be an array)");
                if(!isset($op['text']))
                    throw new MmbException("Invalid input options (option required text value)");

                unset($op['value']);
                unset($op['more']);

                $row[] = $op;
            }

            if($row)
            {
                $key[] = $row;
            }
        }
        return $key;
    }

    private $optionsMap;
    /**
     * گرفتن نقشه دکمه ها
     *
     * @return array
     */
    public function getOptionsMap()
    {
        if(isset($this->optionsMap))
        {
            return $this->optionsMap;
        }

        $map = [];
        foreach($this->getOptions() as $line)
        {
            if(!$line)
                continue;
            if(!is_array($line))
                throw new MmbException("Invalid input options (line must be an array)");
            
            foreach($line as $op)
            {
                if(!$op)
                    continue;
                if(!is_array($op))
                    throw new MmbException("Invalid input options (option must be an array)");
                if(!isset($op['text']))
                    throw new MmbException("Invalid input options (option required text value)");

                $text = '#' . $op['text'];
                if(array_key_exists('more', $op))
                {
                    $value = $op['more'];
                }
                else
                {
                    $value = array_key_exists('value', $op) ? [ $op['value'] ] : null;
                }

                if($op['contact'] ?? false)
                {
                    $text = 'contact';
                }
                elseif($op['location'] ?? false)
                {
                    $text = 'location';
                }

                $map[$text] = $value;
            }
        }
        return $this->optionsMap = $map;
    }

    /**
     * لود کردن نقشه دکمه ها از هندلر
     *
     * @return void
     */
    public function loadOptionsMap()
    {
        $this->optionsMap = $this->form->handler->options ?? [];
    }

    /**
     * پیدا کردن دکمه ای که روی آن کلیک کرده اس
     *
     * @param Msg $msg
     * @param mixed $optionValue
     * @param mixed $optionMore
     * @return boolean
     */
    public function findPressedOption(Msg $msg, &$optionValue, &$optionMore)
    {
        $map = $this->getOptionsMap();
        
        if($msg->contact)
        {
            $tag = 'contact';
            $optionValue = $msg->contact;
        }
        elseif($msg->location)
        {
            $tag = 'location';
            $optionValue = $msg->location;
        }
        elseif($msg->type == Msg::TYPE_TEXT)
        {
            $tag = '#' . $msg->text;
            $optionValue = $msg->text;
        }
        else
        {
            return false;
        }

        if(array_key_exists($tag, $map))
        {
            $mapValue = $map[$tag];
            $optionMore = null;
            // Replaced value
            if(is_array($mapValue))
            {
                $optionValue = $mapValue[0];
            }
            // Message value
            elseif(is_null($mapValue))
            {
            }
            // More value
            else
            {
                $optionMore = $mapValue;
                $optionValue = null;
            }

            return true;
        }

        return false;
    }

    private $storeEnabled = false;
    /**
     * با فعال کردن این قابلیت، دکمه ها در دیتابیس ذخیره می شوند
     *
     * @return $this
     */
    public function store()
    {
        $this->storeEnabled = true;
        return $this;
    }
    /**
     * با فعال کردن این قابلیت، دکمه ها در دیتابیس ذخیره می شوند
     *
     * @return $this
     */
    public function storeOptions()
    {
        return $this->store();
    }
    /**
     * بررسی می کند ذخیره سازی دکمه ها فعال است یا خیر
     *
     * @return boolean
     */
    public function isStoreEnabled()
    {
        return $this->storeEnabled;
    }

}

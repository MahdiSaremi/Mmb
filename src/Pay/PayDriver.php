<?php
#auto-name
namespace Mmb\Pay;

use Mmb\Calling\Caller;
use Mmb\Tools\ATool;

abstract class PayDriver
{

    /**
     * فعال بودن حالت دیباگ
     * 
     * اگر فعال باشد پرداخت ها بصورت تستی انجام می شوند
     * 
     * @var bool
     */
    public $debug;

    /**
     * کلید درگاه پرداخت
     * 
     * @var string
     */
    protected $key;

    /**
     * لینک بازگشت از درگاه
     * 
     * @var string
     */
    public $callbackUrl;

    /**
     * کلاسی که مدیریت کننده ذخیره سازی اطلاعات است
     * 
     * پیشفرض: `Storage\PayStorage::class`
     *
     * @var string
     */
    public $storage;

    public function __construct($key, $debug = false)
    {
        $this->key = $key;
        $this->debug = $debug;
        $this->storage = Storage\PayStorage::class;
    }


    /**
     * ایجاد یک آیدی یونیک در دیتابیس
     *
     * @return int
     */
    public function uniqueID()
    {
        $store = $this->storage;
        if(method_exists($store, 'query'))
        {
            return $store::query()->insert(['data' => 'null', 'driver' => static::class])->id;
        }
        else
        {
            $id = 0;
            $store::editBase(function (&$data) use (&$id) {

                $id = $data['last_uuid'] ?? 0;

                while (array_key_exists(++$id, $data[static::class] ?? [])) ;

                @$data[static::class][$id] = [];
                $data['last_uuid'] = $id;

            });
            return $id;
        }
    }

    /**
     * ذخیره دیتا در دیتابیس
     *
     * @param int $id
     * @param array $data
     * @return void
     */
    public function saveData($id, $data)
    {
        $store = $this->storage;
        if(method_exists($store, 'query'))
        {
            $query = $store::query()->where('id', $id)->where('driver', static::class);
            if($query->exists())
                $query->update([ 'data' => json_encode($data) ]);
            else
                $query->create([ 'data' => json_encode($data) ]);
        }
        else
        {
            $store::editBase(function (&$base) use ($id, $data) {
                @$base[static::class][$id] = $data;
            });
        }
    }

    /**
     * بارگیری دیتا از دیتابیس - بعد از بارگزاری، دیتا را حذف می کند
     *
     * @param int $id
     * @return array|false
     */
    public function loadData($id)
    {
        $store = $this->storage;
        if(method_exists($store, 'query'))
        {
            $pay = $store::query()->where('id', $id)->where('driver', static::class)->get();
            if ($pay)
            {
                $pay->delete();
                return json_decode($pay->data, true);
            }
            else
                return false;
        }
        else
        {
            $result = false;
            $store::editBase(function (&$data) use ($id, &$result) {

                if(isset($data[static::class][$id]))
                {
                    $result = $data[static::class][$id];
                    unset($data[static::class][$id]);
                }

            });
            return $result;
        }
    }


    /**
     * ایجاد خطا
     * 
     * + اگر یک ورودی عددی وارد شود، کد آن محسوب می شود و متن آن خودکار تشخیص داده می شود
     * + اگر یک ورودی متنی وارد شود، متن آن محسوب می شود و کد خطا 0 در نظر گرفته می شود
     * + اگر دو ورودی وارد شود، ورودی اول متن خطا و ورودی دوم کد خطا در نظر گرفته می شود
     * 
     * @param string|int $error
     * @param int $error_id
     * @throws PayException
     * @return never
     */
    public function error($error, $error_id = NULL)
    {
        if ($error_id !== NULL)
            throw new PayException($error, $error_id);
        elseif (is_int($error) || is_double($error))
            throw new PayException($this->getErrorText($error), $error);
        else
            throw new PayException($error, 0);
    }


    /**
     * بررسی و اجرای پرداخت فعلی
     * 
     * این تابع را در لینک بازگشت از درگاه قرار دهید
     * 
     * @param bool &$is_pay_request آیا این یک بازگشت از درگاه است
     * @return bool اگر هیچ تابعی اجرا نشود فالس می شود
     */
    public function execute(&$is_pay_request = false)
    {
        if ($this->debug)
            $current = $this->getCurrentDebug();
        else
            $current = $this->getCurrent();

        // Find data
        if ($current === null || $current === false)
            return false;
        if (!is_array($current) && !($current instanceof PayInfo))
            $current = $this->loadData($current);
        if (!$current)
            return false;

        $is_pay_request = true;

        // Class handler
        if ($current instanceof PayInfo)
            $info = $current;
        else
            $info = new PayInfo($current);

        $args = $info->args;
        ATool::insert($args, 0, $info);

        if(isset($current['cl']))
        {
            $cl = $current['cl'];
            $object = app($cl);
            if($object instanceof PayModifier)
            {
                if($object->payValidate($info))
                {
                    if($this->debug ? $this->verifyDebug($info) : $this->verify($info))
                    {
                        $object->paySuccess($info);
                        return true;
                    }
                }
                else
                {
                    if($object->payFailed($info) === false)
                        return false;
                    return true;
                }
            }
            else
            {
                if(!method_exists($object, 'payValidate') || Caller::call([$object, 'payValidate'], $args))
                {
                    if($this->debug ? $this->verifyDebug($info) : $this->verify($info))
                    {
                        Caller::call([$object, 'paySuccess'], $args);
                        return true;
                    }
                }
                else
                {
                    if(method_exists($object, 'payFailed'))
                    {
                        if(Caller::call([$object, 'payFailed']) === false)
                            return false;
                        return true;
                    }
                }
            }
        }

        // Function handler
        else
        {
            if(isset($current['va']))
            {
                if(!Caller::invoke2($current['va'], $args))
                {
                    if (isset($current['fa']))
                    {
                        if(Caller::invoke2($current['fa'], [ $info ]) === false)
                            return false;
                        return true;
                    }
                }
            }
            if($this->debug ? $this->verifyDebug($info) : $this->verify($info))
            {
                Caller::invoke2($current['su'], $args);
                return true;
            }
        }

        return false;
    }

    /**
     * ساخت لینک درگاه
     * 
     * **$options:**
     * `success`: متدی که زمان تکمیل پرداخت صدا زده می شود
     * `validate`: متدی که قبل از تایید پرداخت، اعتبارسنجی می کند
     * `fail`: متدی که زمان فالس بودن متد ولیدیت صدا زده می شود
     * `...`: بستگی به درگاه پرداخت دارد
     * 
     * @param int $amount مبلغ به تومان
     * @param array|null $options
     * @param mixed ...$args ورودی ها
     * @return string|bool
     */
    public function createLink($amount, $options = [], ...$args)
    {
        return $this->_createLink($amount, $options, $args);
    }

    /**
     * ساخت لینک درگاه | با استفاده از کلاس مدیریت کننده
     * 
     * `$link = $pay->createLink(1000, PayModify::class, $user->id);`
     * 
     * ==================== WAY 1 ====================
     * 
     * `class PayModify: fun paySuccess; fun payValidate; fun payFailed;`
     * Use arguments: function paySuccess(PayInfo $info, $arg1, $arg2) { ... }
     * 
     * ==================== WAY 2 ====================
     * 
     * `class PayModify extends PayModifier: fun paySuccess; fun payValidate; fun payFailed;`
     * Use arguments: function paySuccess(PayInfo $info) { $arg1 = $info[0]; $arg2 = $this[1]; }
     * 
     * @param int $amount مبلغ به تومان
     * @param string|object $class کلاس مدیریت کننده - توحه: اگر شی وارد کنید، تنها نام آن ذخیره می شود
     * @param mixed ...$args
     * @return string
     */
    public function createLinkByClass($amount, $class, ...$args)
    {
        return $this->_createLink($amount, $this->loadOptionsFromClass($class), $args);
    }

    /**
     * @param int $amount
     * @param array $options
     * @param array $args
     * @return string
     */
    protected function _createLink($amount, $options, $args)
    {
        if (isset($options['amount']))
            $amount = $options['amount'];

        $data = [];
        if (isset($options['class']))
            $data['cl'] = $options['class'];
        if (isset($options['success']))
            $data['su'] = $options['success'];
        if (isset($options['validate']))
            $data['va'] = $options['validate'];
        if (isset($options['failed']))
            $data['fa'] = $options['failed'];
        $data['args'] = $args;
        $data['amount'] = $amount;
        $data['time'] = time();

        $result_id = NULL;
        try {
            if ($this->debug)
                $link = $this->requestPayDebug($amount, $options, $data, $result_id);
            else
                $link = $this->requestPay($amount, $options, $data, $result_id);
        }
        catch(PayException $e) {
            if ($result_id !== NULL)
                $this->loadData($result_id);
            throw $e;
        }

        if($result_id !== NULL)
            $this->saveData($result_id, $data);
                
        return $link;
    }

    /**
     * Load options from class
     * 
     * @param string|object $class
     * @return array
     */
    protected final function loadOptionsFromClass($class)
    {
        if(is_string($class)) {
            $object = method_exists($class, 'instance') ? $class::instance() : new $class;
        } else {
            $object = $class;
            $class = get_class($class);
        }

        $options = ['class' => $class];
        foreach($this->optionsList() as $opt)
        {
            if (isset($object->$opt))
                $options[$opt] = $object->$opt;
        }
        return $options;
    }

    /**
     * ایجاد لینک پرداخت
     * 
     * @param int $amount
     * @param array $options
     * @param int &$result_id
     * @return string
     */
    protected abstract function requestPay($amount, $options, &$saved_data, &$result_id);

    /**
     * ایجاد لینک پرداخت - حالت دیباگ
     * 
     * @param int $amount
     * @param array $options
     * @param int &$result_id
     * @return string
     */
    protected function requestPayDebug($amount, $options, &$saved_data, &$result_id)
    {
        return $this->requestPay($amount, $options + ['debug' => true], $saved_data, $result_id);
    }

    /**
     * گرفتن لیست آپشن ها
     * 
     * @return string[]
     */
    protected abstract function optionsList();

    /**
     * گرفتن متن خطا
     * 
     * @param int $error_id
     * @return string
     */
    public function getErrorText($error_id)
    {
        return "Error";
    }

    /**
     * یافتن اطلاعات از طریق لینک کنونی
     * 
     * @return int|array|PayInfo|bool|null
     */
    protected abstract function getCurrent();

    /**
     * یافتن اطلاعات از طریق لینک کنونی در حالت دیباگ
     * 
     * @return int|array|PayInfo|bool|null
     */
    protected function getCurrentDebug()
    {
        return $this->getCurrent();
    }

    /**
     * تایید پرداخت فعلی از طریق درگاه
     * 
     * @param PayInfo $info
     * @return bool
     */
    protected abstract function verify(PayInfo $info);

    /**
     * تایید پرداخت فعلی از طریق درگاه در حالت دیباگ
     * 
     * @param PayInfo $info
     * @return bool
     */
    protected function verifyDebug(PayInfo $info)
    {
        return $this->verify($info);
    }

}

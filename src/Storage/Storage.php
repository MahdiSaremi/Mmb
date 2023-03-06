<?php

// Copyright (C): t.me/MMBlib

namespace Mmb\Storage; #auto

use Closure;
use Mmb\Files\Files;
use Mmb\Kernel\Kernel;
use Mmb\Tools\ATool;

/**
 * کلاسی با ارث بری از این کلاس بسازید تا دیتا های عمومی خود را راحت در دست بگیرید!
 */
class Storage
{

    public static $storagePath = '.';

    private static $path = [];
    private static $datas = [];

    /**
     * تنظیم مسیر ذخیره دیتای این کلاس
     *
     * @param string $storagePath
     * @return void
     */
    public static function setPath($storagePath) 
    {
        self::$path[static::class] = $storagePath;
    }

    /**
     * گرفتن مسیر ذخیره دیتای این کلاس
     *
     * @return string
     */
    public static function getPath()
    {
        return self::$path[static::class] ?? self::$storagePath;
    }

    /**
     * گرفتن نام فایل بدون پسوند
     * 
     * @return string
     */
    public static function getFileName()
    {
        return str_replace("\\", ".", strtolower(static::class));
    }

    public static function jsonFlag() 
    {
        return JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
    }

    /**
     * دیتا را با سلکتور شما انتخاب می کند و بر میگرداند (تنها یک مقدار)
     * 
     * * Selector:
     * * `"menu.name.*.text|caption" = $data['menu']['name'][همه][text و caption]`
     *
     * @param string $selector
     * @param mixed $default
     * @return mixed
     */
    public static function get($selector, $default = null)
    {
        $data = static::getBase();
        return ATool::selectorGet($data, $selector, $default);
    }

    /**
     * دیتا را با سلکتور شما انتخاب می کند و بررسی می کند که وجود دارد یا نه
     * 
     * * Selector:
     * * `"menu.name.*.text|caption" = $data['menu']['name'][همه][text و caption]`
     *
     * @param string $selector
     * @return mixed
     */
    public static function exists($selector)
    {
        $data = static::getBase();
        return ATool::selectorExists($data, $selector);
    }

    /**
     * دیتا را با سلکتور شما انتخاب می کند و تمام انتخاب ها را بر میگرداند
     * 
     * * Selector:
     * * `"menu.name.*.text|caption" = $data['menu']['name'][همه][text و caption]`
     *
     * @param string $selector
     * @return array
     */
    public static function getList($selector)
    {
        $data = static::getBase();
        return ATool::selectorGetList($data, $selector);
    }

    private static function _dataSet($data) 
    {
        self::$datas[static::class] = $data;
    }

    /**
     * عین مقدار ذخیره شده را بر میگرداند
     *
     * @return array|null
     */
    public static function getBase(bool $load_require = false)
    {
        if(
            !$load_require &&
            isset(self::$datas[static::class]) &&
            !Kernel::runIsLong()
        ) {
            return self::$datas[static::class];
        }

        $target = static::getPath() . '/' . static::getFileName() . '.json';
        $file = file_exists($target) ? Files::get($target) : false;
        if($file){
            $data = @json_decode($file, true);
            self::$datas[static::class] = $data;
            return $data;
        }
        else{
            return [];
        }
    }

    /**
     * انتخاب، اجرای کالبک، ذخیره
     * 
     * با این تابع می توانید دیتا های مورد نظر را بگیرید و ویرایش کنید
     *
     * * Callback: function(&$data)
     * * `Globals::editBase(function(&$data) { $data['test'] = strtolower($data['test']); });`
     * 
     * @param callable|Closure|string|array $callback
     * @return void
     */
    public static function editBase($callback)
    {
        $storage = static::getPath();
        if(!is_dir($storage)) mkdir($storage);
        if(!file_exists($storage . '/.htaccess')){
            file_put_contents($storage . '/403.php', '<?php echo "Access danied"; ?>');
            file_put_contents($storage . '/.htaccess', "<IfModule mod_rewrite.c>\nRewriteEngine On\n\nRewriteRule ^ 403.php\n</IfModule>");
        }

        $target = $storage . '/' . static::getFileName() . '.json';
        Files::editText($target, function($file) use(&$callback) {
            if($file){
                $data = @json_decode($file, true);
            }
            else{
                $data = [];
            }
            
            $callback($data);
            
            static::_dataSet($data);
            return json_encode($data, static::jsonFlag());
        });
    }

    /**
     * دیتا را با سلکتور شما انتخاب می کند و انتخاب ها را تنظیم می کند
     * 
     * * Selector:
     * * `"menu.name.*.text|caption" = $data['menu']['name'][همه][text و caption]`
     * 
     * @param string $selector
     * @param mixed $value
     * @return void
     */
    public static function set($selector, $value)
    {
        static::editBase(function(&$data) use(&$selector, &$value) {

            ATool::selectorSet($data, $selector, $value);

        });
    }

    /**
     * انتخاب با سلکتور، اجرای کالبک، ذخیره
     * 
     * با این تابع می توانید دیتا های مورد نظر را بگیرید و ویرایش کنید
     * 
     * * توجه: تمام مقدار هایی که سلکتور انتخاب می کند، یک به یک به کالبک ارسال می شوند
     *
     * * Callback: `function(&$data)`
     * * `Globals::edit2('messages.*.text', function(&$text) { $text = strtoupper($text); });`
     * 
     * @param Callable|Closure|string|array $callback
     * @return void
     */
    public static function edit($selector, $callback) 
    {
        static::editBase(function(&$data) use(&$selector, &$callback) {
            
            $sel = ATool::selectorGetSelectors($data, $selector);
            if($sel) {
                for($i = 0; $i < count($sel); $i++) {
                    $callback($sel[$i]);
                }
            }

        });
    }

    /**
     * عین مقداری که تعیین می کنید ذخیره می شود برای کل اطلاعات این کلاس
     *
     * @param array $data
     * @return void
     */
    public static function setBase(array $data)
    {
        $storage = static::getPath();
        if(!is_dir($storage)) mkdir($storage);
        if(!file_exists($storage . '/.htaccess')){
            file_put_contents($storage . '/403.php', '<?php echo "Access danied"; ?>');
            file_put_contents($storage . '/.htaccess', "<IfModule mod_rewrite.c>\nRewriteEngine On\n\nRewriteRule ^ 403.php\n</IfModule>");
        }
        $target = $storage . '/' . static::getFileName() . '.json';
        Files::put($target, json_encode($data, static::jsonFlag()));
    }

    /**
     * دیتا را با سلکتور شما انتخاب می کند و آنها را حذف می کند
     * 
     * * Selector:
     * * `"menu.name.*.text|caption" = $data['menu']['name'][همه][text و caption]`
     * 
     * @param string $selector
     * @return void
     */
    public static function unset($selector)
    {
        static::editBase(function(&$data) use(&$selector) {
            
            ATool::selectorUnset($data, $selector);
            
        });
    }

    /**
     * بررسی می کند متن شما شامل کاراکتر های دستوری سلکتور نیست
     *
     * @param string $name
     * @return bool
     */
    public static function selectorValidName($selector_name) 
    {
        return ATool::selectorValidName($selector_name);
    }

    /**
     * تمام مقدار های ذخیره شده را حذف می کند
     *
     * @return void
     */
    public static function reset()
    {
        static::setBase([]);
    }

}

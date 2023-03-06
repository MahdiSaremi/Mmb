<?php
#auto-name
namespace Mmb\Tools;

class PhpTool
{

    /**
     * پیدا کردن کلاس ها از مسیر
     * 
     * این تابع فایل های پی اچ پی را پیدا می کند و طبق استاندارد اتولود، نیم اسپیس آن ها را حدس میزند
     *
     * @param string $dir
     * @param string $namespace
     * @return array
     */
    public static function findClassesFromDir($dir, $namespace)
    {
        $classes = [];
        foreach(scandir($dir) as $sub)
        {
            if(endsWith($sub, '.php'))
            {
                $classes[] = $namespace . '\\' . substr($sub, 0, strlen($sub) - 4);
            }
            elseif($sub != '.' && $sub != '..' && is_dir("$dir/$sub"))
            {
                array_push($classes, ...static::findClassesFromDir("$dir/$sub", "$namespace\\$sub"));
            }
        }
        return $classes;
    }
    
}

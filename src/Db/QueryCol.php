<?php

namespace Mmb\Db; #auto

use Mmb\Big\BigNumber;
use Mmb\Controller\StepHandler\StepHandler;
use Mmb\Guard\Role;
use Mmb\Listeners\HasCustomMethod;
use Mmb\Tools\Text;
use Mmb\Update\User\UserInfo;

class QueryCol
{

    use HasCustomMethod;

    private $cols = [];
    private $col_names = null;

    /**
     * افزودن یک ستون جدید
     *
     * @param SingleCol $column
     * @return SingleCol
     */
    private function newColumn(SingleCol $column)
    {
        $this->cols[] = $column;
        return $column;
    }

    /**
     * افزودن یک ستون جدید
     *
     * @param string $name
     * @param string $type
     * @return SingleCol
     */
    public function createColumn($name, $type)
    {
        $column = new SingleCol($name, $type);
        $this->cols[] = $column;
        return $column;
    }

    /**
     * گرفتن ستون ها
     *
     * @return SingleCol[]
     */
    public function getColumns()
    {
        return $this->cols;
    }

    /**
     * گرفتن اسم ستون ها
     * 
     * این مقدار کش می شود
     *
     * @return array
     */
    public function getColumnNames()
    {
        if(isset($this->col_names))
            return $this->col_names;

        $this->col_names = [];
        foreach($this->cols as $col)
            $this->col_names[] = $col->name;
        
        return $this->col_names;
    }

    /**
     * پیدا کردن مشخصات ستون
     *
     * @param string $name
     * @return SingleCol|false
     */
    public function findColumn($name)
    {
        $index = array_search($name, $this->getColumnNames());
        return $index === false ? false : $this->cols[$index];
    }

    private $keys = [];

    /**
     * افزودن یک کلید جدید
     *
     * @param SingleKey $key
     * @return SingleKey
     */
    private function newKey(SingleKey $key)
    {
        $this->keys[] = $key;
        return $key;
    }

    /**
     * گرفتن کلید ها
     *
     * @return SingleKey[]
     */
    public function getKeys()
    {
        return $this->keys;
    }



    /**
     * ستون جدید عدد صحیح
     *
     * @param string $name
     * @return SingleCol
     */
    public function int($name)
    {
        return $this->createColumn($name, 'int');
    }

    /**
     * ستون جدید عدد صحیح مثبت
     *
     * @param string $name
     * @return SingleCol
     */
    public function unsignedInt($name)
    {
        return $this->createColumn($name, 'int')->unsigned();
    }

    /**
     * ستون جدید عدد صحیح بزرگ
     *
     * @param string $name
     * @return SingleCol
     */
    public function bigint($name)
    {
        return $this->createColumn($name, 'bigint');
    }

    /**
     * ستون جدید عدد صحیح بزرگ مثبت
     *
     * @param string $name
     * @return SingleCol
     */
    public function unsignedBigint($name)
    {
        return $this->createColumn($name, 'bigint')->unsigned();
    }

    /**
     * ستون جدید بایت با علامت
     *
     * @param string $name
     * @return SingleCol
     */
    public function tinyint($name)
    {
        return $this->createColumn($name, 'tinyint');
    }

    /**
     * ستون جدید عدد بایت
     *
     * @param string $name
     * @return SingleCol
     */
    public function unsignedTinyint($name)
    {
        return $this->createColumn($name, 'tinyint')->unsigned();
    }

    /**
     * ستون جدید عدد صحیح کوچک
     *
     * @param string $name
     * @return SingleCol
     */
    public function smallint($name) 
    {
        return $this->createColumn($name, 'smallint');
    }

    /**
     * ستون جدید عدد صحیح کوچک مثبت
     *
     * @param string $name
     * @return SingleCol
     */
    public function unsignedSmallint($name) 
    {
        return $this->createColumn($name, 'smallint')->unsigned();
    }

    /**
     * ستون جدید عدد صحیح متوسط
     *
     * @param string $name
     * @return SingleCol
     */
    public function mediumint($name) 
    {
        return $this->createColumn($name, 'mediumint');
    }

    /**
     * ستون جدید عدد صحیح متوسط مثبت
     *
     * @param string $name
     * @return SingleCol
     */
    public function unsignedMediumint($name)
    {
        return $this->createColumn($name, 'mediumint')->unsigned();
    }

    /**
     * ستون جدید عدد اعشاری 32بیت
     *
     * @param string $name
     * @return SingleCol
     */
    public function float($name)
    {
        return $this->createColumn($name, 'float');
    }

    /**
     * ستون جدید عدد اعشاری 32بیت مثبت
     *
     * @param string $name
     * @return SingleCol
     */
    public function unsingedFloat($name)
    {
        return $this->createColumn($name, 'float')->unsigned();
    }

    /**
     * ستون جدید عدد اعشاری 64بیت
     *
     * @param string $name
     * @return SingleCol
     */
    public function double($name)
    {
        return $this->createColumn($name, 'double');
    }

    /**
     * ستون جدید عدد اعشاری 64بیت مثبت
     *
     * @param string $name
     * @return SingleCol
     */
    public function unsingedDouble($name)
    {
        return $this->createColumn($name, 'double')->unsigned();
    }

    /**
     * ستون جدید عدد اعشاری 128بیت
     *
     * @param string $name
     * @return SingleCol
     */
    public function decimal($name)
    {
        return $this->createColumn($name, 'decimal');
    }

    /**
     * ستون جدید عدد اعشاری 128بیت مثبت
     *
     * @param string $name
     * @return SingleCol
     */
    public function unsingedDecimal($name)
    {
        return $this->createColumn($name, 'decimal')->unsigned();
    }

    /**
     * ستون جدید منطقی
     *
     * @param string $name
     * @return SingleCol
     */
    public function bool($name)
    {
        return $this->createColumn($name, 'tinyint')->len(1);
    }

    /**
     * ستون جدید کاراکتر
     *
     * @param string $name
     * @return SingleCol
     */
    public function char($name)
    {
        return $this->createColumn($name, 'char');
    }

    /**
     * ستون جدید متن
     *
     * @param string $name
     * @param int $len
     * @return SingleCol
     */
    public function string($name, $len)
    {
        $len = intval($len);
        return $this->createColumn($name, "varchar($len)");
    }

    /**
     * ستون جدید متن
     * 
     * حداکثر طول 255 بایت
     * همراه با 1 بایت برای ذخیره سازی طول
     *
     * @param string $name
     * @return SingleCol
     */
    public function tinytext($name)
    {
        return $this->createColumn($name, 'tinytext');
    }

    /**
     * ستون جدید متن
     * 
     * حداکثر طول 65,535 بایت
     * همراه با 2 بایت برای ذخیره سازی طول
     *
     * @param string $name
     * @return SingleCol
     */
    public function text($name)
    {
        return $this->createColumn($name, 'text');
    }

    /**
     * ستون جدید متن
     * 
     * حداکثر طول 16,777,215 بایت
     * همراه با 3 بایت برای ذخیره سازی طول
     *
     * @param string $name
     * @return SingleCol
     */
    public function mediumtext($name)
    {
        return $this->createColumn($name, 'mediumtext');
    }

    /**
     * ستون جدید متن
     *
     * حداکثر طول تقریبا 4 گیگابایت
     * همراه با 4 بایت برای ذخیره سازی طول
     * 
     * @param string $name
     * @return SingleCol
     */
    public function longtext($name)
    {
        return $this->createColumn($name, 'longtext');
    }

    /**
     * ستون جدید جیسون
     * 
     * این ستون از جنس متن است که در زمان ورود و خروج به ام ام بی انکد و دیکد می شود
     *
     * @param string $name
     * @param boolean $assoc
     * @return SingleCol
     */
    public function json($name, $assoc = false)
    {
        return $this
                ->text($name)
                ->nullable()
                ->modifyIn(function($value) use($assoc) {
                    return @json_decode($value, $assoc);
                })
                ->modifyOut(function($value) {
                    return json_encode($value);
                });
    }

    /**
     * ستون جدید عدد بزرگ
     * 
     * اعداد بزرگ اعدادی هستند که در پی اچ پی بصورت کلاس طور از آنها استفاده می کنید که متد های ریاضیاتی را دارد و می توانید تا ارقام بسیار بزرگ با اعشار بالا را با آن محاسبه کنید
     * 
     * `Mmb\Big\BigNumber`
     * 
     * ستونی که در دیتابیس ایجاد می شود از نوع رشته است
     *
     * @param string $name
     * @return SingleCol
     */
    public function bigNumber($name)
    {
        return $this
                ->text($name)
                ->modifyIn(function($value) {
                    return new BigNumber($value);
                })
                ->modifyOut(function($value) {
                    return "$value";
                });
    }

    /**
     * ستون جدید زمان
     * 
     * @param string $name
     * @return SingleCol
     */
    public function timestamp($name, $cast = true)
    {
        return $this->createColumn($name, 'timestamp');
    }

    /**
     * ستون جدید زمان
     * 
     * @param string $name
     * @return SingleCol
     */
    public function datetime($name)
    {
        return $this->createColumn($name, 'datetime');
    }

    /**
     * ستون جدید تاریخ
     * 
     * @param string $name
     * @return SingleCol
     */
    public function date($name)
    {
        return $this->createColumn($name, 'date');
    }

    /**
     * افزودن ستونی با مشخصات زیر:
     * `unsignedBiginteger` `autoIncrement` `id`
     *
     * @return SingleCol
     */
    public function id()
    {
        return $this->unsignedBigint('id')->autoIncrement();
    }

    /**
     * افزودن ستون زمان که زمان بروز شدن را نشان می دهد
     *
     * @return SingleCol
     */
    public function updateTimestamp($name)
    {
        return $this->timestamp($name)
                    ->defaultRaw('CURRENT_TIMESTAMP')
                    ->onUpdate('CURRENT_TIMESTAMP');
    }

    /**
     * افزودن ستون زمان که زمان ایجاد شدن را نشان می دهد
     *
     * @return SingleCol
     */
    public function createTimestamp($name)
    {
        return $this->timestamp($name)
                    ->defaultRaw('CURRENT_TIMESTAMP');
    }

    /**
     * افزودن دو ستون زمان ایجاد و زمان ویرایش
     * `created_at` `updated_at`
     *
     * @return void
     */
    public function timestamps()
    {
        $this->createTimestamp('created_at');
        $this->updateTimestamp('updated_at');
    }

    /**
     * افزودن ستون استپ برای کاربر
     *
     * @param string $name
     * @param string $idColumn
     * @return SingleCol
     */
    public function step($name = 'step', $idColumn = 'id')
    {
        return $this->text($name)
                ->nullable();
                // ->alwaysSave()
                // ->modifyIn(
                //     function($step, $model) use($idColumn)
                //     {
                //         if(UserInfo::$this && UserInfo::$this->id == $model->$idColumn)
                //         {
                //             StepHandler::modifyIn($step);
                //         }
                //         return $step;
                //     },
                //     true
                // )
                // ->modifyOut(
                //     function($step, $model) use($idColumn)
                //     {
                //         if(UserInfo::$this && UserInfo::$this->id == $model->$idColumn)
                //         {
                //             StepHandler::modifyOut($step);
                //         }
                //         return $step;
                //     },
                //     true
                // );
    }

    /**
     * افزودن ستون نقش
     *
     * @param string $name
     * @param string $idColumn
     * @return SingleCol
     */
    public function role($name = 'role', $idColumn = 'id')
    {
        return $this->text($name)
                ->nullable()
                ->alwaysSave()
                ->modifyIn(
                    function($role, $model) use($idColumn)
                    {
                        if($constant = Role::getFullConstantOf(@$model->$idColumn))
                        {
                            $role = $constant;
                        }
                
                        Role::modifyIn($role);
                        return $role;
                    },
                    true
                )
                ->modifyOut(
                    function($role, $model) use($idColumn)
                    {
                        if(Role::issetConstant(@$model->$idColumn))
                        {
                            $role = null;
                            return;
                        }
                
                        Role::modifyOut($role);
                        return $role;
                    },
                    true
                );
    }

    /**
     * افزودن ستونی که با آیدی کلاس مورد نظر رابطه داشته باشد
     * 
     * نام این ستون، به این شکل است:
     * `Text::snake($class) . "_" . $primary`
     * 
     * نوع این ستون: `unsignedBigInt`
     * 
     * **Example:**
     * 
     * `$this->relatedTo(User::class)->nullable()->foreign_key->onDeleteCascade();`
     *
     * @param string $class
     * @return SingleCol
     */
    public function relatedTo($class)
    {
        $class = Text::afterLast($class, "\\");
        $name = Text::snake($class) . "_" . $class::getPrimaryKey();

        $col = $this->unsignedBigint($name);
        $col->foreign($class);
        return $col;
    }

    /**
     * افزودن ستونی که با آیدی کلاس مورد نظر رابطه داشته باشد
     * 
     * نام این ستون، به این شکل است:
     * `Text::snake($class) . "_" . $primary`
     * 
     * نوع این ستون: `unsignedBigInt`
     * 
     * **Example:**
     * 
     * `$this->foreign(User::class)->nullable()->foreign_key->onDeleteCascade();`
     *
     * @param string $class
     * @return SingleCol
     */
    public function foreign($class)
    {
        $className = Text::afterLast($class, "\\");
        $name = Text::snake($className) . "_" . $class::getPrimaryKey();

        $col = $this->unsignedBigint($name);
        $col->foreign($class);
        return $col;
    }

}

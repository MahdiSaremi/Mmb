<?php

namespace Mmb\Db; #auto

use Closure;
use Generator;
use Mmb\Big\BigNumber;
use Mmb\Controller\StepHandler\StepHandler;
use Mmb\Db\Relation\Morph;
use Mmb\Guard\Role;
use Mmb\Listeners\HasCustomMethod;
use Mmb\Mapping\Arr;
use Mmb\Mapping\Arrayable;
use Mmb\Tools\ATool;
use Mmb\Tools\Text;
use Mmb\Update\User\UserInfo;
use Traversable;
use UnitEnum;

class QueryCol
{

    use HasCustomMethod;

    protected string $tableName;
    public function __construct(string $table)
    {
        $this->tableName = $table;
    }

    private array $cols = [];
    private ?array $col_names = null;

    private array $indexs = [];

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
    public function longtext(string $name)
    {
        return $this->createColumn($name, 'longtext');
    }

    /**
     * ستون جدید با مقدار های ثابت
     * 
     * هر ردیف باید از بین این مقادیر، مقدار خود را تنظیم کند
     * 
     * اگر به عنوان ورودی دوم، نام کلاس ای-نامی را وارد کنید، مقدار آن به یک مقدار داینامیک ای-نام پی اچ پی تبدیل می شود.
     * توجه کنید که اگر مقادیر ثابت را تغییر دهید، باید یکبار دیتابیس را نصب کنید.
     * 
     * `$table->enum('language', ['Persian', 'English']);`.
     * `$table->enum('method', MethodEnum::class);`.
     *
     * @param string $name
     * @param array|Arrayable|Traversable|string $values
     * @return SingleCol
     */
    public function enum(string $name, array|Arrayable|Traversable|string $values)
    {
        if(is_string($values) && enum_exists($values))
        {
            $enum = $values;
            $vals = [];
            foreach($enum::cases() as $case)
            {
                $vals[] = $case->value;
            }

            return $this->createColumn($name, 'enum')
                    ->inner($vals)
                    ->modifyIn(function($data) use($enum)
                    {
                        return is_null($data) ? null : $enum::tryFrom($data);
                    })
                    ->modifyOut(function($data)
                    {
                        return is_null($data) ? null : (
                            $data instanceof UnitEnum ? $data->value : "$data"
                        );
                    });
        }
        else
        {
            $values = ATool::toArray($values);
            return $this->createColumn($name, 'enum')->inner($values);
        }
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
     * نام پیشفرض این ستون، به این شکل است.
     * `Text::snake($class) . "_" . $primary`
     * 
     * نوع این ستون: `unsignedBigInt`
     * 
     * **Example:**
     * 
     * `$table->relatedTo(User::class)->nullable()->foreign_key->onDeleteCascade();`
     *
     * @param string $class
     * @param string $name
     * @return SingleCol
     */
    public function relatedTo(string $class, string $name = null)
    {
        $name ??= Text::snake(Text::afterLast($class, "\\")) . "_" . $class::getPrimaryKey();

        $col = $this->unsignedBigint($name);
        $col->foreign($class);
        return $col;
    }

    /**
     * افزودنی ستونی که با آیدی کلاس مورد نظر رابطه داشته باشد
     * 
     * این تابع، ویژگی اجباری بودن رابطه را اضافه می کند، به این صورت که اگر مقدار رابطه حذف شود، این ردیف نیز حذف می شود
     * 
     * نام پیشفرض ستون به این شکل است.
     * `Text::snake($class) . "_" . $primary`
     *
     * نوع این ستون: `unsignedBigInt`
     * 
     * **Examples:**
     * 
     * `$table->forceRelatedTo(Post::class);`.
     * 
     * @param string $class
     * @param string|null $name
     * @return SingleCol
     */
    public function forceRelatedTo(string $class, string $name = null)
    {
        $col = $this->relatedTo($class, $name);
        $col->foreign_key->onDeleteCascade();
        return $col;
    }

    /**
     * افزودنی ستونی که با آیدی کلاس مورد نظر رابطه داشته باشد
     * 
     * این تابع، ویژگی قابل نال بودن رابطه را اضافه می کند، به این صورت که مقدار پیشفرض این ستون نال است و اگر مقدار رابطه حذف شود، مقدار این ستون نال می شود
     * 
     * نام پیشفرض ستون به این شکل است.
     * `Text::snake($class) . "_" . $primary`
     *
     * نوع این ستون: `unsignedBigInt`
     * 
     * `$table->nullableRelatedTo(Cover::class);`
     * 
     * @param string $class
     * @param string|null $name
     * @return SingleCol
     */
    public function nullableRelatedTo(string $class, string $name = null)
    {
        $col = $this->relatedTo($class, $name);
        $col->nullable()->foreign_key->onDeleteNull();
        return $col;
    }

    /**
     * ستون نوع برای رابطه مورف اضافه می کند
     *
     * @param string $name
     * @param array|Arrayable|Traversable|string|null $classes
     * @return SingleCol
     */
    protected function addMorphType(string $name, array|Arrayable|Traversable|string $classes = null)
    {
        if(is_null($classes))
        {
            return $this->string($name, 128);
        }
        else
        {
            if(!is_string($classes))
                $classes = array_map(Morph::getGlobalTypeInsteadOf(...), ATool::toArray($classes));
            
            return $this->enum($name, $classes);
        }
    }

    /**
     * افزودن ستون های مربوط به رابطه مورف
     *
     * @param string $name
     * @param array|Arrayable|Traversable|string|null $classes
     * @param string|null $indexName
     * @return void
     */
    public function morphs(string $name, array|Arrayable|Traversable|string $classes = null, string $indexName = null)
    {
        $this->addMorphType("{$name}_type", $classes);

        $this->unsignedBigint("{$name}_id");

        $this->index(["{$name}_type", "{$name}_id"], $indexName);
    }

    /**
     * افزودن ستون های مربوط به رابطه مورف
     *
     * @param string $name
     * @param array|Arrayable|Traversable|string|null $classes
     * @param string|null $indexName
     * @return void
     */
    public function nullableMorphs(string $name, array|Arrayable|Traversable|string $classes = null, string $indexName = null)
    {
        $this->addMorphType("{$name}_type", $classes)->nullable();

        $this->unsignedBigint("{$name}_id")->nullable();

        $this->index(["{$name}_type", "{$name}_id"], $indexName);
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

    /**
     * افزودن ایندکس
     *
     * @param string $type
     * @param string|array $columns
     * @param ?string $name
     * @return void
     */
    public function addIndex(string $type, string|array $columns, ?string $name)
    {
        if(!is_array($columns))
        {
            $columns = [ $columns ];
        }

        $name ??= $this->createIndexName($type, $columns);

        $this->indexs[] = new SingleIndex($type, $columns, $name);
    }

    /**
     * افزودن یک ایندکس کلید اصلی
     *
     * @param string|array $columns
     * @param ?string $name
     * @return void
     */
    public function primary(string|array $columns, ?string $name = null)
    {
        $this->addIndex('PRIMARY', $columns, $name);
    }

    /**
     * افزودن یک ایندکس کلید یکتا
     *
     * @param string|array $columns
     * @param ?string $name
     * @return void
     */
    public function unique(string|array $columns, ?string $name = null)
    {
        $this->addIndex('UNIQUE', $columns, $name);
    }

    /**
     * افزودن یک ایندکس معمولی
     *
     * @param string|array $columns
     * @param ?string $name
     * @return void
     */
    public function index(string|array $columns, ?string $name = null)
    {
        $this->addIndex('', $columns, $name);
    }

    /**
     * افزودن یک ایندکس کلید فول تکست
     *
     * @param string|array $columns
     * @param ?string $name
     * @return void
     */
    public function fullText(string|array $columns, ?string $name = null)
    {
        $this->addIndex('FULLTEXT', $columns, $name);
    }

    /**
     * افزودن یک ایندکس spatial
     *
     * @param string|array $columns
     * @param ?string $name
     * @return void
     */
    public function spatialIndex(string|array $columns, ?string $name = null)
    {
        $this->addIndex('SPATIALINDEX', $columns, $name);
    }

    /**
     * یک نام برای ایندکس ایجاد می کند
     *
     * @param string $type
     * @param array $columns
     * @return string
     */
    protected function createIndexName(string $type, array $columns)
    {
        $index = strtolower($this->tableName . '_' . implode('_', $columns) . '_' . strtolower($type));

        return str_replace(['-', '.'], '_', $index);
    }

    /**
     * ایندکس ها را بر می گرداند
     *
     * @return Arr<SingleIndex>
     */
    public function getIndexs()
    {
        return arr($this->indexs);
    }

    private array $onInstallEvents = [];

    /**
     * @param Closure $callback `function(QueryCol $old, QueryCol $new) { echo 'Before'; yield true; echo 'After'; }`
     * @return void
     */
    public function onInstall(Closure $callback, bool $runAfterInstall = false)
    {
        if($runAfterInstall)
        {
            $callback = function() use($callback)
            {
                yield true;
                return $callback();
            };
        }

        $this->onInstallEvents[] = $callback;
    }

    public function fireInstallBefore(QueryCol $before)
    {
        $after = [];

        foreach($this->onInstallEvents as $event)
        {
            $result = $event($before, $this);
            if($result instanceof Generator)
            {
                $after[] = $result;
            }
        }

        return $after;
    }

    public function fireInstallAfter(array $events)
    {
        foreach($events as $event)
        {
            if($event instanceof Generator)
            {
                $event->next();
            }
        }
    }

    // public int $versionValue = 0;

    // /**
    //  * تنظیم ورژن
    //  *
    //  * @param integer $version
    //  * @return void
    //  */
    // public function version(int $version)
    // {
    //     $this->versionValue = $version;
    // }

    // public array $upgradeEvents = [];

    // /**
    //  * تنظیم می کند زمانی که این جدول در حال بروزرسانی به این ورژن می باشد این تابع اجرا شود
    //  * 
    //  * هر جا که کد زیر را بنویسید، در آن بخش عملیات ارتقای دیتابیس اجرا می شود:.
    //  * `yield true;`
    //  * 
    //  * همیشه متد شما قبل از ارتقای دیتابیس صدا زده می شود مگر ورودی آخر این تابع را ترو کنید
    //  *
    //  * @param integer $version
    //  * @param Closure $callback
    //  * @param bool $runAfterUpgrade
    //  * @return void
    //  */
    // public function upgrade(int $version, Closure $callback, bool $runAfterUpgrade = false)
    // {
    //     $this->upgradeEvents[] = [
    //         'version' => $version,
    //         'callback' => $callback,
    //         'after' => $runAfterUpgrade,
    //     ];
    // }

    // public function runUpgradeBefore(int $oldVersion)
    // {
    //     $events = arr($this->upgradeEvents)
    //             ->where('version', '>', $oldVersion)
    //             ->sortBy('version');

    //     foreach($events as $i => $event)
    //     {
    //         if(!$event['after'])
    //         {
    //             $callback = $event['callback'];
    //             $result = $callback();

    //             if(!($result instanceof Generator))
    //             {
    //                 $events->
    //             }
    //         }
    //     }
    // }

    // public function runUpgradeAfter(Arr $events)
    // {
        
    // }

}

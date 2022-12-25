<?php

namespace Mmb\Db; #auto

abstract class QueryResult {

    /**
     * موفق بودن عملیات
     *
     * @var bool
     */
    public $ok;

    /**
     * گرفتن یک ردیف خروجی
     *
     * @return array|bool
     */
    public abstract function fetch();

    /**
     * گرفتن ردیف خروجی بصورت کلاس
     *
     * @param string $class
     * @return object|bool
     */
    public function fetchAs($class) {

        $fetch = $this->fetch();
        if(!$fetch)
            return false;

        return new $class($fetch);

    }

    /**
     * گرفتن تمامی ردیف های خروجی
     *
     * @return array
     */
    public abstract function fetchAll();

    /**
     * گرفتن تمامی ردیف های خروجی بصورت کلاس
     *
     * @param string $class
     * @return array
     */
    public function fetchAllAs($class) {

        return array_map(function($value) use($class) {

            return new $class($value);

        }, $this->fetchAll());

    }

    /**
     * گرفتن ستون خاص از تمامی ردیف های خروجی
     *
     * @param string $name
     * @return array
     */
    public function fetchPluck($name) {

        return array_map(function($value) use($name) {

            return $value[$name];

        }, $this->fetchAll());

    }

    /**
     * گرفتن دو ستون خاص از تمامی ردیف های خروجی به عنوان کلید و مقدار آرایه
     *
     * @param string $key
     * @param string $value
     * @return array
     */
    public function fetchPluckAssoc($key, $value) {

        $array = [];
        foreach($this->fetchAll() as $row)
        {
            $array[$row[$key]] = $row[$value];
        }
        return $array;

    }

    /**
     * گرفتن آیدی اینسرت شده
     *
     * @return mixed
     */
    public abstract function insertID();

    /**
     * تبدیل خروجی به کوئری کول
     *
     * @return \Mmb\Db\QueryCol
     */
    public abstract function toQueryCol();

}

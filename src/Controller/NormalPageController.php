<?php
#auto-name
namespace Mmb\Controller;

use Mmb\Mapping\Arr;

abstract class NormalPageController extends Controller
{


    public abstract function query();

    public function perPage()
    {
        return 10;
    }

    public function pageCount()
    {
        return ceil($this->query()->count() / $this->perPage());
    }

    public static function keyPage($text, $page = 1)
    {
        return static::key($text, 'pageResponse', $page);
    }

    public $currentPage;
    public $nextPage;
    public $prevPage;
    public final function pageResponse($page = 1)
    {
        $limit = $this->perPage();
        $data = $this->query()->limit($limit)->offset($limit * ($page - 1))->all();
        $nextExists = $this->query()->offset($limit * $page)->exists();
        
        $this->currentPage = $page;
        $this->prevPage = $page - 1;
        $this->nextPage = $nextExists ? $page + 1 : 0;

        return $this->page($data);
    }

    public function empty()
    {
        if(callback())
            answer("لیست خالیست", true);
        else
            response("لیست خالیست");
    }

    public abstract function page(Arr $data);

}

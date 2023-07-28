<?php
#auto-name
namespace Mmb\Controller;
use Mmb\Controller\QueryControl\CallbackControl;
use Mmb\Controller\QueryControl\QueryBooter;
use Mmb\Mapping\Arr;

abstract class InlinePageController extends Controller
{

    use CallbackControl;
    public function bootCallback(QueryBooter $booter)
    {
        $name = $this->name();
        $booter->pattern("$name:1")
                ->invoke('pageResponse');
        $booter->pattern("$name:{page}")
                ->int('page')
                ->invoke('pageResponse');
    }

    public function main()
    {
        return $this->pageResponse();
    }


    public abstract function name();

    public abstract function query();

    public function perPage()
    {
        return 10;
    }

    public function pageCount()
    {
        return ceil($this->query()->count() / $this->perPage());
    }

    public $currentPage;
    public $nextPage;
    public $prevPage;
    public final function pageResponse($page = 1)
    {
        $limit = $this->perPage();
        $data = $this->query()->limit($limit)->offset($limit * ($page - 1))->all();
        $nextExists = $this->pageCount > $page;
        
        $this->currentPage = $page;
        $this->prevPage = $page - 1;
        $this->nextPage = $nextExists ? $page + 1 : 0;

        if(!$data)
            return $this->empty();

        if(callback())
        {
            setResponse('editText');
        }

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

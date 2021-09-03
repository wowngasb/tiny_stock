<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/1/27 0027
 * Time: 10:37
 */

namespace app\Libs;


use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Pagination\BootstrapThreePresenter;
use Illuminate\Support\HtmlString;
use Tiny\Interfaces\RequestInterface;

class NewPaginationPresenter extends BootstrapThreePresenter
{

    private $_request;

    public function __construct(PaginatorContract $paginator, RequestInterface $request)
    {
        $this->_request = $request;
        parent::__construct($paginator);
    }

    /**
     * Convert the URL window into Bootstrap HTML.
     *
     * @return HtmlString
     */
    public function render()
    {
        if ($this->hasPages()) {
            $getPreviousButton = $this->getPreviousButton();
            $getLinks = $this->getLinks();
            $getNextButton = $this->getNextButton();
            $getGoToButton = $this->getGoToButton($this->_request->all_get());
            $html_str = <<<EOT
<span style="display: inline">
    <ul class="pagination" style="display: block;">
        {$getPreviousButton}
        {$getLinks}
        {$getNextButton}
    </ul>
</span>
<span style="display: inline">
    {$getGoToButton}
</span>
EOT;
            return new HtmlString($html_str);
        }

        return new HtmlString('');
    }

    private function getGoToButton($args)
    {
        $input_list = [];
        unset($args['page']);
        foreach ($args as $key => $val) {
            $key = trim($key);
            if ($key !== '') {
                $key = urlencode($key);
                $val = urlencode($val);
                $input_list[] = "<input type='hidden' name='{$key}' value='{$val}'>";
            }
        }
        $input_str = join('', $input_list);

        return <<<EOT
    <form method="GET" >
        {$input_str}
        <span style="margin-left: 10px;">第 <input class="form-control" type="text" name="page" style="width: 50px;"> 页</span>
        <button class="btn btn-primary" type="submit" value="">Go</button>
    </form>
EOT;

    }
}
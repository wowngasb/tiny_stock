<?php

namespace app\Http\Controllers\Front;

use app\App;
use app\Http\base\FrontController;
use app\Libs\BompConst;


class IndexController extends FrontController
{
    public function beforeAction(array $params)
    {
        $params = parent::beforeAction($params);

        if (App::config('ENV_WEB.hide_index')) {
            return $this->getResponse()->end();
        }

        $site_title = App::config('ENV_WEB.');
        $this->assign('site_title', $site_title);
        return $params;
    }

    public function index()
    {
        if ($this->_get('_bomp', 0)) {
            $bomp = BompConst::get_bomp();
            header("Content-Encoding: gzip");
            header("Content-Length: " . strlen($bomp));
            //Turn off output buffering
            if (ob_get_level()) ob_end_clean();
            //send the gzipped file to the client
            return $this->getResponse()->end($bomp);
        } else {
            return $this->view('front.index');
        }
    }

}
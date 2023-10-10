<?php
namespace app\index\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cache;


class ErrorPage  extends BaseController
{
    
    public function index(){
        $otherclass = new Otherclass($this->app);
        $host_data = $otherclass->getHostData(Request::host());

        $this->error("It's used for phishing, URL blocked!",$host_data[0]['http_prefix'].$host_data[0]['domain_url'],5);
    }

}
<?php
namespace app\index\controller;


use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;


class TermsOfService  extends BaseController
{

    public function index(){
        $otherclass = new Otherclass($this->app);
        $host_data = $otherclass->getHostData(Request::host());


        $title = "Terms of Service - ".$host_data[0]['site_name'];
        $keywords = "Report Malicious URL";
        $description = "This page describes the conditions of use of the URL shortener service.";
        

        $domain_url = $host_data[0]['http_prefix'].Request::host()."/";


        View::assign("domain_url", $domain_url);
        View::assign("title", $title);
        View::assign("keywords", $keywords);
        View::assign("description", $description);
        View::assign("year_num", Config::get("app.year_num"));
    
        return View::fetch("/Template_".$host_data[0]['template_num']."/TermsOfService/terms-of-service");




    }
}

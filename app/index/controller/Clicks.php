<?php
namespace app\index\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;


class Clicks extends BaseController
{

    public function index(){
        
            
        $otherclass = new Otherclass($this->app);
        $host_data = $otherclass->getHostData(Request::host());

        //var_dump($host_data);
        $site_id = $host_data[0]['itemid'];
        $title = "URL Click Counter";
        $keywords = "URL Click Counter,URL Click,counter";
        $description = "Click counter shows in real time how many clicks your shortened URL received so far.";




        //获取当前访问的url
        $domain_url = $host_data[0]['http_prefix'].Request::host()."/";

        View::assign("domain_url", $domain_url);
        View::assign("title", $title);
        View::assign("keywords", $keywords);
        View::assign("description", $description);
        View::assign("year_num", Config::get("app.year_num"));
        

        return View::fetch("/Template_".$host_data[0]['template_num']."/Clicks/total-clicks-index");

    }





    public function total(){
        $otherclass = new Otherclass($this->app);
        $remote_ip = $otherclass->get_user_ip();
        $country = $otherclass->get_country($remote_ip);

        $url = Request::param("url") ? Request::param("url") : "";

        //echo $url;


        //当url为空时就直接展示模板，否则就启用查询url
        if($url == ""){
            $this->error("URL error",'/url-click-counter',3);
        }
        //当url不为空时
        //将url开始分割，将short_str分割出来
        $url_array = preg_split("/\..*?\//", $url);
        
        $short_str = "";

        if(count($url_array) == 2){
            $short_str = $url_array[1];
        }else{
            //如果1没有分割好说明是用户输入的数据有问题，采用数组下标0即可  此时可匹配用户直接输入short_str进行查询
            $short_str = $url_array[0];
        }



        //当short_str长度为0时，说明short_str传递参数有问题，提示错误
        if(strlen($short_str)==0){
            $this->error("URL error",'/url-click-counter',3);
        }

        $data = Db::table("tp_shortener")->where("short_url","=",$short_str)->select();
        //var_dump($data);


        //当返回的数据长度大于0时说明有匹配到结果，否则就点击数就是0
        if(count($data) > 0){
            $hits_num = $data[0]['hits'];
        }else{
            $hits_num = 0;
        }




        try{

            //记录日志
            $this->log($remote_ip." - {$country} - ".$url." - Hits: {$hits_num}","counter_file_u6e.log");


        }catch(\Exception $e){
            //echo "1";
        }





        //获取当前主url的数据
        
        $host_data = $otherclass->getHostData(Request::host());

        //var_dump($host_data);
        $site_id = $host_data[0]['itemid'];
        $title = "Total URL Clicks - ".$host_data[0]['site_name'];
        $keywords = "Total URL Clicks";
        $description = "The number of clicks that your shortened URL received.";
        //获取当前访问的url
        $domain_url = $host_data[0]['http_prefix'].Request::host()."/";

        View::assign("domain_url", $domain_url);
        View::assign("title", $title);
        View::assign("keywords", $keywords);
        View::assign("description", $description);
        View::assign("hits_num", $hits_num);
        View::assign("year_num", Config::get("app.year_num"));
        

        return View::fetch("/Template_".$host_data[0]['template_num']."/Clicks/total-clicks-index-post");

        
    }

}
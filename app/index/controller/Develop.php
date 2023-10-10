<?php
namespace app\index\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Cache;




class Develop  extends BaseController
{

    public function index(){
        $limit_time =30; //单位秒，默认限制速率为30秒

        $otherclass = new Otherclass($this->app);
        #  ---------------------- 首页是否支持JS渲染验证 begin ----------------------
        $hash_str = Request::param("hash_str") ? Request::param("hash_str") : "";

        //echo "hash_str:".$hash_str;

        #如果数据不匹配就提示错误页面
        if(!Cache::has(Config::get("app.redis_prefix")."index_hash_str".$hash_str) || $hash_str == ""){
            $this->error("User-agent error,Please try again.",'/',10);
        }
        #  ---------------------- 首页是否支持JS渲染验证 end ----------------------

        $user_agent = $_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : "";
        $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ? $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] : "";

        if(!Cache::has(Config::get("app.redis_prefix")."index_hash_str".$hash_str) || $hash_str == ""){
            $this->error("User-agent error,Please try again.",'/',3);
        }


        $redis_key = Config::get("app.redis_prefix")."-api-token-".md5($user_agent.$lang);


    
        if(Cache::has($redis_key)){
            $token_str = Cache::get($redis_key);
        }else{
            $token_str = substr(md5($user_agent.$lang.time()),0,16);

            Cache::set($redis_key,$token_str);

            
            Db::table("tp_api_token")->insert(['token'=>$token_str,"limit_time"=>$limit_time]);
        }

        echo "Method:POST"."    <br/>\n";
        echo "Shortener api url:"."https://m5.gs/shortener-api"."    <br/>\n";
        echo "Your token:".$token_str;
             
    }

}
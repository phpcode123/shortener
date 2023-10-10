<?php
namespace app\index\controller;


use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cookie;


class Adstxt extends BaseController
{

    public function index(){
        $adsense_data = Db::table("tp_adsense")->where("adsense_domain",Request::host())->select();

        if(count($adsense_data) == 1){
            //输出该域名的adsense_txt 文本
            echo $adsense_data[0]['adsense_txt'];
        }else{
            
            $ads_file = fopen(__DIR__."/../../../public/ads.txt","r+");
            
            echo fgets($ads_file);
           
            fclose($ads_file);
        
        }

    }
}
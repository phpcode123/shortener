<?php
namespace app\index\controller;


use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Cookie;
use GuzzleHttp\Client;


class RemoteSetMaliciousApi extends BaseController
{
    //通过web接口获取itemid、url等参数，使用asc排序，json数据
    //获取check_malicious_status为1的数据，默认为0,先得检查url是否是恶意链接，脚本运行后会将check_malicious_status值设置为1,然后再是check_malicious_2来运行
    public function index(){

        $api_password = Request::param("api_password") ?  Request::param("api_password") : "";
        if($api_password != Config::get("app.api_password") || empty($api_password)){
            return ">> HttpApi get api password error.\n";
        }
        $itemid = Request::param("itemid") ?? "";
        $is_404 = Request::param("is_404") ?? "";

        //echo Db::table("tp_shortener")->fetchSql(true)->where("itemid",$itemid)->update(["is_404"=>$is_404]);
        try{
            Db::table("tp_shortener")->where("itemid",$itemid)->update(["is_404"=>$is_404]);
            echo "success";
        }catch(\Exception $e){
            echo "fail";
            var_dump($e);
        }

        //     echo "success";
        // }else{
        //     echo "fail";
        // }


    }

}
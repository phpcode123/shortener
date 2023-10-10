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


class HttpApi extends BaseController
{
    //通过web接口获取itemid、url等参数，使用asc排序，json数据
    //获取check_malicious_status为1的数据，默认为0,先得检查url是否是恶意链接，脚本运行后会将check_malicious_status值设置为1,然后再是check_malicious_2来运行
    public function check_malicious_2_get_api(){

        $api_password = Request::param("api_password") ?  Request::param("api_password") : "";
        if($api_password != Config::get("app.api_password") || empty($api_password)){
            return ">> HttpApi get api password error.\n";
        }

        //-------------------- 设置cache，用于后台显示统计  begin --------------------
        $cache_key = Config::get("app.redis_prefix")."check_malicious_2_post";
        Cache::set($cache_key, time(),1800);
        //-------------------- 设置cache，用于后台显示统计  end --------------------



        $data = Db::table("tp_shortener")->where("check_malicious_status","1")->order("itemid","desc")->limit(1)->select();
        if(count($data) == 1){
            $json_data = [
                "status_code" => 1,
                "message" => "success",
                "data" => $data[0]
            ];


            
        }else{
            $json_data = [
                "status_code" => 0,
                "message" => "Data length less than 1",
                "data" => "none"
            ];
            
        }

        echo json_encode($json_data);

    }


    //通过post数据
    public function check_malicious_2_post_api(){
        if(Request::isPost()){

            //-------------------- 设置cache，用于后台显示统计  begin --------------------
            $cache_key = Config::get("app.redis_prefix")."check_malicious_2_post";
            Cache::set($cache_key, time(),1800);
            //-------------------- 设置cache，用于后台显示统计  end --------------------



            $itemid = Request::param("itemid") ? Request::param("itemid") : "";
            $check_malicious_url_value = Request::param("check_malicious_url_value") ?? "";
            $adsense_switch = Request::param("adsense_switch") ?? "0";
            
            //api_password密码的校验
            $api_password = Request::param("api_password") ?? "";
            
            if($api_password != Config::get("app.api_password") || empty($api_password)){

                return  ">> HttpApi post api password error.\n";
            }

            // echo "itemid:{$itemid}\n";
            // echo "check_malicious_url_value:{$check_malicious_url_value}\n";

            if(empty($itemid) || empty($check_malicious_url_value)){
                $json_data = [
                    "status_code" => 0,
                    "message" => "error,parameter is null",
                ];
            }else{

                $update_data = [
                    "check_malicious_status" => $check_malicious_url_value,
                    "adsense_switch" => $adsense_switch
                ];

                //更新数据
                if(Db::table("tp_shortener")->where("itemid",$itemid)->update($update_data)){

                    $json_data = [
                        "status_code" => 1,
                        "message" => "Data update success\n",
                    ];

                }else{
                    $json_data = [
                        "status_code" => 0,
                        "message" => "Data update error\n",
                    ];
                }
            }
            return json_encode($json_data);

        }else{
            echo  "Method error, just only post.\n";
            return "";
        }


    }
}
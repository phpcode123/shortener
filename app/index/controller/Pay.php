<?php
namespace app\index\controller;


use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Cache;


class Pay extends BaseController
{

    public function index(){
        $otherclass = new Otherclass($this->app);

        $host_data = $otherclass->getHostData(Request::host());


        $title = "Payment notice.";
        $keywords = "Payment notice.";
        $description = "Payment notice.";
        
        if(Config::get("app.server_upgrade_status") == 1){
            return  Config::get("app.server_upgrade_tips");
            
        }


        $url = Request::param("url") ?? "";
        if($url == ""){
            abort(404,"url is null");
        }

        $url_decode = base64_decode(str_rot13($url));


        //--------------------黑名单关键词判断 begin-------------------------
        $money = "9.9";  //价格设置默认值
        $black_url_data = Db::table("tp_black_url")->where("success",0)->select();
        for($x=0;$x<count($black_url_data);$x++){
            if(preg_match_all('#'.$black_url_data[$x]['pattern'].'#i',$url_decode)){
                
                $money = $black_url_data[$x]['price'];
                //跳出当前循环
                break;
            }
        }


        $money_encode=str_rot13(base64_encode($money));

        //--------------------黑名单关键词判断 end -------------------------
        $usdt_pay_url = "/pay/usdt?url=".$url."&money=".$money_encode;


        View::assign("title",$title);
        View::assign("keywords",$keywords);
        View::assign("description",$description);
        View::assign("money",$money);
        View::assign("usdt_pay_url",$usdt_pay_url);


        return View::fetch("/Template_".$host_data[0]['template_num']."/Pay/pay_index");

    }


}
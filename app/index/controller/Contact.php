<?php
namespace app\index\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cache;


class Contact extends BaseController
{

    public function index(){


        //当请求模式为post时
        if(Request::isPost()){
            $otherclass = new Otherclass($this->app);
            $data = Request::post();


            // ---------------  时间戳验证码验证 begin  ------------------------------
            $hash_str = Request::param("hash_str") ? Request::param("hash_str") : "";
            if(!Cache::has(Config::get("app.redis_prefix")."contact_hash_str".$hash_str) || $hash_str == ""){
                $this->error("Please try again.",'/',5);
            }

            // ---------------  时间戳验证码验证 end  ------------------------------



            //数字验证码
            $cptc = $data["cptc"];
            $cptc_number_1 = $data["cptc_number_1"];
            $cptc_number_2 = $data["cptc_number_2"];

            $name = $data["name"];
            $message = $data["message"];
            $email = $data["email"];


            //--------------------黑名单关键词判断 begin-------------------------
            $black_num = 0;//黑名单状态码
            $contact_black_word = Config::get('app.contact_black_word');
            for($x=0;$x<count($contact_black_word);$x++){
                if(preg_match_all('/'.$contact_black_word[$x].'/i',$message) || preg_match_all('/'.$contact_black_word[$x].'/i',$name)  || preg_match_all('/'.$contact_black_word[$x].'/i',$email)){
                    
                    $black_num = 1;
                    break;//跳出当前循环
                }
            }


            if($black_num == 1){
                
                $this->error("Error, Please try again!",'/',10);
            }

            //--------------------黑名单关键词判断 end -------------------------
            

            $remote_ip = $otherclass->get_user_ip();
            $user_county = $otherclass->get_country($remote_ip);

            //验证通过就开始插入数据库
            if($cptc_number_1 + $cptc_number_2 == $cptc){
                $insert_data = [
                    "name" => $name,
                    "remote_ip" => $remote_ip,
                    'country' => $user_county,
                    "message" => $message,
                    "email" => $email,
                    "timestamp" => time()
                ];

                if(Db::table("tp_contact")->strict(false)->insert($insert_data)){
                    $this->success("Submit success",'/contact',2);
                }else{
                    $this->error("Unknown error",'/contact',3);
                }

            }else{
                $this->error("Captcha error",'/contact',3);
            }

        }else{
            $otherclass = new Otherclass($this->app);
            $host_data = $otherclass->getHostData(Request::host());
    
    

            //随机验证码,验证码的核心逻辑就是客户端与服务端的md5(ua+"-"+timestamp)进行验证,index首页使用timestamp进行伪装
            //verification
            $index_timestamp = time();
            $hash_str = md5(Request::header('user-agent')."-".$index_timestamp);
            Cache::set(Config::get("app.redis_prefix")."contact_hash_str".$hash_str, $value=1, $overtime=60*30);
            View::assign("index_timestamp",$index_timestamp);

            // hash验证 end



            $title = "Contact - ".$host_data[0]['site_name'];
            $keywords = "contact";
            $description = "If you have a question or a problem, you can reach our team by using the contact form.";
            
    
            $domain_url = $host_data[0]['http_prefix'].Request::host()."/";
    
    
            $cptc_number_1 = mt_rand(0,9);
            $cptc_number_2 = mt_rand(0,9);
            View::assign("cptc_number_1",$cptc_number_1);
            View::assign("cptc_number_2",$cptc_number_2);


            View::assign("domain_url", $domain_url);
            View::assign("title", $title);
            View::assign("keywords", $keywords);
            View::assign("description", $description);
            View::assign("year_num", Config::get("app.year_num"));
        
            return View::fetch("/Template_".$host_data[0]['template_num']."/Contact/contact");
        }



    }
}

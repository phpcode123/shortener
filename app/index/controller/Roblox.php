<?php
namespace app\index\controller;


use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Cookie;
use think\facade\Session;


class Roblox  extends BaseController
{
    public function show(){
        $otherclass = new Otherclass($this->app);
        $short_url = Request::param("short_str") ?? Request::param("short_str");

        if($short_url == ""){
            abort(404,"数据不存在");
        }


        $host_data = $otherclass->getHostData(Request::host());
        $adsense_host_data = $otherclass->get_adsense_host_data(Request::host());
        view::assign("adsense_host_data",$adsense_host_data);


        $data = Db::table("tp_youtube_url")->where("youtube_short_url",$short_url)->select();
        

        //var_dump($data);

        //当$data的长度为0时说明在数据库中没有匹配到该数据，跳转首页
        if(count($data) == 0){
            $this->error("URL ERROR！",$host_data[0]['http_prefix'].$host_data[0]['domain_url'],3);
        }


        $youtube_title = $data[0]['title'];
        $youtube_keyword = $data[0]['keyword'];
        $youtube_description = $data[0]['description'];
        $youtube_url = $data[0]['youtube_url'];
        $youtube_short_url = $data[0]['youtube_short_url'];


        view::assign("youtube_title",$youtube_title);
        view::assign("youtube_description",$youtube_description);
        view::assign("youtube_keyword",$youtube_keyword);
        view::assign("youtube_url",$youtube_url);
        view::assign("youtube_short_url",$youtube_short_url);
        


        //-----------------  获取recommand推荐值 begin ---------------
        $roblox_redis_key = Config::get("app.redis_prefix")."reblox_show_recom_{$short_url}";
        
        if(Cache::get($roblox_redis_key)){
            $recom_itemid_str = Cache::get($roblox_redis_key);
        }else{
            $recom_itemid_str = $otherclass->roblox_recom_itemid_str(9);
            
            Cache::set($roblox_redis_key,$recom_itemid_str,60*60*24);
        }

        $roblox_recom_data = Db::table("tp_youtube_url")->where("itemid","in",$recom_itemid_str)->order("itemid","asc")->select();

        view::assign("roblox_recom_data",$roblox_recom_data);
        //-----------------  获取recommand推荐值 end ---------------


    

        return View::fetch("/Template_".$host_data[0]['template_num']."/Roblox/roblox_show");
    }




    public function contact_us(){

        $Otherclass = new Otherclass($this->app);

        //当请求模式为post时
        if(Request::isPost()){
            
            $data = Request::post();

            // ---------------  时间戳验证码验证 begin  ------------------------------
            $hash_str = Request::param("hash_str") ? Request::param("hash_str") : "";
            if(!Cache::get(Config::get("app.redis_prefix")."contact_hash_str".$hash_str) || $hash_str == ""){
                $this->error("Please try again.",'/contact-us',5);
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
                if(preg_match_all('#'.$contact_black_word[$x].'#i',$message) || preg_match_all('#'.$contact_black_word[$x].'#i',$name)  || preg_match_all('#'.$contact_black_word[$x].'#i',$email)){
                    
                    $black_num = 1;
                    break;//跳出当前循环
                }
            }


            if($black_num == 1){
                
                $this->error("Error, Please try again!",'/',10);
            }

            //--------------------黑名单关键词判断 end -------------------------


            //验证通过就开始插入数据库
            if($cptc_number_1 + $cptc_number_2 == $cptc){
                $insert_data = [
                    "name" => $name,
                    "remote_ip" => $Otherclass->get_user_ip(),
                    "message" => $message,
                    "email" => $email,
                    "timestamp" => time()
                ];

                if(Db::table("tp_contact")->strict(false)->insert($insert_data)){
                    $this->success("Successfully",'/',5);
                }else{
                    $this->error("Unknown error",'/contact-us',5);
                }

            }else{
                $this->error("Captcha error",'/contact-us',3);
            }

        }else{
            
            $host_data = $Otherclass->getHostData(Request::host());
    


            //随机验证码,验证码的核心逻辑就是客户端与服务端的md5(ua+"-"+timestamp)进行验证,index首页使用timestamp进行伪装
            //verification
            $index_timestamp = time();
            $hash_str = md5(Request::header('user-agent')."-".$index_timestamp);
            Cache::set(Config::get("app.redis_prefix")."contact_hash_str".$hash_str, $value=1, $overtime=60*30);
            View::assign("index_timestamp",$index_timestamp);

            // hash验证 end



    
            $title = "Contact us - ".$host_data[0]['site_name'];
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
        
           
            return View::fetch("/Template_".$host_data[0]['template_num']."/Roblox/roblox_contact");
        }

    }


    public function roblox_search(){
        $Otherclass = new Otherclass($this->app);

        if(Request::isPost()){
            $keyword = Request::param("keyword") ?? Request::param("keyword");
            
            if(strlen($keyword) < 3){
                $this->error("Keyword too short...","/",5);
            }

            $data = Db::table("tp_youtube_url")->where("title","like","%".$keyword."%")->limit(20)->select();
            View::assign("data",$data);





            //-----------------  获取recommand推荐值 begin ---------------
            $roblox_redis_key = Config::get("app.redis_prefix")."reblox_search_recom";
                    
            if(Cache::get($roblox_redis_key)){
                $recom_itemid_str = Cache::get($roblox_redis_key);
            }else{
                $recom_itemid_str = $Otherclass->roblox_recom_itemid_str(10);
                
                //搜索结果随机推荐缓存10分钟 ,
                Cache::set($roblox_redis_key,$recom_itemid_str,60*10);
            }

            $roblox_recom_data = Db::table("tp_youtube_url")->where("itemid","in",$recom_itemid_str)->order("itemid","asc")->select();

            view::assign("roblox_recom_data",$roblox_recom_data);
            //-----------------  获取recommand推荐值 end ---------------


            $host_data = $Otherclass->getHostData(Request::host());
            return View::fetch("/Template_".$host_data[0]['template_num']."/Roblox/roblox_list");
        



        }else{
            $this->error("Error","/",3);
        }
    }



    public function landing(){
        $this->error("please login...","/user-login",1);
    }



    public function user_login(){
        $Otherclass = new Otherclass($this->app);

        $host_data = $Otherclass->getHostData(Request::host());

        return View::fetch("/Template_".$host_data[0]['template_num']."/Roblox/roblox_login");
    }

    public function user_login_post(){
        $this->error("Password Error!",$_SERVER['HTTP_REFERER'],10);
    }
}
                 

                    
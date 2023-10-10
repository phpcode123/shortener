<?php
namespace app\index\controller;


use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cache;


class ReportMaliciousUrl  extends BaseController
{

    public function index(){

        //当请求模式为post时
        if(Request::isPost()){
            $otherclass = new Otherclass($this->app);
            $data = Request::post();


            // ---------------  时间戳验证码验证 begin  ------------------------------
            $hash_str = Request::param("hash_str") ? Request::param("hash_str") : "";
            if(!Cache::has(Config::get("app.redis_prefix")."reportMaliciousUrl_hash_str".$hash_str) || $hash_str == ""){
                $this->error("Please try again.",'/',5);
            }

            // ---------------  时间戳验证码验证 end  ------------------------------

            //数字验证码
            $cptc = $data["cptc"];
            $cptc_number_1 = $data["cptc_number_1"];
            $cptc_number_2 = $data["cptc_number_2"];

            $url = $data["url"];
            $comment = $data["comment"];
            $email = $data["email"];


            $otherclass = new Otherclass($this->app);

            //---------------------------------- 判断url是否是本站的url begin ----------------------------------
            $domain_data = Db::table("tp_domain")->order("itemid","desc")->select();
            $domain_num = 0;//状态码
            for($x=0;$x<count($domain_data);$x++){
                if(preg_match_all('/'.$domain_data[$x]['domain_url'].'/i',$url)){
                    $domain_num = 1;
                    break;
                }
            }
            //return ;
            if($domain_num == 0){
                $this->error("Report URL is not belong our site.",$_SERVER['HTTP_REFERER'],10);
            }


            //---------------------------------- 判断url是否是本站的url begin ----------------------------------






            $remote_ip = $otherclass->get_user_ip();
            $user_county = $otherclass->get_country($remote_ip);

            //验证通过就开始插入数据库
            if($cptc_number_1 + $cptc_number_2 == $cptc){
                $insert_data = [
                    "url" => $url,
                    "remote_ip" => $remote_ip,
                    'country' => $user_county,
                    "comment" => $comment,
                    "email" => $email,
                    "timestamp" => time()
                ];

                if(Db::table("tp_report_malicious_url")->strict(false)->insert($insert_data)){
                    $this->success("Report success",'/report-malicious-url',2);
                }else{
                    $this->error("Unknown error",'/report-malicious-url',3);
                }

            }else{
                $this->error("Captcha error",'/report-malicious-url',3);
            }

        }else{
            $otherclass = new Otherclass($this->app);
            $host_data = $otherclass->getHostData(Request::host());
    


            //随机验证码,验证码的核心逻辑就是客户端与服务端的md5(ua+"-"+timestamp)进行验证,index首页使用timestamp进行伪装
            //verification
            $index_timestamp = time();
            $hash_str = md5(Request::header('user-agent')."-".$index_timestamp);
            Cache::set(Config::get("app.redis_prefix")."reportMaliciousUrl_hash_str".$hash_str, $value=1, $overtime=60*30);
            View::assign("index_timestamp",$index_timestamp);

            // hash验证 end

    
            $title = "Report Malicious URL - ".$host_data[0]['site_name'];
            $keywords = "Report Malicious URL";
            $description = "Use the form to report malicious short link to our team.";
            
    
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
            
        
            return View::fetch("/Template_".$host_data[0]['template_num']."/ReportMaliciousUrl/report-malicious-url");
        }



    }
}

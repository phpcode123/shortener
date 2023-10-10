<?php
namespace app\index\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Cache;




class ShortenerApi  extends BaseController
{

    public function index(){
        if(Request::isPost()){

            
            /*数据库参数和状态说明
            数据库表项：itemid， token(32位hash字符串)， limit_time(redis限制时间，单位：秒)
                api返回项 
                    array = [
                        "code"  => 1,
                        "shorturl" => "",
                        "message"  =>  ""
                    ];


                code状态码：
                    0  为成功返回shorturl，除了此项其它的项均将shorturl留空
                    1  提供的url有问题或者是恶意url
                    2  超出速率限制
                    3  任何其他的错误包括潜在的问题，比如服务器维护
                    4  token有错误
                    
            */

          
            $otherclass = new Otherclass($this->app);

            $url = Request::param("url") ? Request::param("url") : "";
            $check_site_id = Request::param("check_site_id") ? Request::param("check_site_id") : 1;
            $token = Request::param("token") ? Request::param("token") : "";


            // 判断token是否正确,如果返回的数据长度等于0则说明token验证失败
            $token_data = Db::table("tp_api_token")->where("token",$token)->select();

            //var_dump($token_data);

            if(count($token_data) == 0){
                $json_array = [
                    "code" => 4 ,
                    "shorturl" => "",
                    "message" => "Token verification failed"
                ];
    
                return json_encode($json_array);
            }else{
                //将统计数字自增1
                Db::table("tp_api_token")->where("token",$token)->inc('count')->update();
            }


            #服务器处于升级状态时
            if(Config::get("app.server_upgrade_status") == 1){
                $json_array = [
                    "code" => 3 ,
                    "shorturl" => "",
                    "message" => "Server upgrade, please try again after half an hour..."
                ];
    
                return json_encode($json_array);

            }













            //echo $token_data[0]['limit_time'];

            //判断当前token是否超出限制,当limit_time值为0时说明不限制
            if($token_data[0]['limit_time'] > 0){

                $redis_key = Config::get("app.redis_prefix")."-shortener-api-".$token;

                //判断当前url是否已经储存在redis中，如果已存在说明当前已经超限
                if(Cache::has($redis_key)){
                    $json_array = [
                        "code" => 2 ,
                        "shorturl" => "",
                        "message" => "Over limit, Please try again later."
                    ];
        
                    return json_encode($json_array);
                }else{
                    //将值储存在redis中，过期时间为tp_api_token 中的limit_time限制时间
                    Cache::set($redis_key,"1", $token_data[0]['limit_time']);
                }
    
            }










            //------------------- 移除字符串两边的空格和不可见字符串 begin-------------------------
            //会自动移除不可见的空白字符，如空格、tab、制表、回车、换行字符串等
            $url = trim($url);
            //------------------- 移除字符串两边的空格和不可见字符串 end-------------------------











            //--------------------黑名单关键词判断 begin-------------------------
            $black_num = 0;//黑名单状态码
            $black_url_array = Config::get('app.black_url_array');
            for($x=0;$x<count($black_url_array);$x++){
                if(preg_match_all('#'.$black_url_array[$x].'#i',$url)){
                    //echo $black_url_array[$x];
                    $black_num = 1;
                    break;//跳出当前循环
                }
            }




            //----------  如果url长度小于3或者url中为匹配到.，注意：url中不能有空格   || preg_match("/ /",$url) -----------------
            if(strlen($url) < 3  || strlen($url) > 10000 || !preg_match("/\./",$url) || $url == "" || $black_num == 1){
                
                
                $json_array = [
                    "code" => 1 ,
                    "shorturl" => "",
                    "message" => "There was a problem with the short URL provided or malicious URL."
                ];
    
                return json_encode($json_array);
                
            }
            //--------------------黑名单关键词判断 end -------------------------


            //获取用户UA，如果长度大于254，就只截取254的长度
            $user_agent = Request::header('User-Agent') ? Request::header('User-Agent') : "none";
            if(strlen($user_agent) > 254){
                $user_agent = substr($user_agent,0,254);
            }



            //获取自定义的site_id域名数据
            $check_site_id_data = Db::table("tp_domain")->where("itemid","=",$check_site_id)->select();
            $remote_ip = $otherclass->get_user_ip();


            
            $urlMd5Hash = Config::get("app.redis_url_catch_prefix").md5($url);
        
            //判断当前url是否已经储存在redis中，如果已储存就直接读取数据，如果没有储存就将数据插入数据库，同时也将数据插入到redis中
            if(Cache::has($urlMd5Hash)){
                $url_data = Db::table("tp_shortener")->where("itemid",Cache::get($urlMd5Hash))->select();
                $short_url = $url_data[0]['short_url'];

            }else{



                //----------------------------   统计当天的short_url生成数量   Begin  ----------------------------------------------
                //设置点击数统计key:redis_prefix-shortener-clicks-
                $redis_key = Config::get("app.redis_prefix")."_shortener_short_url_".date("Y-m-d", time());


                //当redis数据库中存在此键值时，直接读取此键值的数据，否则就新建一个
                if(Cache::has($redis_key)){
                    
                    $total_value = Cache::get($redis_key);
                    //点击数自增1
                    $total_value  += 1;

                    Cache::set($redis_key, $total_value,60*60*24*Config::get("app.clicks_and_short_url_analysis_days"));

                }else{
                    //如果数据库中没有此键值就将此值重置为0
                    
                    Cache::set($redis_key, 1, 60*60*24*Config::get("app.clicks_and_short_url_analysis_days"));
                }

                //----------------------------   统计当天的short_url生成数量   End  ----------------------------------------------

                //三层跳转
                $short_url = "";
                $short_url_7 = "";
                $short_url_8 = "";

        
                while(true){
                    $short_url = $otherclass->getShortUrlStr();
                    $short_url_7 = $otherclass->getShortUrlStr(7);
                    $short_url_8 = $otherclass->getShortUrlStr(8);


                    //echo "short:".$short_url_7." ".$short_url_8;
                    //如果都不为空，则跳出当前循环
                    if($short_url !="" && $short_url_7 != "" && $short_url_8 !=""){
                        break;
                    }

                }

                //判断是否是PC端
                
                //---------------- 判断当前是否是pc端  begin ----------------
                if(!preg_match("/".Config::get("app.mobile_user_agent")."/i", $user_agent)){
                    $is_pc = 1;
                }else{
                    $is_pc = 0;
                }

                //---------------- 判断当前是否是pc端  end ----------------

                $accept_language = Request::header('accept-language') ? substr(Request::header('accept-language'), 0 ,100) : "none";


                //get country name
                $country = $otherclass->get_country($remote_ip);


                $data = [
                    'site_id' => $check_site_id,
                    'user_name' => $token,
                    'access_url'  => Request::host(),
                    'short_url' => $short_url,
                    'short_url_7' => $short_url_7,
                    'short_url_8' => $short_url_8,
                    'url' => $url,
                    'short_from' => 3,  //1为首页 ，2为批量添加页面， 3为api接口
                    'remote_ip' => $remote_ip,
                    'timestamp' => time(),
                    'user_agent' => $user_agent,
                    'country' => $country,
                    'accept_language' => $accept_language,
                    'is_pc' => $is_pc
                    
                ];

                

                $insertItemid = Db::table('tp_shortener')->strict(false)->insertGetId($data);

            
                //设置redis
                if(Cache::set($short_url, $insertItemid) && Cache::set($short_url_7, $insertItemid) && Cache::set($short_url_8, $insertItemid)){
                    Db::table("tp_shortener")->where("itemid",$insertItemid)->update(["redis_index" => 1]);
                    //将url-md5($url)储存至redis
                    Cache::set($urlMd5Hash, $insertItemid);

                }
            }


            //$main_domain_url = $check_site_id_data[0]['http_prefix'].$check_site_id_data[0]['domain_url']."/";
            $main_domain_url = $check_site_id_data[0]['http_prefix'].$check_site_id_data[0]['domain_url']."/";
            $short_url = $main_domain_url.$short_url;




            $json_array = [
                "code" => 0 ,
                "shorturl" => $short_url,
                "message" => "Shortened successfully"
            ];

            return json_encode($json_array);



            
        }else{
            $otherclass = new Otherclass($this->app);
            $host_data = $otherclass->getHostData(Request::host());


            //首页随机验证码,验证码的核心逻辑就是客户端与服务端的md5(ua+"-"+timestamp)进行验证,index首页使用timestamp进行伪装
            $index_timestamp = time();
            $hash_str = md5(Request::header('user-agent')."-".$index_timestamp);
            Cache::set(Config::get("app.redis_prefix")."index_hash_str".$hash_str, $value=1, $overtime=60*30);
            View::assign("index_timestamp",$index_timestamp);


            // 首页hash验证 end


            $title = "API - shortener";
            $keywords = "Shortener Api";
            $description = "Shortener Api";

            $domain_url = "https://m5.gs/";



            View::assign("title", $title);
            View::assign("keywords", $keywords);
            View::assign("description", $description);
            View::assign("year_num", Config::get("app.year_num"));
            View::assign("domain_url", $domain_url);
        
            return View::fetch("/Template_".$host_data[0]['template_num']."/ShortenerApi/index");
        }
    }

}
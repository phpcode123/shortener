<?php
namespace app\index\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Cache;




class Shortener  extends BaseController
{

    public function index(){
        if(Request::isPost()){
            //获取post param参数
            $url = Request::param("url");
            $check_site_id = Request::param("check_site_id") ? Request::param("check_site_id") : 1;


            $otherclass = new Otherclass($this->app);
            $remote_ip = $otherclass->get_user_ip();
            $country = $otherclass->get_country($remote_ip);



            //黑名单国家-----------begin
            $black_country_arr = explode("|",Config::get("app.black_country"));

            if(in_array($country,$black_country_arr)){
                $this->error("Server error, Please try again later.",'/',5);
            }
            //黑名单国家-----------end


            // if($country == "South Korea"){
            //     //记录日志
            //     $this->log($remote_ip." is_pc:{$is_pc} - {$country} - ".$url." - URL banned -- South Korea country.","error_url_u9s.log");

            //     $this->error("URL banned!",'/',5);
            // }

            //获取用户UA，如果长度大于254，就只截取254的长度
            $user_agent = Request::header('User-Agent') ? Request::header('User-Agent') : "none";
            $accept_language = Request::header('accept-language') ? substr(Request::header('accept-language'), 0 ,100) : "none";

            if(strlen($user_agent) > 254){
                $user_agent = substr($user_agent,0,254);
            }

            //---------------- 判断当前是否是pc端  begin ----------------
            if(!preg_match("/".Config::get("app.mobile_user_agent")."/i", $user_agent)){
                $is_pc = 1;
            }else{
                $is_pc = 0;
            }

            //---------------- 判断当前是否是pc端  end ----------------



            // ---------------  时间戳验证码验证 begin  ------------------------------
            $hash_str = Request::param("hash_str") ? Request::param("hash_str") : "";
            if(!Cache::has(Config::get("app.redis_prefix")."index_hash_str".$hash_str) || $hash_str == ""){
                $this->error("Please try again, Error code 1.",'/',5);
            }

            // ---------------  时间戳验证码验证 end  ------------------------------

            //判断是否是正常url
            if(!$otherclass->is_url($url)){
                
                //记录日志
                $this->log($remote_ip." is_pc:{$is_pc} - {$country} - ".substr($url,0,255)." - URL error.","error_url_u9s.log");

                $this->error("URL error, Please check it.",'/',5);
            }

            //给url增加一个http标签 如果没有http标识的url，最好是加http标签
            if(preg_match('~^([a-zA-Z0-9+!*(),;?&=$_.-]+(:[a-zA-Z0-9+!*(),;?&=$_.-]+)?@)?([a-zA-Z0-9\-\.]*)\.(([a-zA-Z]{2,4})|([0-9]{1,3}\.([0-9]{1,3})\.([0-9]{1,3})))(:[0-9]{2,5})?(/([a-zA-Z0-9+$_%-]\.?)+)*/?(\?[a-z+&\$_.-][a-zA-Z0-9;:@&%=+/$_.-]*)?(#[a-z_.-][a-zA-Z0-9+$%_.-]*)?~', $url) && !preg_match('(http://|https://)', $url)){
                $url = "http://$url";
            }
            //--验证码验证 end



            //----------  如果url长度小于3或者url中为匹配到.，注意：url中不能有空格   || preg_match("/ /",$url) -----------------
            if(strlen($url) < 3  || !preg_match("/\./",$url) ){

                //记录日志
                $this->log($remote_ip." is_pc:{$is_pc} - {$country} - ".$url." - URL error.","error_url_u9s.log");

                $this->error("URL error, Please check it.",'/',5);
            }

            //------------------- 移除字符串两边的空格和不可见字符串 begin-------------------------
            //会自动移除不可见的空白字符，如空格、tab、制表、回车、换行字符串等
            $url = trim($url);
            //------------------- 移除字符串两边的空格和不可见字符串 end-------------------------



            //--------------------黑名单关键词判断 begin-------------------------
            $black_num = 0;//黑名单状态码
            $black_url_array = Db::table("tp_black_url")->where("success",0)->select();
            for($x=0;$x<count($black_url_array);$x++){
                if(preg_match_all('#'.$black_url_array[$x]['pattern'].'#i',$url)){
                    
                    $black_num = 1;
                    break;//跳出当前循环
                }
            }


            if($black_num == 1){
                //记录日志
                $this->log($remote_ip." is_pc:{$is_pc} - {$country} - ".$url." - URL banned.","error_url_u9s.log");

                //$this->error("URL banned!",'/',5);
                //如果在黑名单中，跳转到支付提示页面，让支付金额
                return redirect("/pay/index?url=".str_rot13(base64_encode($url)));
            }

            //--------------------黑名单关键词判断 end -------------------------






            //---------------------- 防止前端用户恶意生成url 根据ua和客户端语言来判断 begin --------------------------
            //2小时内如果超过60次，就直接提示恶意使用
            $ua_al_key = Config::get("app.redis_url_catch_prefix")."-index-verification-".md5($user_agent.$accept_language);
            if(!Cache::has($ua_al_key)){
                Cache::set($ua_al_key, 1 , 60*120);
            }else{
                $ua_al_key_value = Cache::get($ua_al_key);
                if($ua_al_key_value > 60){

                    //记录日志
                    $this->log($remote_ip." is_pc:{$is_pc} - {$country} - ".$url." - index-verification-error.","error_url_u9s.log");

                    $this->error("Do not use it maliciously",'/',5);
                }else{
                    Cache::set($ua_al_key, $ua_al_key_value+1 , 60*120);
                }
            }

            //---------------------- 防止前端用户恶意生成url 根据ua和客户端语言来判断 end --------------------------














            //获取自定义的site_id域名数据
            $check_site_id_data = Db::table("tp_domain")->where("itemid","=",$check_site_id)->select();

            //校验is_customize是否为0，为0则说明当前url非自定义url
            if($check_site_id_data[0]['is_customize'] == 0){

                //记录日志
                $this->log($remote_ip." is_pc:{$is_pc} - {$country} - ".$url." - is not allow customize.","error_url_u9s.log");

                $this->error("Customize URL error.",'/',5);
            }

            //过滤url长度，如果长度大于10000就直接报错，避免客户端向数据库中灌垃圾数据
            if(strlen($url) > 10000){
                //记录日志
                $this->log($remote_ip." is_pc:{$is_pc} - {$country} - ".substr($url,0,255)." - url max length over 10000.","error_url_u9s.log");

                $this->error("URL length over limit",'/',5);
            }

            

            
            //获取当前url的参数
            
            $host_data = $otherclass->getHostData(Request::host());

            //var_dump($host_data);
            $site_id = $host_data[0]['itemid'];
            $title = $host_data[0]['index_title'];
            $keywords = $host_data[0]['index_keyword'];
            $description = $host_data[0]['index_description'];
            


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

                
                $data = [
                    'site_id' => $check_site_id,
                    'access_url'  => Request::host(),
                    'short_url' => $short_url,
                    'short_url_7' => $short_url_7,
                    'short_url_8' => $short_url_8,
                    'url' => $url,
                    'short_from' => 1,  //1为首页 ，2为批量添加页面， 3为api接口
                    'remote_ip' => $remote_ip,
                    'country' => $country,
                    'timestamp' => time(),
                    'user_agent' => $user_agent,
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



            //----------------- 设置cookies Begin ------------------------------------ 
            $cookie_name = Config::get("app.redis_prefix")."_shortener_short_url";

            if(Cookie::has($cookie_name)){
                $cookie_value = Cookie::get($cookie_name);

                //将新生成的短链拼接到cookie_Str里，并且储存新的cookie值
                $new_cookie_value = $short_url.",".$cookie_value;
                Cookie::forever($cookie_name,$new_cookie_value);
            }else{
                $cookie_value = $short_url;
                //将新生成的短链拼接到cookie_Str里，并且储存新的cookie值
                $new_cookie_value = $short_url;
                Cookie::forever($cookie_name,$new_cookie_value);
            }
            //-----------------  设置cookies End  ------------------------------------ 




            //$main_domain_url = $check_site_id_data[0]['http_prefix'].$check_site_id_data[0]['domain_url']."/";
            $main_domain_url = $check_site_id_data[0]['http_prefix'].$check_site_id_data[0]['domain_url']."/";



            $short_url = $main_domain_url.$short_url;








            
            //给url自动补全https://   //此功能禁用，否则会出现异常
            $long_url = $url;
            // if(!preg_match("/http/i",$url)){
            //     $long_url = "https://".$url;
            // }

            //获取当前访问的url
            $domain_url = $host_data[0]['http_prefix'].Request::host()."/";

            View::assign("domain_url", $domain_url);
            View::assign("short_url", $short_url);
            View::assign("long_url", $long_url);
            View::assign("title", $title);
            View::assign("keywords", $keywords);
            View::assign("description", $description);
            View::assign("year_num", Config::get("app.year_num"));
        
            return View::fetch("/Template_".$host_data[0]['template_num']."/Shortener/index");

            
        }else{
            abort(404,"Only post");
        }
    }

}
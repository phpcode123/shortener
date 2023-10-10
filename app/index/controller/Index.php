<?php
namespace app\index\controller;


use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Cache;


class Index extends BaseController
{

    public function index(){
        $otherclass = new Otherclass($this->app);

        $host_data = $otherclass->getHostData(Request::host());


        $title = $host_data[0]['index_title'];
        $keywords = $host_data[0]['index_keyword'];
        $description = $host_data[0]['index_description'];
        
        if(Config::get("app.server_upgrade_status") == 1){
            return  Config::get("app.server_upgrade_tips");
            
        }

        $domain_url = $host_data[0]['http_prefix'].Request::host()."/";



        //-----  如果roblox_index值为1则加载 roblox的首页模板用于展示adsense广告 begin----
        if($host_data[0]['roblox_index'] == 1){

            $adsense_host_data = $otherclass->get_adsense_host_data(Request::host());
            View::assign("adsense_host_data",$adsense_host_data); 


            return View::fetch("/Template_".$host_data[0]['template_num']."/Roblox/roblox_index");
        }
        //-----  如果roblox_index值为1则加载 roblox的首页模板用于展示adsense广告 end----



        //首页用户自定义url的数据读取
        $customize_data = Db::table("tp_domain")->where("is_customize",">","0")->order("is_customize","desc")->select();




        //----------------------------   获取总点击数统计   Begin  ----------------------------------------------
        //设置点击数统计key:redis_prefix-shortener-clicks-
        $total_redis_key = Config::get("app.redis_prefix")."_total_clicks";
        
        //echo $redis_key;
        //当redis数据库中存在此键值时，直接读取此键值的数据，否则就新建一个
        if(Cache::has($total_redis_key)){
            $total_clicks_value = Cache::get($total_redis_key);

        }else{
            $total_clicks_value = 1;
        }
        $total_clicks = Config::get("app.index_total_clicks_base_num")+$total_clicks_value;
        //----------------------------   获取总的点击数统计   End  ----------------------------------------------
        



        //----------------------------   获取总的链接生成统计   Begin  ----------------------------------------------
        $total_links_data = Db::table("tp_shortener")->order("itemid","desc")->limit(1)->select();

        $total_links = Config::get("app.index_total_links_base_num")+$total_links_data[0]['itemid'];
        //----------------------------   获取总的链接生成统计   End  ----------------------------------------------






        //----------------------------   统计当天的生成数量 此只为设置首页上的漂亮数字   Begin  ----------------------------------------------
        //设置点击数统计key:redis_prefix-shortener-clicks-
        $links_today_key = Config::get("app.redis_prefix")."_links_today_".date("Y-m-d", time());


        //当redis数据库中存在此键值时，直接读取此键值的数据，否则就新建一个
        if(Cache::has($links_today_key)){
            
            $links_today_value = Cache::get($links_today_key);

        }else{
            //如果数据库中没有此键值就将此值重置为0
            $links_today_value = mt_rand(Config::get("app.index_today_links_rand_num")[0], Config::get("app.index_today_links_rand_num")[1]);
            
            Cache::set($links_today_key, $links_today_value, 60*60*24*Config::get("app.clicks_and_short_url_analysis_days"));
        }
        //echo $links_today_value;
        //获取当前生成的url数量，并和随机生成的值相加
        $links_today = $links_today_value + Cache::get(Config::get("app.redis_prefix")."_shortener_short_url_".date("Y-m-d", time()));
        //----------------------------   统计当天的生成数量 此只为设置首页上的漂亮数字    End  ----------------------------------------------



        //读取cookie
        //----------------- 读取cookies Begin ------------------------------------ 
        $cookie_name = Config::get("app.redis_prefix")."_shortener_short_url";

        if(Cookie::has($cookie_name)){
            $cookie_value = Cookie::get($cookie_name);

        }else{
            $cookie_value = 0;
        }
        
        //从数据库中读取数据
        $cookie_data = Db::table("tp_shortener")->where("short_url","in",$cookie_value)->order("itemid","desc")->select();
        $cookie_data_count_num = count($cookie_data);

        //统计cookie_data中的点击数量
        $cookie_data_total_clicks = 0;
        if($cookie_data_count_num > 2){
            for($i=0; $i < $cookie_data_count_num; $i++){
                $cookie_data_total_clicks += $cookie_data[$i]['hits'];
            }
        }

        View::assign("cookie_data_total_clicks", $cookie_data_total_clicks);
        
        //-----------------  读取cookies End  ------------------------------------ 




        //直接在首页上展示short_url是下策，最好是生成一个sitemap文件，提交给google
        // //--------------------------------判断是否是蜘蛛或者爬虫，如果是就直接跳转到目标站点 begin    --------------------------------
        // //获取用户UA，如果长度大于254，就只截取254的长度
        // $user_agent = Request::header('User-Agent') ? Request::header('User-Agent') : "none";
        // if(strlen($user_agent) > 254){
        //     $user_agent = substr($user_agent,0,254);
        // }


        // $spider_status = $otherclass->getSpiderStatus($user_agent);
        // //echo $spider_status;
        // $index_short_url_data = array();
        // if($spider_status == 1 ){
        //     $index_short_url_data = Db::table("tp_shortener")->order("itemid","desc")->limit(30);
        // }
        // View::assign("index_short_url_data",$index_short_url_data);
        // View::assign("spider_status",$spider_status);
        // //--------------------------------判断是否是蜘蛛或者爬虫，如果是就直接跳转到目标站点 end    --------------------------------



        //首页随机验证码,验证码的核心逻辑就是客户端与服务端的md5(ua+"-"+timestamp)进行验证,index首页使用timestamp进行伪装
        $index_timestamp = time();
        $hash_str = md5(Request::header('user-agent')."-".$index_timestamp);
        Cache::set(Config::get("app.redis_prefix")."index_hash_str".$hash_str, $value=1, $overtime=60*30);
        View::assign("index_timestamp",$index_timestamp);


        // 首页hash验证 end





        
        $domain_data = Db::table("tp_domain")->order("itemid","asc")->select();
        View::assign("domain_data", $domain_data);



        View::assign("total_clicks",$total_clicks);
        View::assign("total_links",$total_links);
        View::assign("links_today",$links_today);
        View::assign("index_display_user_cookies_data",Config::get("app.index_display_user_cookies_data"));

        View::assign("cookie_data",$cookie_data);
        View::assign("cookie_data_count_num",$cookie_data_count_num);

        View::assign("domain_url", $domain_url);
        View::assign("title", $title);
        View::assign("keywords", $keywords);
        View::assign("description", $description);
        View::assign("year_num", Config::get("app.year_num"));
        View::assign("customize_data", $customize_data);
       


        if($host_data[0]['itemid'] == 14 || $host_data[0]['itemid'] == 7){
            return View::fetch("/Template_".$host_data[0]['template_num']."/Index/new_domain");
        }else{
            return View::fetch("/Template_".$host_data[0]['template_num']."/Index/index");
        }
        

    }



    public function shorten(){
        $url = Request::param("url");
        $check_site_id = Request::param("check_site_id") ? Request::param("check_site_id") : 1;

        $check_site_id_data = Db::table("tp_domain")->where("itemid","=",$check_site_id)->select();



        if(strlen($url) > 10000){
            $json_data_array = array(
                "error" => 1,
                "msg" => "Error,Url length exceeds limit !"
            ); 
    
            return json_encode($json_data_array);
        }

        $remote_ip = $_SERVER['REMOTE_ADDR'];
        
        $otherclass = new Otherclass($this->app);
        $host_data = $otherclass->getHostData(Request::host());
        $site_id = $host_data[0]['itemid'];


        $urlMd5Hash = Config::get("app.redis_url_catch_prefix").md5($url);
       
        
        if(Cache::has($urlMd5Hash)){
            $url_data = Db::table("tp_shortener")->where("itemid",Cache::get($urlMd5Hash))->select();
            
            $short_url = $url_data[0]['short_url'];

            
            $error_num = 0;

        }else{

            $short_url = $otherclass->getShortUrlStr();

            $data = [
                'site_id' => $site_id,
                'short_url' => $short_url,
                'url' => $url,
                'remote_ip' => $remote_ip,
                'timestamp' => time()
            ];

            $insertItemid = Db::table('tp_shortener')->strict(false)->insertGetId($data);

        
            //设置redis
            if(Cache::set($short_url, $insertItemid)){
                Db::table("tp_shortener")->where("itemid",$insertItemid)->update(["redis_index" => 1]);

                //将url-md5($url)储存至redis
                Cache::set($urlMd5Hash, $insertItemid);
                $error_num = 0;

            }else{
                $error_num = 1;    
            }
        }

        $main_domain_url = $check_site_id_data[0]['http_prefix'].$check_site_id_data[0]['domain_url']."/";


        $json_data_array = array(
            "error" => $error_num,
            "short" => $main_domain_url.$short_url
        ); 

        return json_encode($json_data_array);
    }






}
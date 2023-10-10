<?php
namespace app\index\controller;


use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Cookie;


class Gotourl  extends BaseController
{
    public function index(){
        $otherclass = new Otherclass($this->app);
        $short_url = Request::param("short_str");

        $host_data = $otherclass->getHostData(Request::host());
        $http_referer = $_SERVER['HTTP_REFERER'] ?? "None";






        //获取用户UA，如果长度大于254，就只截取254的长度
        $user_agent = Request::header('User-Agent') ?? "none";
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

        //--------------- 判断当前是否为蜘蛛  begin ---------------
        $spider_status = $otherclass->getSpiderStatus($user_agent);
        //--------------- 判断当前是否为蜘蛛  end ---------------

        if(Cache::has($short_url)){
            $redisValueItemid = Cache::get($short_url);
            $data = Db::table("tp_shortener")->where("itemid",$redisValueItemid)->select();
        
            //当$data的长度为0时说明在数据库中没有匹配到该数据，跳转首页
            if(count($data) == 0){
                $this->error("URL ERROR！",$host_data[0]['http_prefix'].$host_data[0]['domain_url'],5);
            }


            //-------------------------- is_404 begin -----------------------------
            //9为恶意网址，系脚本自动设置
            if($data[0]['is_404'] == 9){
                $this->error("Malicious URL!",$host_data[0]['http_prefix'].$host_data[0]['domain_url'],5);
            }

            //8为用于钓鱼，手动处理
            if($data[0]['is_404'] == 8){
                $this->error("Phishing, URL banned!",$host_data[0]['http_prefix'].$host_data[0]['domain_url'],5);
            }


            //如果is_404大于0时，直接返回404页面
            //is_404状态码：1为自己手动设置
            if($data[0]['is_404'] > 0){
                abort(404,"is_404");
            }
            //-------------------------- is_404 end -----------------------------
        }else{
            $this->error("URL ERROR！",$host_data[0]['http_prefix'].$host_data[0]['domain_url'],5);
        }





        //---------------------- adsense domain  begin ----------------------
        //自动跳转google adsense中间页 
        //从数据库读取数据
        if(Config::get("app.google_adsense_middle_page_switch") == 1 && $spider_status == 0){
            $adsense_data = Db::table("tp_adsense")->where("adsense_switch","1")->order("itemid","asc")->select();
            
            //随机数据下标获取数据
            try{
                $adsense_rand_num = mt_rand(0,count($adsense_data)-1);
                $adsense_domain = $adsense_data[$adsense_rand_num]['adsense_domain'];
                //将随机获取的当前adsense_code的数据注册到模板中，调用广告
                
                View::assign("adsense_code", $adsense_data[$adsense_rand_num]['adsense_code']);
            }catch(\Exception $e){
                //当逻辑出现错误时，在app.php中调用备用配置
                $adsense_domain = Config::get("app.google_adsense_middle_url");
            }


            if(Config::get("app.google_adsense_middle_page_switch") == 1 && Request::host() != $adsense_domain){
                //直接跳转到$rand_site_itemid
                return redirect("https://".$adsense_domain."/".$short_url, 301);
                
            }
        }
        //---------------------- adsense domain  end ----------------------



        //--------------------  三层跳转 begin ------------------
        //如果开启了adsense广告就处理三层跳转, 只是移动端才会有三重跳转
        if(Config::get("app.google_adsense_middle_page_switch") == 1){

            if(Cache::has($short_url)){
                $redisValueItemid = Cache::get($short_url);

                //上面有请求过一次$data，这个地方不再请求数据库，省cpu资源
                if(!isset($data)){
                    $data = Db::table("tp_shortener")->where("itemid",$redisValueItemid)->select();
                }
            
                //当$data的长度为0时说明在数据库中没有匹配到该数据，跳转首页
                if(count($data) == 0){
                    $this->error("URL ERROR！",$host_data[0]['http_prefix'].$host_data[0]['domain_url'],5);
                }
            

                //只有当点击量大于设定值时才跳转
                //if($data[0]['hits'] > Config::get("app.goto_url_auto_jump_min_hits")){


                    //判断youtube_url_itemid是否为空，为空则自动更新一个
                    if($data[0]['youtube_url_itemid'] == 0){
                        $youtube_url_max_data_itemid= $this->update_youtube_url_itemid($data);
                       
                    }else{
                        $youtube_url_max_data_itemid = $data[0]['youtube_url_itemid'];
                    }




                    //----------  注册youtube_data  begin -------------------
                    $youtube_url_data = Db::table("tp_youtube_url")->where("itemid",$youtube_url_max_data_itemid)->select();
                    if(count($youtube_url_data) == 0){
                        abort(404,"itemid data less than 1");
                    }else{
                        $youtube_title = $youtube_url_data[0]['title'];
                        $youtube_keyword = $youtube_url_data[0]['keyword'];
                        $youtube_description = $youtube_url_data[0]['description'];
                        $youtube_url = $youtube_url_data[0]['youtube_url'];
                        $youtube_short_url = $youtube_url_data[0]['youtube_short_url'];


                        view::assign("youtube_title",$youtube_title);
                        view::assign("youtube_description",$youtube_description);
                        view::assign("youtube_keyword",$youtube_keyword);
                        view::assign("youtube_url",$youtube_url);
                        view::assign("youtube_short_url",$youtube_short_url);


                        $adsense_host_data = $otherclass->get_adsense_host_data(Request::host());
                        view::assign("adsense_host_data",$adsense_host_data);
                        


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
                    }



                    //----------  注册youtube_data  end -------------------

                    //http_referer的原理就是
                    //三层跳转原理逻辑，从6位的入口short_url 跳转到short_url_7、然后跳转到short_url_8
                    //其中三个都要判断referfer来源，如果其中一个环节断了就直接跳转到adsense_url
                    /*
                    *注意，redis缓存会储存4个值
                    *url-md5(url)   //用户要储存的url，主要用于用户生成url去重
                    *short_str,itemid     //6位short_str
                    *short_str_7,itemid   //7位short_str
                    *short_str_8,itemid   //8位short_str 
                    *如果展示adsense广告，short_str_8是最终展示广告页面
                    */
                    //

                
                    //三层跳转判断referer来源   直接使用redirect()重定向，来源为最初的那个url
                    
                    if(strlen($short_url) == 6){
                        //当为pc端时，可以自动跳转
                        if(Config::get("app.is_pc_visit_direct_jump") == 1 && $is_pc ==1 && $spider_status == 0){
                            //不执行当前的流程，当前流程不执行就会进入到后面的步骤，不会执行short_url_6和short_url_7
                        }else{
                            if(isset($adsense_domain)){
                                $redirect_url = $host_data[0]['http_prefix'].$adsense_domain."/".$data[0]["short_url_7"];
                            }else{
                                $redirect_url = $host_data[0]['http_prefix'].$host_data[0]['domain_url']."/".$data[0]["short_url_7"];
                            }
                            //使用页面跳转
                            View::assign("redirect_url",$redirect_url);
                            return View::fetch("/Template_".$host_data[0]['template_num']."/Gotourl/http_referer_3_jump");
                        }
                    }

                    //必须要匹配到是从short_url跳转而来,如果不是就直接跳转到adsense_url
                    if(strlen($short_url) == 7){
                        if(preg_match("#".$data[0]['short_url']."#i",$http_referer,$matches_)){
                            
                            
                            if(isset($adsense_domain)){
                                $redirect_url = $host_data[0]['http_prefix'].$adsense_domain."/".$data[0]["short_url_8"];
                            }else{
                                $redirect_url = $host_data[0]['http_prefix'].$host_data[0]['domain_url']."/".$data[0]["short_url_8"];
                            }

                            //使用页面跳转
                            View::assign("redirect_url",$redirect_url);
                            return View::fetch("/Template_".$host_data[0]['template_num']."/Gotourl/http_referer_3_jump");
                        }else{
                        
                            //return redirect($data[0]['adsense_url']);
                            return View::fetch("/Template_".$host_data[0]['template_num']."/Roblox/roblox_show");
                        }
                    }
        

                    
                    
                    //必须要匹配到是从short_url跳转而来,如果不是就直接跳转到adsense_url
                    if(strlen($short_url) == 8){
                        //如果来源非short_url_7 则直接跳转到adsense_url中
                        if(!preg_match("#".$data[0]['short_url_7']."#i",$http_referer,$matches_)){
                            

                            return View::fetch("/Template_".$host_data[0]['template_num']."/Roblox/roblox_show");
                            
                        }
                    }

 
                //}  
            }
        }
        //--------------------  三层跳转 end --------------------



    
        //$host_data上面有调用一次
        //$host_data = $otherclass->getHostData(Request::host());
        if(Cache::has($short_url)){
            $redisValueItemid = Cache::get($short_url);

            $data = Db::table("tp_shortener")->where("itemid",$redisValueItemid)->select();
            //当$data的长度为0时说明在数据库中没有匹配到该数据，跳转首页
            if(count($data) == 0){
                $this->error("URL ERROR！",$host_data[0]['http_prefix'].$host_data[0]['domain_url'],5);
            }

            //var_dump($data);

            // -----------------       更新点击数 begin  ------------------------
            //更新点击数和最后访问时间戳,点击数增加和最后访问时间以user_agent.short_url来判断，避免客户端刷点击,超时时间半小时  begin
            //一个用户在访问一次后就不会再统计点击数，根据redis中的hash key缓存来的，hash key用于识别用户身份
            $hits_add_key = Config::get("app.redis_prefix")."_hits_add_".md5($user_agent)."_".$short_url;
            if(!Cache::has($hits_add_key)){
                Cache::set($hits_add_key, 1, 60*30);
                Db::table("tp_shortener")->where("itemid",$redisValueItemid)->inc('hits')->update();
                Db::table("tp_shortener")->where("itemid",$redisValueItemid)->update(['last_access_timestamp'=>time()]);
            }





            //用户的访问来源都记录下，用于识别出google adsense蜘蛛，可以将此段代码放在上面的if判断中，如果是同一个用户就只记录一次来源记录
            //---------------------- insert into http_referer begin -------------------------------

            //部分蜘蛛没有 Undefined index: HTTP_ACCEPT_LANGUAGE
            $SERVER_HTTP_ACCEPT_LANGUAGE = $_SERVER["HTTP_ACCEPT_LANGUAGE"] ?? "none";
            if($SERVER_HTTP_ACCEPT_LANGUAGE != "none"){
                try{    
                    $user_language = explode(",", $SERVER_HTTP_ACCEPT_LANGUAGE)[0];
                }catch(\Exception $e){
                    $user_language = "none";
                }
            }else{
                $user_language = "none";
            }
            
            
            //get user ip
            $remote_ip = $otherclass->get_user_ip();
            //get country name
            
            $country = $otherclass->get_country($remote_ip);


        
            $http_referer_data = [
                'short_str'      => $short_url ? $short_url : "null",
                'http_referer'   => Request::server("HTTP_REFERER") ?? "null",
                'user_agent'     => $user_agent,
                'is_pc'          => $is_pc,
                'is_spider'      => $spider_status,
                'user_language'  => $user_language,
                'remote_ip'      => $remote_ip,
                'country'        => $country,
                'timestamp'      => time()
            ];

            Db::table("tp_http_referer")->strict(false)->insert($http_referer_data);
            //---------------------- insert into http_referer end  -------------------------------
        
        
            //-----------------       更新点击数 end  ------------------------



            //如果adsense_switch为11和匹配到当前用户为209.190开头的IP，则跳转到指定adsense_url地址上
            //adsense报警 "抓取工具：托管服务器出错"，是因为很多url只能针对本国地区的访问，adsense服务器IP在美国，不能访问。建议最好是选择google play类的url，adsense的美国服务器可以访问。
            //preg_match_all("/^209\.190/i", $remote_ip)   // google adsense spider ip
            //preg_match_all("/^138\.19/i", $remote_ip)   //my ip
            //66.249.89.x    Mediapartners-Google
            //66.249.66.130  Google spider
            //ias-or/3.1 (+https://www.admantx.com/service-fetcher.html)  是数字广告爬虫 增加一个判断策略
            //新增Mediapartners|Google UA识别  --20230207
            //Mozilla/5.0 (compatible; proximic; +https://www.comscore.com/Web-Crawler)  广告分析数字平台的爬虫，美国ip

            //$country == "United States" && $user_language == "none" && $spider_status == 0
            //有部分google蜘蛛伪装成普通的用户，但是蜘蛛有特征，如地区为美国地区，user_language为none，spider_status为0(即非蜘蛛UA)  则跳转到指定的URL
            //dlltbF ias-ir/3.1 (+https://www.admantx.com/service-fetcher.html)	1	0	none	2a05:d018:e54:f401:d2d9:e9d0:df54:aa7b	Ireland	2023-02-15 19:29:47  
            //上述是最新ua admantx.com出现了ip国家为Ireland的地区
            //dataminr风险实时监控的AI爬虫(美国地区)
            $adsense_ua_country_array = ["United States","Ireland"];
            if((preg_match_all("/^209\.190|^66\.249/i", $remote_ip) || preg_match_all("#admantx|Mediapartners|Google|comscore\.com|proximic|TTD-Content|thetradedesk|TrendsmapResolver|dataminr|CFNetwork#i",$user_agent) ||  (in_array($country,$adsense_ua_country_array) && $user_language == "none" && $spider_status == 0)  )){

                return View::fetch("/Template_".$host_data[0]['template_num']."/Roblox/roblox_show");

            }








            //--------------------------------判断是否是蜘蛛或者爬虫，如果是就直接跳转到目标站点 begin    --------------------------------
            
            
            if($spider_status == 1){

                //增加一个允许蜘蛛自动跳转的控制开关，避免后期有高访问量的违规url不能删除时，而google ad或google蜘蛛又提示违规
                if($data[0]['allow_spider_jump'] == 1){
                    return redirect($data[0]['url'], 301);
                }else{  
                    abort(404,"蜘蛛访问404");
     
                }
            }
            //--------------------------------判断是否是蜘蛛或者爬虫，如果是就直接跳转到目标站点 end    --------------------------------




            //如果当前short_url的status状态值为1，则直接跳转到错误提示页，先显示广告，广告显示完成后再跳转到错误提示页
            if($data[0]['status'] == 0){
                $redirect_url = $host_data[0]['http_prefix'].$host_data[0]['domain_url']."/error-page";
                //$this->error("URL blocked!",$host_data[0]['http_prefix'].$host_data[0]['domain_url'],5);
            }else{
                //echo $data[0]['url'];
                if(!preg_match("/http/i",$data[0]['url'])){
                    $redirect_url = "http://".$data[0]['url'];
                }else{
                    $redirect_url = $data[0]['url'];
                }

            }



            // ----------------------  清洗URl不可见字符 begin ---------------------
            //去掉url中的空白不可见字符，不可见字符会影响页面中的js跳转执行，会导致倒计时js代码失效
            //ord()  函数是返回字符串ASCII的码值  注意还有一个ASCII拓展码 ã也是拓展码中的，部分国外的url中包含此类码
            //拓展码详见： https://www.asciim.cn/  0x20与0xFF均表示16进制
            $url_len = strlen($redirect_url);
            $new_url_str = "";
            for($i=0;$i<$url_len;$i++){
                
                if((ord($redirect_url[$i]) >= 0x20 && ord($redirect_url[$i]) <= 0xFF) ){

                    //0x22为双引号(将双引号转义即可，其它的可见字符串不冲突) 加转义防止html中的js跳转代码失效
                    if(ord($redirect_url[$i]) == 0x22){
                        $new_url_str = $new_url_str."%22";
                    }else{
                        $new_url_str = $new_url_str.$redirect_url[$i];
                    }
                            
                }
            }

            $redirect_url = $new_url_str;


            // ----------------------  清洗URl不可见字符 end ---------------------






           
            //1、根据生成者cookies跳转
            //2、根据UA跳转
            //3、根据点击数跳转
            //三种逻辑顺序不可颠倒


            //----------------- 读取cookies，如果用户是当前url的生成者，直接跳转，不对生成者展示广告 begin ----------------------------
            //----------------- 读取cookies Begin ------------------------------------ 
            $cookie_name = Config::get("app.redis_prefix")."_shortener_short_url";
            

            if(Cookie::has($cookie_name)){
                $cookie_value = Cookie::get($cookie_name);
                
            }else{
                $cookie_value = 0;
            }
            //----------------- 读取cookies end ------------------------------------ 

            if(preg_match("/".$short_url."/i", $cookie_value)){
                return redirect($redirect_url, 301);
            }
            // //----------------- 读取cookies，如果用户是当前url的生成者，直接跳转，不对生成者展示广告 end ----------------------------


  






            
            //----------------------------   设置UA访问数统计统计 如果在30分钟内的UA访问次数超过某次，就直接跳转，防止facebook和instegram的人工蜘蛛爬虫来爬  Begin  ----------------------------------------------
            //设置点击数统计key:redis_prefix-shortener-clicks-
            $ua_jump_redis_key = Config::get("app.redis_prefix")."_ua_".md5($user_agent)."_".$short_url;
        

            //当redis数据库中存在此键值时，直接读取此键值的数据，否则就新建一个
            if(Cache::has($ua_jump_redis_key)){
                
                //echo "get value success!";

                $ua_jump_num_value = Cache::get($ua_jump_redis_key);
                //点击数自增1
                $ua_jump_num_value += 1;
                Cache::set($ua_jump_redis_key, $ua_jump_num_value,1800);

                //当用户访问当前url次数超过$goto_url_min_hits就自动跳转，默认最好设置为1次，即访问一次后就跳转。
                $goto_url_min_hits = 1;

                //如果30分钟的访问次数大于$goto_url_min_hits，就直接跳转
                if($ua_jump_num_value > $goto_url_min_hits){
                    return redirect($redirect_url, 301);
                }

            }else{
                Cache::set($ua_jump_redis_key, 1);
            }

            //----------------------------   设置UA访问数统计统计 如果在30分钟内的UA访问次数超过3次，就直接跳转，防止facebook和instegram的人工蜘蛛爬虫来爬   End  ----------------------------------------------


            //------------------  当点击数小于某一个数值时，直接跳转 begin------------------------
            if($data[0]['hits'] < Config::get("app.goto_url_auto_jump_min_hits")){
                //echo "true";
                //echo $data[0]['hits'];
                //echo Config::get("app.goto_url_auto_jump_min_hits");
                return redirect($redirect_url, 301);
            }

            //------------------  当点击数小于某一个数值时，直接跳转 end------------------------

            // ----------------------   三重跳转 end --------------------------





            //----------------------------   设置当天的总点击数统计   Begin  ----------------------------------------------
            
            //设置点击数统计key:redis_prefix-shortener-clicks-
            $redis_key = Config::get("app.redis_prefix")."_shortener_clicks_".date("Y-m-d",time());
            
            //echo $redis_key;
            //当redis数据库中存在此键值时，直接读取此键值的数据，否则就新建一个
            if(Cache::has($redis_key)){
                
                //echo "get value success!";

                $clicks_value = Cache::get($redis_key);
                //点击数自增1
                $clicks_value += 1;
                Cache::set($redis_key, $clicks_value, 60*60*24*Config::get("app.clicks_and_short_url_analysis_days"));

            }else{
                //echo "get value fail!";
                //echo 60*60*24*Config::get("app.clicks_and_short_url_analysis_days");
                //如果数据库中没有此键值就将此值重置为0
                Cache::set($redis_key, 1, 60*60*24*Config::get("app.clicks_and_short_url_analysis_days"));
            }

            //----------------------------   设置当天的总点击数统计   End  ----------------------------------------------




            //----------------------------   当天PC端或M端总点击数统计   Begin  ----------------------------------------------
            if($is_pc==1){
                //设置点击数统计key:redis_prefix-shortener-clicks-
                $redis_key = Config::get("app.redis_prefix")."_shortener_pc_clicks_".date("Y-m-d",time());
            }else{
                $redis_key = Config::get("app.redis_prefix")."_shortener_m_clicks_".date("Y-m-d",time());
            }

            //echo $redis_key;
            //当redis数据库中存在此键值时，直接读取此键值的数据，否则就新建一个
            if(Cache::has($redis_key)){
                
                //echo "get value success!";

                $clicks_value = Cache::get($redis_key);
                //点击数自增1
                $clicks_value += 1;
                Cache::set($redis_key, $clicks_value, 60*60*24*Config::get("app.clicks_and_short_url_analysis_days"));

            }else{
                //echo "get value fail!";
                //echo 60*60*24*Config::get("app.clicks_and_short_url_analysis_days");
                //如果数据库中没有此键值就将此值重置为0
                Cache::set($redis_key, 1, 60*60*24*Config::get("app.clicks_and_short_url_analysis_days"));
            }

            //----------------------------   当天PC端或M端总点击数统计   End  ----------------------------------------------




            //----------------------------   设置总点击数统计   Begin  ----------------------------------------------
            //设置点击数统计key:redis_prefix-shortener-clicks-
            $total_redis_key = Config::get("app.redis_prefix")."_total_clicks";
            
            //echo $redis_key;
            //当redis数据库中存在此键值时，直接读取此键值的数据，否则就新建一个
            if(Cache::has($total_redis_key)){
                
                //echo "get value success!";

                $total_clicks_value = Cache::get($total_redis_key);
                //点击数自增1
                $total_clicks_value += 1;
                Cache::set($total_redis_key, $total_clicks_value);

            }else{
                Cache::set($total_redis_key, 1);
            }

            //----------------------------   设置总的点击数统计   End  ----------------------------------------------





            //pc和m端的自动跳转，先统计点击数，统计完点击数然后再跳转，顺序得在redis统计点击数的下面
            // ----------------------- pc m auto jump  begin-------------------------
            if(Config::get("app.is_pc_visit_direct_jump") == 1 && $is_pc == 1){
                return redirect($redirect_url, 301);
            }

            if(Config::get("app.is_m_visit_direct_jump") == 1  && $is_pc == 0){
                return redirect($redirect_url, 301);
            }
            // ----------------------- pc m auto jump  end-------------------------









            //当时间超过指定小时后就开始展示中间页广告  60*60*Config::get("app.display_ad_hour")
            if((time() - $data[0]['timestamp']) < (60*60*Config::get("app.display_ad_hour"))){

                return redirect($redirect_url, 301);
                
            }else{
                //当跳转中间页暂时时间大于0时，则显示跳转中间页，否则直接跳转
                if(Config::get("app.google_adsense_middle_page_switch") == 1){

                    //数据库中的nmiddle_page中间页项目是否为1，如果为1则说明展示中间页，0为直接跳转
                    if($data[0]['middle_page'] == 0){
                        return redirect($redirect_url, 301);
                    }else{


                        //----------------------------   当天middle_page点击数统计   Begin  ----------------------------------------------
    
                        $redis_key = Config::get("app.redis_prefix")."_shortener_middle_page_clicks_".date("Y-m-d",time());
                        
                        //echo $redis_key;
                        //当redis数据库中存在此键值时，直接读取此键值的数据，否则就新建一个
                        if(Cache::has($redis_key)){
                            
                            //echo "get value success!";

                            $clicks_value = Cache::get($redis_key);
                            //点击数自增1
                            $clicks_value += 1;
                            Cache::set($redis_key, $clicks_value, 60*60*24*Config::get("app.clicks_and_short_url_analysis_days"));

                        }else{
                            //echo "get value fail!";
                            //echo 60*60*24*Config::get("app.clicks_and_short_url_analysis_days");
                            //如果数据库中没有此键值就将此值重置为0
                            Cache::set($redis_key, 1, 60*60*24*Config::get("app.clicks_and_short_url_analysis_days"));
                        }

                        //----------------------------   当天middle_page点击数统计  End  ----------------------------------------------







                        View::assign("google_adsense_middle_page_sleep_time", Config::get("app.google_adsense_middle_page_sleep_time"));
                        View::assign("redirect_url", $redirect_url);
                        View::assign("http_prefix",$host_data[0]['http_prefix']);
                        View::assign("domain_url",$host_data[0]['domain_url']);
                        View::assign("year_num", Config::get("app.year_num"));
                        View::assign("data", $data);

                        
                        if(Config::get("app.google_adsense_middle_page_auto_jump_num") == 3){
                            $auto_jump_num = mt_rand(1,2);
                        }else{
                            $auto_jump_num = Config::get("app.google_adsense_middle_page_auto_jump_num");
                        }

                        //广告中间页是否自动跳转，1为自动跳转，2为设置值为CLICK HERE让用户手动去点击  目的是为了增加广告点击量;值为3时，一半的几率自动跳转，一半的几率手动,
                        //设置为3为了降低google adsense广告点击率，避免点击率过高导致账户被封
                        View::assign("auto_jump_num", $auto_jump_num);

                        //如果在google_adsense_middle_page_sleep_time时间后用户没有点击页面中的链接，就在..auto_jump_sleeptime自动跳转
                        View::assign("auto_jump_sleeptime", Config::get("app.google_adsense_middle_page_auto_jump_sleeptime"));
                        View::assign("gotourl_page_display_ads_switch", Config::get("app.gotourl_page_display_ads_switch"));

                        //随机加载模板1和2，两个不同的模板广告位布局不一样，为的是降低广告点击率
                        
                        //$rand_template_num = mt_rand(1,2);
                        //return View::fetch("/Template_".$host_data[0]['template_num']."/Gotourl/gotourl_".$rand_template_num);
                        return View::fetch("/Template_".$host_data[0]['template_num']."/Gotourl/gotourl_1");
                        
                    }


                }else{

                    return redirect($redirect_url, 301);
                }

                
            }

        }else{
            $this->error("URL ERROR！",$host_data[0]['http_prefix'].$host_data[0]['domain_url'],5);
        }
    }


    public function error_page(){
        $otherclass = new Otherclass($this->app);
        $host_data = $otherclass->getHostData(Request::host());

        $this->error("URL blocked!",$host_data[0]['http_prefix'].$host_data[0]['domain_url'],5);
    }


    //传入参数是tp_shortener的但个itemiddata
    public function update_youtube_url_itemid($data){
        if($data[0]['youtube_url_itemid'] == 0){
            $youtube_url_max_data = Db::table("tp_youtube_url")->order("itemid","desc")->limit(1)->select();

            if(count($youtube_url_max_data) == 0){
                abort(404,"tp_youtube_url data less 1");
            }

            $youtube_url_max_data_itemid = mt_rand(1,$youtube_url_max_data[0]['itemid']);
            //将随机生成的youtube_url_itemid更新到数据库
            try{
                Db::table("tp_shortener")->where("itemid",$data[0]['itemid'])->update([
                    "youtube_url_itemid" => $youtube_url_max_data_itemid
                ]);
            }catch(\Exception $e){
                //do something
            }

            return $youtube_url_max_data_itemid;
        }
    }

}
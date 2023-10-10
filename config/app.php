<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // +------------------------------------------------------------------
    // | 网站设置
    // +------------------------------------------------------------------
    //app_name用于后台title显示，最终会显示：{$app_name} Admin Dashboard，取名尽量好区分，避免多个应用时总是误点
    'app_name'               => 'Shortener',
    'app_version'            => "v2.01",
    //admin后台登录的账号用户名和密码
    'admin_username'         => 'admin',    //后台用户名
    'admin_password'         => 'admin888', //后台密码
    'api_password'           => 'a7sadha9', //程序api通信密码
    'install_path'           => '/www/wwwroot/shortener/',  //程序目录

    //后台相关配置项
    'admin_url'              => 'https://your_domain.com/', //后台登录网址
    'admin_path'             => 'admin.php',//后台入口文件，防止后台被爆破
    'admin_page_num'         => '50',//后台分页数量
    'email'                  => '123#gmail.com(#替换@)',

    //全站底部的年数数字
    'year_num'             => '2023',


    //服务器升级维护中 ，0为正常,1为维护
    'server_upgrade_status' => 0,
    'server_upgrade_tips'   => 'Server upgrade, please try again after half an hour...',


    //black_url //"\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}"
    //BOCAI URL
    //,"\\d+\.com/register","kdinmobiliaria\.net","/register\?ref=","url123\.click","urls\.by","goo.by","sub2unlock\.me","rich9game\.com","palatlaldate\.com","prodlgiousdates\.com","99tg\d+\.com","bwwetpo\.ws","ph\w+\.bet","/link\.php\?member=","clickbank\.net","rich9game\.com","nekobet99\.art"


    //usdt pay订单的过期时间，默认10分钟
    "usdt_address" => "YOUR_USDT_ADDRESS",
    'usdt_api'         => 'https://apilist.tronscanapi.com/api/transfer/trc20',
    'usdt_api_trc20Id' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
    'usdt_callback_url'=> '/usdt/callback',
    'usdt_pay_timeout' => 600,//付款页面的超时时间，单位秒，默认设置为10分钟




    //black_country  黑名单国家  多个国家使用|隔开
    'black_country' => "China|Morocco",

    //contact black word 联系方式黑名单关键词，避免恶意灌水,可以匹配邮箱、联系人姓名、联系内容
    'contact_black_word'   => array("rmikhail1985"," Eric ","no-repl"," Eric,","cloedcolela","Helina Aziz"," nісe mаn "," mу sistеr ","website's design","bode-roesch","battletech-newsletter","SEO","henry","forum",".dk","robot","blueliners",".de","money","mailme","mail-online","nіce mаn","pussy","fuck ","href","http","pertitfer","pouring","mаrried","automatic","@o2.pl","Cryto"," href ","contactform_","Contact us","Telegram","lupamailler"),

    //短链接字符串长度,没有特殊情况，此长度不要随意更改
    'short_url_str_length'   => 6, //生成短链接字符串的长度
  

    //redis 缓存设置
    'redis_host'             => '127.0.0.1',
    'redis_port'             =>  6379,
    //增加redis的key前缀，防止在同一台服务器上不同的程序之间的redis key重复，造成数据覆盖丢失。
    'redis_prefix'           => 'shortener_20220502', 
    //用于储存当天clicks点击数和short_url统计数的天数,单位：天， 调用类：Gotourl.php\ shortener.php
    'clicks_analysis_days'  => 10,


    //url redis去重前缀，程序在redis中要缓存两种数据：
    //1、  short_url => tp_shortener表中对应的itemid   （用于短链去重）
    //2、  url-md5(url)  =>  tp_shortener表中对应的itemid  （用于url去重，避免数据库中生成重复的url数据）
    'redis_url_catch_prefix' => 'url-',    //数据样式： url-md5(url),设置好了就不要轻易改动

    //在memcached中储存用户生成短链接的次数，防止用户向数据库中灌垃圾数据  因为无法从CDN处获取用户ip，此项配置暂时未用
    'remote_max_limits'      => '50',  //客户端最大可以生成的数据数量
    'remote_ip_cache_time'   => '10',  //remote_ip生成数据缓存时间，用于防止客户端向数据库中灌垃圾数据，单位秒,推荐1800 
    

    //常见蜘蛛User-agent  Dalvik 是google开发的安卓虚拟机，有大量请求的情况
    //zalo为越南的聊天软件
    //FB_IAB/FB4A;FBAV/409.0.0.27.106  为facebook来源的ua特征符号
    'spider_user_agent'      =>  'baiduspider|sogou|google|360spider|YisouSpider|Bytespider|bing|yodao|bot|robot|facebook|meta|twitter|reddit|WhatsApp|tiktok|Dalvik|telegram|crawler|ZaloPC|Zalo|discord|Aloha|CFNetwork|redditbot|HttpClient|tw\.ystudio\.BePTT|CFNetwork|com\.joshua\.jptt|okhttp/4',//注意不要以｜结尾，否则会匹配到所有的数据,｜为或运算符
    

    //移动端user_agent 用于程序逻辑判断是否是移动端
    'mobile_user_agent'      =>  'iPhone|Android|ios|mobile',

    //is_404_url_array  is_404为2时指定跳转的url,最好使用netflix的url来处理  is_404=2的设置配置选项停用，gotourl相关逻辑也清理了,此配置无用。


    //展示google广告中间url，所有的url都跳转到此url上用于展示google广告，此url必须得过google adsense审核
    //此数组中的域名必须要在google adsense后台过审
    //此项配置是一个备用配置，程序逻辑主要是从tp_adsense中读取数据，当出现逻辑错误时才会调用此项备用配置
    //值为直接域名，不带https,如：middle_page.com
    'google_adsense_middle_url'  => "middle_page.com",  


    //如果是pc端访问就直接跳转
    //1为直接跳转，0为不跳转，当此项之设置为1时，pc端会直接跳转，移动端会遵循下面google_adsense_middle_page_switch设置配置项
    'is_pc_visit_direct_jump'  => 1,
    'is_m_visit_direct_jump'  => 1,  //要展示中间页广告就将此项值设置为0


    //是否显示跳转中间页面，用于展示广告，中间页的跳转开关
    'google_adsense_middle_page_switch'  => 0, //0为关闭，1为开启
    //跳转中间件展示google adsense广告暂停倒计时时间，单位：秒。设置为0秒就自动跳转,不会展示跳转中介页
    //注意google adsense后台有一个CTR点击参数（网页点击率），正常不能超过8%，超过8%就可能会封号，跳转中间页等待时间过长会提升CTR值，正常10s左右最好
    'google_adsense_middle_page_sleep_time'      => 5, 
    
    //如果在google_adsense_middle_page_sleep_time时间后用户没有点击页面中的链接，就在..auto_jump_sleeptime自动跳转
    'google_adsense_middle_page_auto_jump_sleeptime'      => 18, 

    //广告中间页是否自动跳转，1为自动跳转，2为设置值为CLICK HERE让用户手动去点击  目的是为了增加广告点击量;值为3时，一半的几率自动跳转，一半的几率手动,
    //设置为3为了降低google adsense广告点击率，避免点击率过高导致账户被封
    //设置为3时跳转太快，基本加载6秒后就会跳转，不建议
    'google_adsense_middle_page_auto_jump_num'   => 2,


    
    //最长展示广告时间 ，根据tp_shortener中的timesleep表项来处理  当前时间戳 - 数据库时间戳 > 60*60*6(小时) 便开始展示广告 
    //单位：小时，超过6小时后就跳转中间页展示广告ad，没有超过就直接跳转到目标网址上
    //建议是6个小时，积累数据的初期最好不要设置跳转，不然用户可能不会使用，待数据积累到了一定的量时，再使用跳转中间件。
    //避免又出现短链被用作跳转诈骗而遭到举报，最好将时间设置短一些，默认是60*60*dispaly_ad_hour  0.5就是半小时，0就是不等待时间，所有的url都必须经过中间页
    //程序有一个cookies识别机制，如果是URL创建者生成的链接，则会自动自动跳转
    'display_ad_hour'        => 0, 

    //当点击数小于10时直接跳转，不展示广告中间页,正常页面点击一般都不会超过8个
    //直接跳转时又容易被举报为恶意网址
    'goto_url_auto_jump_min_hits' => 5,


    //首页上的统计数字基础数字
    'index_total_clicks_base_num' => 100000,
    'index_total_links_base_num'  => 5000,
    'index_today_links_rand_num'  => array(100,900), //在此数字间随机取一个值用作随机基础数

    //首页上是否展示用户cookies数据，即用户的历史url数据
    'index_display_user_cookies_data' => 0,



    //sitemaps_url_num
    'sitemaps_url_num'             => "5000",

    //配置:
    //0为不展示或者只展示网站自身品牌logo
    //1为展示广告,具体的广告根据数据库中的值进行展示，0为不展示，1为google adsense广告，2为affilist.com广告
    'gotourl_page_display_ads_switch' => "1",




    // ------------------------------------------------------------------
    // 默认跳转页面对应的模板文件【新增】
    'dispatch_success_tmpl' => app()->getRootPath() . '/public/tpl/dispatch_jump.tpl',
    'dispatch_error_tmpl'  => app()->getRootPath() . '/public/tpl/dispatch_jump.tpl',


    'http_exception_template'    =>  [
        // 定义404错误的模板文件地址
        404 =>  app()->getRootPath() . '/public/404.html',
        // 还可以定义其它的HTTP status
        401 =>  app()->getRootPath() . '/public/404.html',
    ],


    // ------------------------------------------------------------------
    // 应用地址
    'app_host'         => env('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',
    // 应用映射（自动多应用模式有效）
    'app_map'          => [],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => ["middleware","command"],
    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'    => 'Page error! Please try again later.',
    // 显示错误信息
    'show_error_msg'   => false,


];

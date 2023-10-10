<?php
namespace app\admin\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Request;
use think\facade\Config;


class Otherclass  extends BaseController
{

    public static function getRedisStatus($key){

        if(Cache::get($key)){
            $status = 1;
        }else{
            $status = 0;
        }
        return $status;
    }


    //overtime超时时间单位为秒，如果为空则不是设置超时时间，即永久有效
    public static function setRedisValue($key, $value=1, $overtime=0){
        try{
            if($overtime > 0){
                Cache::set($key, $value, $overtime);
            }else{
                Cache::set($key, $value);
            }
            $status = 1;
        }catch(\Exception $e){
            $status = 0;
        }

        return $status;

    }

    public static function getRedisValue($key){

        if(Cache::get($key)){
            $value = Cache::get($key);
            
            return $value;
        }else{
            return 0;
        }
    }




    //返回随机字符串
    public static function getShortUrlStr($length=6) {


        if($length != 6){
            $str_length = $length;
        }else{
            $str_length = Config::get("app.short_url_str_length");
        }



        $short_str = "";
        
        //随机生成32-50位长度的字符串，然后从0-6开始截取字符串去数据库中查询，如果能匹配到则自动增加1，直到匹配不到数据为止。
        //随机字符串不要太长，会占用cpu性能
        $num = mt_rand(32,32);

        $characters = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
        $rand_str = ''; 
        for ($i = 0; $i < $num; $i++) { 
            $index = mt_rand(0, strlen($characters) - 1); 
            $rand_str .= $characters[$index]; 
        }


        $timestamp = time();
        //将字符串使用base64加密, 替换掉base64加密后面可能产生的==号
        $rand_str = base64_encode($rand_str.$timestamp);
        $rand_str = preg_replace("#=#i", "", $rand_str);


        $start_num = 0;
        while(true){
            //截取指定长度的字符串
            $short_str = substr($rand_str, $start_num, $str_length);
            $redis_key = Config::get("app.redis_prefix").$short_str;

            //如果在redis数据库中未匹配到当前字符串，就说明当前字符串未被使用过，将数据储存在redis中，并且退出当前循环  
            if(!Cache::has($redis_key)){
                Cache::set($redis_key, 1, 43200); //60*60*12 设置12小时过期，避免生成大量的数据造成消耗redis资源
                break;
            }

            $start_num += 1;
            //当当前循环超过指定次数时会导致$start_num超过一个长度值
            if($start_num > (strlen($rand_str) - $str_length-1)){
                
                
                //------------    如果所有的数据都匹配完了还是没有匹配到short_str，就将长度+1   begin ------------

                //截取指定长度的字符串
                $short_str = substr($rand_str, $start_num, $str_length+1);
                $redis_key = Config::get("app.redis_prefix").$short_str;

                //如果在redis数据库中未匹配到当前字符串，就说明当前字符串未被使用过，将数据储存在redis中，并且退出当前循环  
                if(!Cache::has($redis_key)){
                    Cache::set($redis_key, 1, 43200); //60*60*12 设置12小时过期，避免生成大量的数据造成消耗redis资源
                    break;
                }
                
                //------------    如果所有的数据都匹配完了还是没有匹配到short_str，就将长度+1   end ------------

                //如果还是超出指定长度还没有匹配到数据，就将shortUrtStr设置为指定值
                if($start_num > strlen($rand_str) * 2){

                    //截取指定长度的字符串
                    $short_str = "errorShortStr".$timestamp."-".time();
                    $redis_key = Config::get("app.redis_prefix").$short_str;

                    //如果在redis数据库中未匹配到当前字符串，就说明当前字符串未被使用过，将数据储存在redis中，并且退出当前循环  
                    if(!Cache::has($redis_key)){
                        Cache::set($redis_key, 1, 43200); //60*60*12 设置12小时过期，避免生成大量的数据造成消耗redis资源
                        break;
                    }
                }

            }
        }

        return $short_str; 



    }


    public function getHostData($http_host){



        $host_data = Db::query('select * from tp_domain where domain_url="'.$http_host.'" order by itemid asc limit 1;');
        

        //如果数据库中未匹配到当前域名，
        if(count($host_data) == 0){
            //均为二级域名

            //url-short,free-url-short
            //short-url,free-short-url 
            //shorturl        
            //----------  对应  shorturl.hk   itemid为4
            if(preg_match("/url\-short/i",$http_host)  || preg_match("/short\-url/i",$http_host) || preg_match("/shorturl/i",$http_host)){
                $host_data = Db::query('select * from tp_domain where domain_url="shorturl.hk" order by itemid asc limit 1;');
            }

            //tinyurl二级域名
            //----------  对应  tinyurl.hk   itemid为3
            if(preg_match("/tinyurl/i",$http_host)  || preg_match("/tiny\-url/i",$http_host)){
                $host_data = Db::query('select * from tp_domain where domain_url="tinyurl.hk" order by itemid asc limit 1;');
            }


            //short-links
            //----------  对应  shortlinks   itemid为5
            if(preg_match("/short\-links/i",$http_host)){
                $host_data = Db::query('select * from tp_domain where domain_url="shortlinks" order by itemid asc limit 1;');
            }

            //shortener,url-shortener,free-url-shortener
            //----------  对应  shortener   itemid为6
            if(preg_match("/shortener/i",$http_host)){
                $host_data = Db::query('select * from tp_domain where domain_url="shortener" order by itemid asc limit 1;');
            }


            //再加一个判断，如果上述的url还是没有匹配到，就设置一个默认的url
            if(count($host_data) == 0){
                $host_data = Db::query('select * from tp_domain where domain_url="shorturl.hk" order by itemid asc limit 1;');
            }
        }


        return $host_data;
    }



    //返回是否是蜘蛛 1 true ; 0 false
    public static function getSpiderStatus($user_agent){
        if(preg_match("/".Config::get("app.spider_user_agent")."/i", $user_agent)){
            $spider_status = 1;
        }else{
            $spider_status = 0;
        }
        return $spider_status;
    }




        //从shorten程序提取的ip获取工具，能够很准确的获取到IP，只要能获取到用户的IP，可以去掉之前使用用户UA判断的选项
        public static function get_user_ip(){
            if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])) $ipaddress =  $_SERVER['HTTP_CF_CONNECTING_IP'];
            elseif (isset($_SERVER['HTTP_X_REAL_IP'])) 	 	$ipaddress = $_SERVER['HTTP_X_REAL_IP'];
            elseif (isset($_SERVER['HTTP_CLIENT_IP']))	 		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
            elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            elseif (isset($_SERVER['HTTP_X_FORWARDED']))		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
            elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
            elseif (isset($_SERVER['HTTP_FORWARDED']))	$ipaddress = $_SERVER['HTTP_FORWARDED'];
            elseif (isset($_SERVER['REMOTE_ADDR']))	$ipaddress = $_SERVER['REMOTE_ADDR'];
            else $ipaddress = "null";
    
            return $ipaddress;
        }
    
        public function userAgent(){
            if(isset($_SERVER["HTTP_USER_AGENT"])) $userAgent= $_SERVER["HTTP_USER_AGENT"];
            elseif(isset($_SERVER["HTTP_REFERER"])) $userAgent= $_SERVER["HTTP_REFERER"];
            else  $userAgent = "null";
    
            return $userAgent;
        }
    
        public function device(){
            $platform =   "Unknown OS";
            $os       =  [
                        '/windows nt 11.0/i'    =>  'Windows 11',
                        '/windows nt 10.0/i'    =>  'Windows 10',
                        '/windows nt 6.3/i'     =>  'Windows 8.1',
                        '/windows nt 6.2/i'     =>  'Windows 8',
                        '/windows nt 6.1/i'     =>  'Windows 7',
                        '/windows nt 6.0/i'     =>  'Windows Vista',
                        '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                        '/windows nt 5.1/i'     =>  'Windows XP',
                        '/windows xp/i'         =>  'Windows XP',
                        '/windows nt 5.0/i'     =>  'Windows 2000',
                        '/windows me/i'         =>  'Windows ME',
                        '/win98/i'              =>  'Windows 98',
                        '/win95/i'              =>  'Windows 95',
                        '/win16/i'              =>  'Windows 3.11',
                        '/macintosh|mac os x/i' =>  'Mac OS X',
                        '/mac_powerpc/i'        =>  'Mac OS 9',
                        '/linux/i'              =>  'Linux',
                        '/ubuntu/i'             =>  'Ubuntu',
                        '/iphone/i'             =>  'iPhone',
                        '/ipod/i'               =>  'iPod',
                        '/ipad/i'               =>  'iPad',
                        '/android/i'            =>  'Android',
                        '/blackberry/i'         =>  'BlackBerry',
                        '/bb10/i'         		=>  'BlackBerry',
                        '/cros/i'				=>	'Chrome OS',
                        '/webos/i'              =>  'Mobile'
                    ];
            foreach ($os as $regex => $value) { 
                if (preg_match($regex, $this->userAgent())) {
                    $platform    =   $value;
                }
            }   
            return $platform;	
        }
    
    
        public function browser() {
            $matched   = 	false;
            $browser   =   "Unknown Browser";
            $browsers  =   [
                            '/safari/i'     =>  'Safari',			
                            '/firefox/i'    =>  'Firefox',
                            '/fxios/i'    	=>  'Firefox',						
                            '/msie/i'       =>  'Internet Explorer',
                            '/Trident\/7.0/i'  =>  'Internet Explorer',
                            '/chrome/i'     =>  'Chrome',
                            '/crios/i'		=>	'Chrome',
                            '/opera/i'      =>  'Opera',
                            '/opr/i'      	=>  'Opera',
                            '/netscape/i'   =>  'Netscape',
                            '/maxthon/i'    =>  'Maxthon',
                            '/konqueror/i'  =>  'Konqueror',
                            '/edg/i'       =>  'Edge',
                        ];
            
            foreach ($browsers as $regex => $value) { 
                if (preg_match($regex,  $this->userAgent())) {
                    $browser  =  $value;
                    $matched = true;
                }
            }
            
            if(!$matched && preg_match('/mobile/i', $this->userAgent())){
                $browser = 'Mobile Browser';
            }
    
            return $browser;
        } 
    
    
    
        //返回英文国家名称
        //https://github.com/maxmind/GeoIP2-php#city-example
        public function get_country($ip){
            try {
                $reader = new \GeoIp2\Database\Reader(Config::get("app.install_path").'vendor/geoip2/GeoLite2-City.mmdb');
                $record = $reader->city($ip);
                $country = $record->country->name;
    
                if(empty($country)){
                    $country = "None";
                }
            }catch(\Exception $e){
                $country = "None";
            }
    
    
            return $country;
        }

}
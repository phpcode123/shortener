<?php
namespace app\index\controller;


use app\BaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Db;
use think\facade\Cache;
use GeoIp2\Database\Reader;


class Otherclass  extends BaseController
{


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


    public function urlMd5Hash($url){
        $urlMd5Hash = Config::get("app.redis_url_catch_prefix").md5($url);
        return $urlMd5Hash;
    }
    


    public function getHostData($http_host){

        $host_data = Db::table("tp_domain")->where("domain_url",$http_host)->order("itemid","asc")->limit(1)->select();

        //var_dump($host_data);
        //让所有的url都可以有数据
        if(count($host_data) == 0){
            $host_data = Db::table("tp_domain")->where("itemid","1")->order("itemid","asc")->limit(1)->select();
        }


        return $host_data;
    }

    public function get_adsense_host_data($http_host){

        $host_data = Db::table("tp_adsense")->where("adsense_domain",$http_host)->order("itemid","asc")->limit(1)->select();

        //var_dump($host_data);
        //让所有的url都可以有数据
        if(count($host_data) == 0){
            $this->error("host data length less than 1","/",3);
        }


        return $host_data;
    }


    //返回是否是蜘蛛 1 true ; 0 false
    public static function getSpiderStatus($user_agent){
        if(preg_match("#".Config::get("app.spider_user_agent")."#i", $user_agent)){
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


    public function is_url($url){
        $url = trim($url);
        if(empty($url)) return FALSE;        

        $parsed = parse_url($url);
        
        //$protocol = $parsed['scheme'] ?? 'http://'; 
        //echo $protocol;       
        $schemes =["http", "https", "www"];
       // $schemes = explode(",", config("schemes"));

       // $schemes = array_diff($schemes, ["http", "https", "www"]);
        //dump($schemes);
        // if($protocol){
        //     if(in_array($protocol, $schemes)){
        //         return $url;
        //     }
        // }

        if(preg_match('~^([a-zA-Z0-9+!*(),;?&=$_.-]+(:[a-zA-Z0-9+!*(),;?&=$_.-]+)?@)?([a-zA-Z0-9\-\.]*)\.(([a-zA-Z]{2,4})|([0-9]{1,3}\.([0-9]{1,3})\.([0-9]{1,3})))(:[0-9]{2,5})?(/([a-zA-Z0-9+$_%-]\.?)+)*/?(\?[a-z+&\$_.-][a-zA-Z0-9;:@&%=+/$_.-]*)?(#[a-z_.-][a-zA-Z0-9+$%_.-]*)?~', $url) && !preg_match('(http://|https://)', $url)){
            $url = "http://$url";
        }
        //echo ">> url : ".$url."   ";
        if(!$this->is_url_check($url)) return false;

        if(!filter_var($url, FILTER_VALIDATE_URL)){
            $parsed = parse_url($url);
            if(!isset($parsed["scheme"]) || !$parsed["scheme"]) return false;
            if(!isset($parsed["host"]) || !$parsed["host"]) return false;
        }                    
        return $url;
    }


    //判断是否是url
    public function is_url_check($url){
        if(empty($url)) return FALSE;    

        if (preg_match("#(?i)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))#", $url)){
            return true;
        }else{
            return false;
        }
        
        
    }


    //返回随机的recommand itemid tr list，需要传入最大的itemid值和返回的字符串长度，返回值用逗号隔开
    public function roblox_recom_itemid_str($max_length){
        $arr = [];
        $max_itemid_data = Db::table("tp_youtube_url")->order("itemid","desc")->limit(1)->select();
        while(count($arr) < $max_length){
            
            $rand_num = mt_rand(0,$max_itemid_data[0]['itemid']);
            if(!in_array($rand_num, $arr)){
                array_push($arr,$rand_num);
            }
        
        }

        $new_list_str = join(",",$arr);
        //echo $new_list_str;
        return $new_list_str;
    }

}
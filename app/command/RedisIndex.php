<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Config;
use think\facade\Db;
use think\facade\Cache;
use app\index\controller\Otherclass;

class RedisIndex extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('app\command\redisindex')
            ->setDescription('the app\command\redisindex command');
    }

    protected function execute(Input $input, Output $output)
    {


        $otherclass= new Otherclass($this->app);
        $count_num = 0;
        while(true){


            //$data = Db::query("select itemid,url,short_url from tp_shortener where redis_index=0 order by itemid asc limit 5000;");
            
            $data = Db::table("tp_shortener")->where("redis_index","0")->order("itemid","asc")->limit(5000)->select();



            //count_num 用于计数，当大于10次时就跳出当前循环结束程序
            if(count($data) == 0){
                $count_num += 1;

                if($count_num > 3){
                    echo ">> Running success!\n";
                    break;
                }
            }else{
                //归0
                $count_num = 0;
            }
            



            
            if(count($data) > 0){
                
                for($i=0; $i<count($data); $i++){
                    //echo $i;

                    $itemid = $data[$i]['itemid'];
                    $url = $data[$i]['url'];//跳转的url
                    $short_url = $data[$i]['short_url'];  //short_str
                    

                    

                    //md5 hash值
                    $url_md5_hash = Config::get("app.redis_url_catch_prefix").md5($url);
                    
                    //将url-md5($url)储存至redis
                    Cache::set($url_md5_hash, $itemid);



                    /*
                    *注意，redis缓存会储存4个值
                    *url-md5(url)   //用户要储存的url，主要用于用户生成url去重
                    *short_str,itemid     //6位short_str
                    *short_str_7,itemid   //7位short_str
                    *short_str_8,itemid   //8位short_str //如果展示adsense广告，这个是最终展示广告页面
                    */
                    
                    $short_str_7 = $otherclass->getShortUrlStr(7);
                    $short_str_8 = $otherclass->getShortUrlStr(8);
                    

                    //设置redis
                    if(Cache::set($short_url, $itemid) && Cache::set($short_str_7, $itemid) && Cache::set($short_str_8, $itemid)){
                        $update_data = [
                            "short_url_7" => $short_str_7,
                            "short_url_8" => $short_str_8,
                            "redis_index" => 1
                        ];
                        Db::table("tp_shortener")->where("itemid",$itemid)->update($update_data);
        

                        //服务器上输出较慢，1000行才输入一次
                        if($i%1000 == 0){
                            echo ">> itemid:".$data[$i]['itemid']." short_url:".$data[$i]['short_url']." - ".$short_str_7." - ".$short_str_8."\n";" set redis success!\n";
                        }
                    }
                }
            }else{
                echo ">> Running success! ".date("Y-m-d H:i:s")."\n";
                break;
            } 
            
        }

    }
}

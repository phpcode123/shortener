<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Cookie;
use GuzzleHttp\Client;
use QL\QueryList;

class CheckMalicious extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('check_malicious')
            ->setDescription('the check_malicious command');
    }

    protected function execute(Input $input, Output $output)
    {
        $sleep_time = 20;
        while(true){
            try{
                $check_malicious_url = "https://transparencyreport.google.com/transparencyreport/api/v3/safebrowsing/status";

                $url_data = Db::table("tp_shortener")->where("check_malicious_status","0")->order("itemid","asc")->limit(100)->select();

                for($i=0; $i<count($url_data); $i++){
                    try{
                        echo ">> url:".substr($url_data[$i]['url'],0,100)."\n";

                        //请求url，获取页面源码
                        $queryList = new QueryList;
                        $res = $queryList->get($check_malicious_url, [
                            'site' => substr($url_data[$i]['url'],0,1000)
                            ],['headers' => [
                                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.3987.149 Safari/537.36',
                                'Accept-Encoding' => 'gzip, deflate, br',
                                'accept-language' => 'en-US,en;q=0.9'
                            ]
                        ]);
                        
                        $html = (string)$res->getHtml();

                        
                        $html = preg_replace("/\)\]\}'/i","",$html);
                        $html = preg_replace("/\s+|\r\n/i","",$html);
                        preg_match_all("/\"sb.ssr\",(.*?),\"/i",$html, $status_str);
                        //echo $html."\n";
                        $status_code = $status_str[1][0];

                        //status_code:1,0,0,0,0,0,1659914965495
                        echo ">> itemid:".$url_data[$i]['itemid']." status_code:".$status_code."\n";
                        


                        $status_code_array = explode(",",$status_code);


                        //判断是否是恶意网址，如果是恶意网址就将is_404设置为9
                        //is_404状态：1为自己临时调整设置，9为恶意网址
                        if($status_code_array[0] == 2 && $status_code_array[3] == 1){
                            echo ">> Is malicious url!\n";
                            $update_data = [
                                'check_malicious_status' => 1,
                                'is_404'=> 9
                            ];
                            
                        }else{
                            $update_data = [
                                'check_malicious_status' => 1
                            ];
                        }

                        Db::table("tp_shortener")->where("itemid",$url_data[$i]['itemid'])->update($update_data);
                        sleep(1);
                    }catch(\Exception $e){
                        echo "Error, time sleep(".$sleep_time.")\n";
                        
                        //var_dump($e);
                        $file = fopen("./milicious_url_error.txt","a+");
                        fwrite($file,(string)$e);
                        fclose($file);
                        sleep($sleep_time);
                        continue;
                    }

                }

                
                $malicious_url_key = Config::get("app.redis_prefix")."malicisou_url_status";
                Cache::set($malicious_url_key, time(),1800);

                echo ">> Date:".date("Y-m-d H:i:s")." - Sleep(".$sleep_time.")\n";
                sleep($sleep_time);
            }catch(\Exception $e){
                dump($e);
                echo "Error, time sleep(".$sleep_time.")\n";
                sleep($sleep_time);
            }

        }
    }
}

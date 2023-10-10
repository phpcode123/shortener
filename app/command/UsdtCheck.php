<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Db;

class UsdtCheck extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('app\command\UsdtCheck')
            ->setDescription('the app\command\UsdtCheck command');
    }


    public function spider($linkurl,$debug=false){
        $client = new \GuzzleHttp\Client(['cookies' => true]);

        $onRedirect = function(
            RequestInterface $request,
            ResponseInterface $response,
            UriInterface $uri
        ) {
            echo 'Redirecting! ' . $request->getUri() . ' to ' . $uri . "\n";
        };

        $res = $client->request('GET',$linkurl,['headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36',
                'Accept-Encoding' => 'gzip, deflate, br',
                'accept-language' => 'en-US,en;q=0.9',
                'accept'=> 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'accept-encoding'=> 'gzip, deflate, br',
                'accept-language'=> 'en-US,en;q=0.9',
                'cache-control'=> 'no-cache',
                'pragma'=> 'no-cache',
            ],
            'query' => [
                'sort' => '-timestamp',
                'limit' => '50',
                'start' => '0',
                'direction' => '2',//1为转账给别人 2为收账 0为全部账目
                'db_version' => '1',
                'trc20Id' => Config::get("app.usdt_api_trc20Id"),
                'address' => Config::get("app.usdt_address")
            ],
            'allow_redirects' => [
                'max' => 10,        // allow at most 10 redirects.
                'strict'          => true,      // use "strict" RFC compliant redirects.
                'referer'         => true,      // add a Referer header
                'protocols'       => ['https','http'], // only allow https URLs
                'on_redirect'     => $onRedirect,
                'track_redirects' => true
            ],
        //   'proxy' => [
        //     'http'  => 'socks://127.0.0.1:1080', // Use this proxy with "http"
        //     'https' => 'socks://127.0.0.1:1080', // Use this proxy with "https",
        //     'no' => ['.mit.edu', 'foo.com']    // Don't use a proxy with these
        //   ],
            'verify' => false,
            'connect_timeout' => 30,
            'timeout' => 30,
            'debug' => $debug
        ]);

        return (string)$res->getBody();

    }


    protected function execute(Input $input, Output $output)
    {
    

        $usdt_api_url = Config::get("app.usdt_api");
        while(true){
            try{
                $json_html = self::spider($usdt_api_url);
                $json_data = json_decode($json_html, true);
                //var_dump($json_data['data'][0]['to']);

                foreach($json_data['data'] as $item){
                    //$token_name = $item["TetherToken"];
                    $money = $item["amount"]/1000000;
                    $hash = $item['hash'];
                    $date = date("Y-m-d H:i:s",$item['block_timestamp']/1000);
                    
                    

                    $redis_key = Config::get("app.redis_prefix")."usdt_check_hash_".$hash;
                    if(Cache::get($redis_key)){

                        //just for test
                        //Cache::set($redis_key,"1",6); 
                        echo "date:".$date." - hash:".substr($hash,0,16)." - money:".$money." old order";
                    }else{
                        Cache::set($redis_key,"1",60*60*24*30);
                        echo "date:".$date." - hash:".substr($hash,0,16)." - money:".$money." new order ---";

                        //查找符合价格数字的订单
                        $price_data = Db::table("tp_order")->where("price",$money)->where("success",0)->where("create_time",">",time()-Config::get("app.usdt_pay_timeout"))->select();
                        
                        //var_dump($price_data);
                        //如果有数据就更新数据库
                        if(count($price_data)>0){
                            try{
                                $update_data = [
                                    "success"=>1,
                                    "pay_time"=>time()
                                ];
                                Db::table("tp_order")->where("id",$price_data[0]['id'])->update($update_data);

                                echo " update_data success";
                            }catch(\Exception $e){
                                var_dump($e);
                                echo " update_data faild";
                            }
                        }else{
                            echo " data:0 ";
                        }
                        
    
                    }
                    echo "\n";

                }

                echo date("Y-m-d H:i:s")."----------------------\n";




                sleep(6);
            }catch(\Exception $e){
                var_dump($e);
            }

        }

        



    }
}

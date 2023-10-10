<?php
namespace app\index\controller;


use app\BaseController;
use think\facade\Config;
use GuzzleHttp\Client;


class HttpClass extends BaseController
{
    public function get($url, $api_password, $headers="", $timeout=5, $connect_timeout=5){

        //调用前需要使用api密码
        if($api_password != Config::get("app.api_password") || empty($api_password)){
            return ">> HttpClass get api password error.\n";
        }

        //如果headers为空，则自定义headers
        if(empty($headers)){
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.3987.149 Safari/537.36',
                'Accept-Encoding' => 'gzip, deflate',
                'Accept-language' => 'en-US,en;q=0.9',
                'Connection' => 'keep-alive',
                'Cache-Control' =>  'no-cache',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9'

            ];
        }


        try{
            $client = new \GuzzleHttp\Client();
            $res = $client->request('GET', $url, [
                ['headers' => $headers],
                ['timeout' => $timeout],
                ['connect_timeout' => $connect_timeout]
                
            ]);

            $html = (string)$res->getBody();
            return $html;
            
            
            
        }catch(\Exception $e){

            //返回数据
            $data = [
                "status_code" => 0,
                "message" => "HttpClass web get error\n",
                "data" => "none"
            ];
            return json_encode($data);
        }
  
    }

    

    public function post($url, $data, $api_password, $headers="", $timeout=5, $connect_timeout=5){

        //调用前需要使用api密码
        if($api_password != Config::get("app.api_password") || empty($api_password)){
            return ">> HttpClass post api password error.\n";
        }


        
        //如果headers为空，则自定义headers
        if(empty($headers)){
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.3987.149 Safari/537.36',
                'Accept-Encoding' => 'gzip, deflate',
                'Accept-language' => 'en-US,en;q=0.9',
                'Connection' => 'keep-alive',
                'Cache-Control' =>  'no-cache',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9'

            ];
        }


        try{
            $client = new \GuzzleHttp\Client(
                ['headers' => $headers],
                ['timeout' => $timeout],
                ['connect_timeout' => $connect_timeout]

            );
            $res = $client->request('POST', $url, [
                'form_params' => $data                
            ]);

            $html = $res->getBody();
            echo $html;
            
            
        }catch(\Exception $e){

            //返回数据
            $data = [
                "status_code" => 0,
                "message" => "HttpClass web post error\n",
                "data" => "none"
            ];
            return json_encode($data);
        }
  
    }



}
<?php
namespace app\index\controller;


use app\BaseController;
use think\facade\Db;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Cookie;
use GuzzleHttp\Client;
use QL\QueryList;


class Console extends BaseController 
{


    // php index.php index/console/check_malicious

    //恶意url检查 
    // 恶意网址：[["sb.ssr",2,0,0,1,0,0,1659803819667,"https://supp4.interpretcambodia.com/session3/scrmde?/auth/lsgin/present?origin\u003dmobilebrowser"]]
    // e.vg （正常）[["sb.ssr",1,0,0,0,0,0,1659924100415,"https://e.vg/"]]
    // m5.gs （正常）  [["sb.ssr",1,0,0,0,0,0,1659894192074,"https://m5.gs"]]
    // shorturl.hk（有些页面不安全） [["sb.ssr",3,0,0,1,0,0,1659916467516,"https://shorturl.hk"]]
    // 9h.fit(恶意网址) [["sb.ssr",2,0,0,1,0,0,1659926723850,"https://9h.fit"]]
    // 9a.fit（有些页面不安全）[["sb.ssr",3,0,0,1,0,0,1659914965495,"https://9a.fit"]]

    // "sb.ssr",2,0,0,1,0,0,1659926723850

    // 1为正常
    // 2为恶意网址 浏览器会拦截
    // 3为警告，页面可以正常访问 
    // 4为很难判断当前网址的数据状态，即此类站点的数据太多 一些大型的站点返回的都是此状态，如：https://open.spotify.com/track  https://instagram.com 
    // 5为分发不常见的软件
    // 6为无数据可用 https://docs.google.com/xxx

    public function check_malicious(){
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
                        fwrite($file,$e);
                        fclose($file);
                        sleep($sleep_time);
                        continue;
                    }

                }

                
                $malicious_url_key = Config::get("redis_prefix")."malicisou_url_status";
                Cache::set($malicious_url_key, time(),1800);

                echo ">> time sleep(".$sleep_time.")\n";
                sleep($sleep_time);
            }catch(\Exception $e){
                echo "Error, time sleep(".$sleep_time.")\n";
                sleep($sleep_time);
            }

        }

    } 

    //这个是检查check_malicious_status为1的数据状态，如果源码页面正常就将页面check_malicious_status值设置为2,否则就将其值设置为9,值为9时改为手动处理。
    //这个是在web服务器上直接运行的脚本，无需通过api接口
    //check_malicious_2_local 方法是通过api接口传递数据，可以在本地运行脚本
    public function check_malicious_2(){
        while(true){

            $data = Db::table("tp_shortener")->where("check_malicious_status","9")->where("is_404","0")->order("itemid","desc")->limit(100)->select();
            //$data = Db::table("tp_shortener")->where("itemid","73343")->order("itemid","desc")->limit(100)->select();

            //var_dump($data);

            foreach($data as $item){
                $itemid = $item['itemid'];
                $url = $item['url'];
                
                echo ">> itemid:{$item['itemid']} url:{$item['url']}\n";





                //---------------  url白名单，只要包含此url，直接略过    begin-------------------------
                $url_white_list = ["\.office\.com/","1drv\.ms/","\.amazon\.com\.br/","\.amazon\.co\.uk/","\.azure\.com/","\.bing\.com/","\.powerbi\.com/","\.microsoft\.com/","\.twitter\.com/","//youtube\.com/","\.tiktok\.com/","\.mediafire\.com/","//shope\.ee/","//shopee\.com\.br/","\.whatsapp\.com","\.spotify\.com/","\.google\.com\.\\w{1,3}/","\.mycloud\.com/","www\.linkedin\.com/","\.apple\.com/","\.google\.com/","forms\.gle/","\.adobe\.com/","\.goo.gl/","\.booking\.com/","\.instagram\.com/","\.amazon\.com/","\.amazon\.\w{2,3}/","\.live\.com/","amzn\.to/","youtube\.com/","youtu\.be/"];



                $url_white_num = 0;
                foreach($url_white_list as $url_while_item){
                    if(preg_match_all("#".$url_while_item."#i",$url)){
                        $url_white_num = 1;
                        break;
                    }else{
                        $url_white_num = 0;
                    }
                }

                if($url_white_num == 1){
                    echo ">> url_white_num:{$url_white_num}\n";
                    echo ">> itemid:".$itemid." set check_malicious_status=2.\n";
                    Db::table("tp_shortener")->where("itemid",$itemid)->update(["check_malicious_status"=>2]);
                    continue;
                }
                //---------------  url白名单，只要包含此url，直接略过   end -------------------------


                //指定后缀的url不爬取链接
                $url_white_word = ["\.pdf/?$","\.apk/?$","\.exe/?$","\.run/?$"."\.rpm/?$","\.png/?$","\.jpg/?$","\.jpeg/?$","\.git/?$","\.gif/?$","\.zip/?$","\.rar/?$","\.mp4/?$","\.avi/?$","\.mpeg/?$","\.wmv/?$","\.mov/?$","\.flv/?$","\.qsv/?$","\.kux/?$","\.dat/?$","\.mkv/?$","\.vob/?$","\.swf/?$","\.rm/?$","\.mp3/?$","\.aac/?$","\.wav/?$","\.wma/?$","\.cda/?$","\.flac/?$","\.m4a/?$","\.mid/?$","\.mka/?$","\.mp2/?$","\.mpa/?$","\.mpc/?$","\.ape/?$","\.ofr/?$","\.ogg/?$","\.ra/?$","\.wv/?$","\.tta/?$","\.ac3/?$","\.dts/?$"];

                $url_white_word_num = 0;
                foreach($url_white_word as $url_item_1){
                    
                    if(preg_match_all("#".$url_item_1."#i",$url)){
                        $url_white_word_num = 1;
                        break;
                    }else{
                        $url_white_word_num = 0;
                    }
                }




                if($url_white_word_num == 1){
                    echo ">> url_white_word_num:{$url_white_word_num}\n";
                    Db::table("tp_shortener")->where("itemid",$itemid)->update(["check_malicious_status"=>2]);
                    continue;
                }



                try{
                    //请求url，获取页面源码
                    try{
                        $queryList = new QueryList;
                        $res = $queryList->get($url, [],['headers' => [
                                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.3987.149 Safari/537.36',
                                'Accept-Encoding' => 'gzip, deflate, br',
                                'accept-language' => 'en-US,en;q=0.9'
                            ],
                            'allow_redirects' => [
                                'max' => 500,
                            ],
                            'connect_timeout' => 6,
                            'timeout' => 8
                        ]);
                    }catch(\Exception $e){
                        echo ">> itemid:".$itemid." set check_malicious_status=8.\n";
                        Db::table("tp_shortener")->where("itemid",$itemid)->update(["check_malicious_status"=>8]);
                        continue;
                    }
                    $html = (string)$res->getHtml();

                    
                    //替换空白字符
                    $html = preg_replace("/\s+/i","",$html);
                    

                    //echo $html;
                    //匹配title

                    preg_match_all("#<title>(.*?)</title>#i",$html,$title);

                    //var_dump($title);

                    //sleep(10);

                    $black_keyword = ["amazon","microsoft","twitter"];

                    $black_keyword_num = 0;
                    if(count($title) > 0){
                        
                        foreach($black_keyword as $k){
                            if(preg_match("/{$k}/i",$title[0][0])){
                                echo $k."\n";
                                $black_keyword_num = 1;
                                break;
                            }else{
                                $black_keyword_num = 0;
                            }
                        }
                    }   
                    if($black_keyword_num == 1){
                        echo ">> itemid:".$itemid." set check_malicious_status=9.\n";
                        Db::table("tp_shortener")->where("itemid",$itemid)->update(["check_malicious_status"=>9]);
                    }else{
                        echo ">> itemid:".$itemid." set check_malicious_status=2.\n";
                        Db::table("tp_shortener")->where("itemid",$itemid)->update(["check_malicious_status"=>2]);
                    }


                    //sleep(10);
                }catch(\Exception $e){
                    echo ">> itemid:".$itemid." set check_malicious_status=8.\n";
                    Db::table("tp_shortener")->where("itemid",$itemid)->update(["check_malicious_status"=>8]);
                    continue;
                }



            }

            echo ">> sleep(10)";
            sleep(10);

        }

    }



    //这个是检查check_malicious_status为1的数据状态，如果源码页面正常就将页面check_malicious_status值设置为2,否则就将其值设置为9,值为9时改为手动处理。
    //这个是在web服务器上直接运行的脚本，无需通过api接口
    //check_malicious_2_local 方法是通过api接口传递数据，可以在本地运行脚本
    public function check_malicious_2_local(){
        while(true){
            $api_url_get = "https://m5.gs/index.php?s=index/http_api/check_malicious_2_get_api?api_password=".Config::get("api_password");
            $api_url_post = "https://m5.gs/index.php?s=index/http_api/check_malicious_2_post_api";

            $httpClass = new HttpClass($this->app);
            $html = $httpClass->get($api_url_get,Config::get("api_password"));
            //echo $html;
            $data = json_decode($html,true);
            //var_dump($data);


            if($data['status_code'] == 0){
                 echo ">> {$data['message']}";
            }else{
                $data = $data["data"];

                $itemid = $data['itemid'];
                $url = $data['url'];

                echo ">> itemid:{$itemid} url:{$url}\n";

                //---------------  url白名单，只要包含此url，直接略过    begin-------------------------
                $url_white_list = ["\.office\.com/","1drv\.ms/","\.amazon\.com\.br/","\.amazon\.co\.uk/","\.azure\.com/","\.bing\.com/","\.powerbi\.com/","\.microsoft\.com/","\.twitter\.com/","//youtube\.com/","\.tiktok\.com/","\.mediafire\.com/","//shope\.ee/","//shopee\.com\.br/","\.whatsapp\.com","xvideos","pron","\.spotify\.com/","\.google\.com\.\\w{1,3}/","\.mycloud\.com/","www\.linkedin\.com/","\.apple\.com/","\.google\.com/","forms\.gle/","\.adobe\.com/","\.goo.gl/","\.booking\.com/","\.instagram\.com/","\.amazon\.com/","\.amazon\.\w{2,3}/","\.live\.com/","amzn\.to/","youtube\.com/","youtu\.be/","\.canva\.com/","//wa\.me/","line\.me/","netlify\.app","//vk\.sv/","//shopee\.","\.sharepoint\.com/","facebook\.com/","twitter\.com\/","tokopedia\.com/","gofood\.link","snapchat\.com/","\.bbc\.com/","\.wasap\.my/","atfx\.com/","//wa\.link/","//wa\.me/","//mega\.nz/"];


                $url_white_num = 0;
                foreach($url_white_list as $url_while_item){
                    if(preg_match_all("#".$url_while_item."#i",$url)){
                        $url_white_num = 1;
                        break;
                    }else{
                        $url_white_num = 0;
                    }
                }

                if($url_white_num == 1){
                    echo ">> url_white_num:{$url_white_num}\n";
                    echo ">> itemid:".$itemid." set check_malicious_status=2.\n";
                    
                    $post_data = [
                        "itemid" => $itemid,
                        "check_malicious_url_value" => 2,
                        "api_password" => Config::get("api_password")
                    ];
                    //var_dump($post_data);

                   
                    $response = $httpClass->post($api_url_post,$post_data,Config::get("api_password"));
                    echo $response."\n";


                    continue;
                }
                //---------------  url白名单，只要包含此url，直接略过   end -------------------------


                //指定后缀的url不爬取链接
                $url_white_word = ["\.pdf/?$","\.apk/?$","\.exe/?$","\.run/?$"."\.rpm/?$","\.png/?$","\.jpg/?$","\.jpeg/?$","\.git/?$","\.gif/?$","\.zip/?$","\.rar/?$","\.mp4/?$","\.avi/?$","\.mpeg/?$","\.wmv/?$","\.mov/?$","\.flv/?$","\.qsv/?$","\.kux/?$","\.dat/?$","\.mkv/?$","\.vob/?$","\.swf/?$","\.rm/?$","\.mp3/?$","\.aac/?$","\.wav/?$","\.wma/?$","\.cda/?$","\.flac/?$","\.m4a/?$","\.mid/?$","\.mka/?$","\.mp2/?$","\.mpa/?$","\.mpc/?$","\.ape/?$","\.ofr/?$","\.ogg/?$","\.ra/?$","\.wv/?$","\.tta/?$","\.ac3/?$","\.dts/?$","\.7z/?$"];

                $url_white_word_num = 0;
                foreach($url_white_word as $url_item_1){
                    
                    if(preg_match_all("#".$url_item_1."#i",$url)){
                        $url_white_word_num = 1;
                        break;
                    }else{
                        $url_white_word_num = 0;
                    }
                }




                if($url_white_word_num == 1){
                    echo ">> url_white_word_num:{$url_white_word_num}\n";
                    

                    $post_data = [
                        "itemid" => $itemid,
                        "check_malicious_url_value" => 2,
                        "api_password" => Config::get("api_password")
                    ];
                    //var_dump($post_data);

                   
                    $response = $httpClass->post($api_url_post,$post_data,Config::get("api_password"));
                    echo $response."\n";


                    continue;
                }


                $httpClass = new HttpClass($this->app);
                try{
                    //请求url，获取页面源码
                    try{
                        $client = new \GuzzleHttp\Client();
                        $res = $client->request('GET',$url, [],['headers' => [
                                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.3987.149 Safari/537.36',
                                'Accept-Encoding' => 'gzip, deflate, br',
                                'accept-language' => 'en-US,en;q=0.9'
                            ],
                            'allow_redirects' => [
                                'max' => 500,
                            ],
                            'connect_timeout' => 6,
                            'timeout' => 8
                        ]);

                    }catch(\Exception $e){
                        echo ">> itemid:".$itemid." set check_malicious_status=8.\n";
                        $post_data = [
                            "itemid" => $itemid,
                            "check_malicious_url_value" => 8,
                            "api_password" => Config::get("api_password")
                        ];
                        //var_dump($post_data);

                       
                        $response = $httpClass->post($api_url_post,$post_data,Config::get("api_password"));
                        echo $response."\n";
                        
 
                        continue;
                    }
                    // ----------------      判断headers是否是text/html类型的 begin----------------
                    //获取headers
                    $respone_headers = $res->getHeader("content-type");
                    echo ">> content-type:".$respone_headers[0]."\n";
                    //如果html中匹配不到text就将status设置为2
                    if(!preg_match("#text#i",$respone_headers[0])){
                        echo ">> itemid:".$itemid." content-type is not text/html, set check_malicious_status=2.\n";
                        $post_data = [
                            "itemid" => $itemid,
                            "check_malicious_url_value" => 2,
                            "api_password" => Config::get("api_password")
                        ];
                        //var_dump($post_data);

                        $response = $httpClass->post($api_url_post,$post_data,Config::get("api_password"));
                        echo $response."\n";
                        continue;

                    }

                    // ----------------      判断headers是否是text/html类型的 end ----------------


                    $html = (string)$res->getBody();
                    
                    //替换空白字符
                    $html = preg_replace("/\s+/i","",$html);
                    

                    //echo $html;
                    //匹配title

                    preg_match_all("#<title>(.*?)</title>#i",$html,$title);

                    //var_dump($title);

                    //sleep(10);

                    $black_keyword = ["amazon","microsoft","twitter","account","sign","login","Verifi","locked","page","admin","document"];

                    $black_keyword_num = 0;
                    if(count($title) > 0){
                        
                        foreach($black_keyword as $k){
                            if(preg_match("/{$k}/i",$title[0][0])){
                                echo $k."\n";
                                $black_keyword_num = 1;
                                break;
                            }else{
                                $black_keyword_num = 0;
                            }
                        }
                    }   
                    if($black_keyword_num == 1){
                        echo ">> itemid:".$itemid." set check_malicious_status=9.\n";
                        $post_data = [
                            "itemid" => $itemid,
                            "check_malicious_url_value" => 9,
                            "api_password" => Config::get("api_password")
                        ];
                        //var_dump($post_data);

                        $response = $httpClass->post($api_url_post,$post_data,Config::get("api_password"));
                        echo $response."\n";


                    }else{
                        echo ">> itemid:".$itemid." set check_malicious_status=2.\n";
                        $post_data = [
                            "itemid" => $itemid,
                            "check_malicious_url_value" => 2,
                            "api_password" => Config::get("api_password")
                        ];
                        //var_dump($post_data);

                        $response = $httpClass->post($api_url_post,$post_data,Config::get("api_password"));
                        echo $response."\n";

                    }


                    //sleep(10);
                }catch(\Exception $e){
                    echo ">> itemid:".$itemid." set check_malicious_status=8.\n";
                    $post_data = [
                        "itemid" => $itemid,
                        "check_malicious_url_value" => 8,
                        "api_password" => Config::get("api_password")
                    ];
                    //var_dump($post_data);

                    
                    $response = $httpClass->post($api_url_post,$post_data,Config::get("api_password"));
                    echo $response."\n";

                    continue;
                }



            }

            echo ">> sleep(1)\n";
            sleep(1);

        }

    }


    public function cm2l(){
        try{
            @self::check_malicious_2_local();
        }catch(\Exception $e){
            var_dump($e);
        }
    }

    





}
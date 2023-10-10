<?php
//declare (strict_types = 1);

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
use app\index\controller\HttpClass;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class CheckMalicious2Local extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('app\command\checkmalicious2local')
            ->setDescription('the app\command\checkmalicious2local command');
    }

    protected function execute(Input $input, Output $output)
    {

        try{
            //这个是检查check_malicious_status为1的数据状态，如果源码页面正常就将页面check_malicious_status值设置为2,否则就将其值设置为9,值为9时改为手动处理。
            //数据都正常是值设置为8,需用人工手动逐步的处理，人工审核涉及菠菜、色情、钓鱼等链接
            //这个是在web服务器上直接运行的脚本，无需通过api接口
            //check_malicious_2_local 方法是通过api接口传递数据，可以在本地运行脚本
            while(true){
                $api_url_get = Config::get("app.admin_url")."/index.php/index/http_api/check_malicious_2_get_api?api_password=".Config::get("app.api_password");
                $api_url_post = Config::get("app.admin_url")."/index.php/index/http_api/check_malicious_2_post_api";

                $httpClass = new HttpClass($this->app);
                $html = $httpClass->get($api_url_get,Config::get("app.api_password"));
                //echo $html;
                $data = json_decode($html,true);
                //var_dump($data);


                if($data['status_code'] == 0){
                    echo ">> ".date("Y-m-d H:i:s ")." {$data['message']}".", sleep(20)\n";
                    sleep(20);
                    continue;
                }else{
                    $data = $data["data"];

                    $itemid = $data['itemid'];
                    $url = $data['url'];

                    echo ">> itemid:{$itemid} url:{$url}\n";

                    //---------------  url白名单，只要包含此url，直接略过    begin-------------------------
                    $url_white_list = ["\.office\.com/","1drv\.ms/","\.amazon\.com\.br/","\.amazon\.co\.uk/","\.azure\.com/","\.bing\.com/","\.powerbi\.com/","\.microsoft\.com/","\.twitter\.com/","//youtube\.com/","\.tiktok\.com/","\.mediafire\.com/","//shope\.ee/","//shopee\.com\.br/","\.whatsapp\.com/","\.spotify\.com/","\.google\.com\.\\w{1,3}/","\.mycloud\.com/","www\.linkedin\.com/","\.apple\.com/","\.google\.com/","forms\.gle/","\.adobe\.com/","\.goo.gl/","gay","boy","\.booking\.com/","\.instagram\.com/","\.amazon\.com/","\.amazon\.\w{2,3}/","\.live\.com/","amzn\.to/","youtube\.com/","youtu\.be/","\.canva\.com/","//wa\.me/","line\.me/","netlify\.app","//vk\.sv/","//shopee\.","\.sharepoint\.com/","facebook\.com/","twitter\.com/","tokopedia\.com/","gofood\.link","snapchat\.com/","\.bbc\.com/","\.wasap\.my/","atfx\.com/","//wa\.link/","//wa\.me/","//mega\.nz/","goo\.gl/","element14\.com/","parceiromagalu\.com\.br","magazinevoce\.com\.br","quantummetal\.com/","instagram\.com/","1688\.com/","wasap\.my","blibli\.com/","pixabay\.com/","tiktok\.com/","flexispot\.my/","\.zoom\.us/","aliexpress\.com/","\.yahoo\.com/","lazada\.com\.my/","\.shopify\.com/","\.reddit\.com/","github\.com/","iplogger\.org","\.ibm\.com/","dropbox\.com/","todayonline\.com/","wikipedia\.org","zoom\.us","pinterest\.com/","pinterest\.ph/","\.cnet\.com/","nypost\.com/","\.gov\.[a-zA-Z]{2,3}/","\.edu\.[a-zA-Z]{2,3}/","\.edu/","\.gov/","/discord\.com/","/discord\.gg/","\.bbc\.co\.uk/","\.bbc\.com/","\.scoi\.com/","\.linkedin\.com/","\.samsung\.com/","pin\.it/","\.apollo\.io/","\.stripe\.com/","\.nytimes\.com/","\.nokia\.com/","\.wikivoyage\.org/","\.github\.io/","/opensea\.io/","\.chess\.com/","\.weibo\.cn/","\.weibo\.com/","/study\.com/","/file\.io/","www\.instagram\.com/","\.xiaohongshu\.com/","\.indeed\.com/","/shp\.ee/","www\.nbcnews\.com","\.airbnb\.com","\.lazada\.com",".alibaba\.com","fb\.watch","www\.ebay\.com","/t.me/","/m.me/","www\.ted\.com/","\.wikibooks\.org/","\.history\.com","www\.cbsnews\.com","\.meta\.com","\.usatoday\.com/","\.fiverr\.com","www\.jobstreet\.com","\.sciencedirect\.com/","www\.dictionary\.com/","\.wattpad\.com/","\.alibabacloud\.com/","\.roblox\.com/","www\.mi\.com/","www\.sony\.com","www\.agoda\.com/","www\.who\.int/","shein\.com/","\.agoda\.com/","//belia\.org\.my/","\.tmall\.com/","\.spotify\.com","\.taobao\.com/","\.wamda\.com/","/gifft\.me/","\.washingtonpost\.com/","\.g2\.com/","soundcloud\.com/","\.trip\.com/","\.msn\.com/","\.shop\.com/","\.douyin\.com/","/time\.com/","\.qq\.com/","pdf\.sciencedirectassets\.com/","\.cisco\.com/","\.researchgate\.net/","www\.w3schools\.com/","\.pinduoduo\.com/","//netflix\.com/","googleusercontent","//xhslink\.com/","//vk\.com/","\.studocu\.com/","\.tmc\.or\.th/","\.icloud\.com/","\.nike\.com/","padlet\.com/","maps\.google\.com","\.bilibili\.tv/","//lin.ee/","\.mozilla\.org/","//wetransfer\.com/","//medium\.com/","\.lazada\.co\.th/","//we\.tl/","\.lg\.com/","aliexpress\.us/","\.walmart\.com/","//fb\.me/","google\.co\.th/url","oraclecloud\.com/","www\.google\.ca/","zalo\.me","\.kakao\.com","doi\.org"];

                    echo ">> url_white_list.\n";






                    // --------------------- adsense_switch begin --------------------------------
                    //当url包含数组中某个值时说明可能是违规url，将adsense_switch值设置为1，让adsense爬虫爬自动爬取特殊处理随机生成的URL
                    //canva.com出现抓取工具错误，google adsense广告抓取工具无法访问到这个站点，将此站点设置为1
                    //此站点蜘蛛访问时出现了，Please update your browser
                    //sharepoint.com会自动跳转微软的官方登陆，adsense后台会提示出现错误(此站点使用蜘蛛访问会返回403错误)
                    //"\.qq\.com/"中文内容可能会拉底adsense单价，将adsense_switch设置为1
                    $white_adsense_switch = ["\.repl\.co","blogspot\.com","zhmgd\.com","xvideos","xnxx\.com","pron","pronhub","duckdns\.org","southeastplace\.com","miarroba\.com","2ndirectglobesec\.ga","sites\.google\.com","ascearcesh\.com","yolasite\.com","dynadot\.com","ourcasinobat\.xyz","interpretcambodia\.com","insights\.genex\.co\.za","shorturl\.at","gg\.gg","is\.gd","v\.gd","e\.vg","cutt\.ly","bit\.ly","tinyurl\.com","kiwifarms\.ru","fastly\.net","inmotionhosting\.com","o\.vg","fnote\.net","\.app\.link","\.teste\.website","vioflix\.xyz","hanime\.tv","ya\.co\.ve","crederes\.xyz","instahacker\.org","hspogxbk\.space","flirtmood\.life","llantasmex\.com","\.co\.vu","clicklo\.life","mylx\.io","9k\.gg","ngrok\.io","bitly\.lc","3c5\.com","shortlink\.biz","//s\.id/","findiover\.net","netlify\.app","linkfree\.me","vitafrute\.com","dultogelhkpools\.com","wixsite\.com","atinglocal\.life","appurl\.io","xhamster2\.com","rapidl\.ink","g12\.br","rb\.gy","bustymets\.com","pornhub\.com","wbox4\.cc","shorten.asia","shre.ink","000webhostapp\.com","owsm\.ly","\.yam\.com","mobilpark\.biz","freshtools\.net","v\.ht","grabify\.link","n5\.gs","8n\.gs","verlink\.co","rebrand\.ly","page\.link","eb4\.us","dynnamn\.ru","cuturl\.cc","b\.link","irtsgood\.life","ularflirt\.life","rich9game\.com","bit\.do","nuly\.do","builderallwppro\.com","lesbianpornvideos\.com","gayboystube\.com","pornpics.com","jav\.com","nekobet99\.art","koin\.yoga","bemyasforever\.com","findmethere\.biz","koji\.to","clickbank\.net","invol\.co","bitly\.ws","encurtaa\.com","facabook\.site","earnmoney-j45ag\.buzz","4track\.ru","orll\.cc","4ty\.me","lc\.cx","shortest\.link","\\d+\.com/register","kdinmobiliaria\.net","/register\?ref=","url123\.click","urls\.by","goo.by","sub2unlock\.me","rich9game\.com","palatlaldate\.com","prodlgiousdates\.com","99tg\d+\.com","bwwetpo\.ws","ph\w+\.bet","/link\.php\?member=","iplogger\.com","sex","porn","asia","linktr\.ee","google\.com/search","\.bet","\.club","\.win","\.link","\.page","linki\.ee","cloudfront\.net","\.vip","\.xyz","hub\.com","\.top","fuck","girls","love","cash","loving","blogspot\.com","sites\.google\.com","clickbank\.net","sck\.io","online","wixsite\.com","dynadot\.com","video","\.xxx","hentai","boss","\.life","movie","\\d+\.com","Brazzers","xhamster","spankbang","kiss","erome","xnxx","luck","hot","gold","bet","live","fux\.com/","\w+win\.com","\.app/","artistsnclients","playabledownload","ubox\d+\.","qa6drama\.com","duckduckgo\.com/","gaming","urlis\.net","t\.ly/","xhday\.com/","\.sbotop\.com/","\.trycloudflare\.com","h5app","mountainfiles\.com","www\.google\.com/url\?","winbox","javhd","\d+vip\.com/","chrome-extension://","javgg\.net/","direct\.me/","1337x\.to","tiny\.one","\.fun/","\w+\d+\.net","\.atfx-gm\.com","surl\.li/","\.yandex\.ru/","/register","pluto\.tv","cosplay","canva\.com","\.proquest\.com/","/we\.tl/","\.gov\.my/","sharepoint\.com","\.bio/","m5\.gs","5n\.gs","discovertoday\.co/","tokyo","pinkcherry","instabio.cc","\.qq\.com/","\.tv/","anime","/bnc\.lt/","optidownloader\.com","\.eu\.org/","/w\.tt/","bitcoin","\.cat/","ganknow\.com/","\.php\?","/watch","jii\.li/","voyeurhub","downblouse","stars","megaparseonalse\.com/","xxx","basketball","football","bicolink\.net","kuza\.me/","\.pinduoduo\.com/","\?code=","&code=","\.gstatic\.com/","saganagame\.com/","//xhslink\.com/","//vk\.com/","//g\.co/","//pslk\.net/","\.onelink\.me/","\.niets\.or\.th","referral=","google\.es","\.site/","\.addtoany\.com/","/v/","\.mihoyo\.com/","unfamilllardate\.net","utm_source=","adsaffable\.com/","/click\?","link","letmegooglethat\.com","lnk\.to","/t\.co/","\w+\d+\.org","//cdn\d*\.","[a-z]+[0-9]+\.[a-z]{1,3}/","wikihow\.com","google\.co\.th/url","/anonfiles\.com/","\.pw","\.site","wecardmeta\.com","win\.","tracking\.","url-x\.it","noodou\.com","digistore24\.com","world\d+","heylink\.me","show.php\?","ashemaletube","pnrtscr","\.xyz/","adfoc\.us","docs\.google\.com","\.top/","/register","ownblouse\.com","anime-h\.com","1337xx.to"];






                    $adsense_switch_num = 0;
                    foreach($white_adsense_switch as $adsense_item){
                        if(preg_match_all("#".$adsense_item."#i",$url)){
                            $adsense_switch_num= 1;
                            break;
                        }else{
                            $adsense_switch_num = 0;
                        }
                    }
                    echo ">> adsense_switch_num:".$adsense_switch_num."\n";
                    // --------------------- adsense_switch end --------------------------------





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
                            "adsense_switch" => $adsense_switch_num,
                            "api_password" => Config::get("app.api_password")
                        ];
                        //var_dump($post_data);

                    
                        $response = $httpClass->post($api_url_post,$post_data,Config::get("app.api_password"));
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


                    echo ">> url_white_word.";

                    if($url_white_word_num == 1){
                        echo ">> url_white_word_num:{$url_white_word_num}\n";
                        

                        $post_data = [
                            "itemid" => $itemid,
                            "check_malicious_url_value" => 2,
                            "adsense_switch" => $adsense_switch_num,
                            "api_password" => Config::get("app.api_password")
                        ];
                        //var_dump($post_data);

                    
                        $response = $httpClass->post($api_url_post,$post_data,Config::get("app.api_password"));
                        echo $response."\n";


                        continue;
                    }

                    echo ">> http.";
                    $httpClass = new HttpClass($this->app);
                    echo ">> http class.";
                    //$url = "https://www.usnews.com/news/articles/2008/01/17/the-new-deal-sealed-the-deal";
                    //echo $url;
                    try{
                        //请求url，获取页面源码
                        try{
                            $client = new \GuzzleHttp\Client(['cookies' => true]);


                            $onRedirect = function(
                                RequestInterface $request,
                                ResponseInterface $response,
                                UriInterface $uri
                            ) {
                                echo 'Redirecting! ' . $request->getUri() . ' to ' . $uri . "\n";
                            };



                            $res = $client->request('GET',$url,['headers' => [
                                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36',
                                    'Accept-Encoding' => 'gzip, deflate, br',
                                    'accept-language' => 'en-US,en;q=0.9',
                                    'accept'=> 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                                    'accept-encoding'=> 'gzip, deflate, br',
                                    'accept-language'=> 'en-US,en;q=0.9,fr-FR;q=0.8,fr;q=0.7,id;q=0.6,zh-CN;q=0.5,zh;q=0.4,ru;q=0.3,ar;q=0.2,de-DE;q=0.1,de;q=0.1,vi;q=0.1,fa;q=0.1,tr;q=0.1,cs;q=0.1,pl;q=0.1,pt;q=0.1,be;q=0.1,ja;q=0.1,fi;q=0.1,da;q=0.1,ms;q=0.1,uk;q=0.1,sv;q=0.1,no;q=0.1,hu;q=0.1,el;q=0.1,nl;q=0.1,th;q=0.1,sk;q=0.1,es;q=0.1,ko;q=0.1,it;q=0.1,zh-TW;q=0.1',
                                    'cache-control'=> 'no-cache',
                                    'pragma'=> 'no-cache',
                                ],
                                'allow_redirects' => [
                                    'max' => 10,        // allow at most 10 redirects.
                                    'strict'          => true,      // use "strict" RFC compliant redirects.
                                    'referer'         => true,      // add a Referer header
                                    'protocols'       => ['https','http'], // only allow https URLs
                                    'on_redirect'     => $onRedirect,
                                    'track_redirects' => true
                                ],
                                'connect_timeout' => 8,
                                'timeout' => 8,
                                'debug' => true
                            ]);
                            //var_dump($res);
                            echo ">> http client.\n";
                           
                        }catch(\Exception $e){
                            dump($e);
                            echo ">> itemid:".$itemid." set check_malicious_status=8.\n";
                            $post_data = [
                                "itemid" => $itemid,
                                "check_malicious_url_value" => 8,
                                "adsense_switch" => $adsense_switch_num,
                                "api_password" => Config::get("app.api_password")
                            ];
                            //var_dump($post_data);

                        
                            $response = $httpClass->post($api_url_post,$post_data,Config::get("app.api_password"));
                            echo $response."\n";
                            
    
                            continue;
                        }
                        // ----------------      判断headers是否是text/html类型的 begin----------------
                        //获取headers
                        $respone_headers = $res->getHeader("content-type");
                        echo ">> content-type:".$respone_headers[0]."\n";
                        //如果html中匹配不到text就将status设置为2
                        if(!preg_match("#text#i",$respone_headers[0])){
                            echo ">> itemid:".$itemid." content-type is not text/html, set check_malicious_status=8.\n";
                            $post_data = [
                                "itemid" => $itemid,
                                "check_malicious_url_value" => 8,
                                "adsense_switch" => $adsense_switch_num,
                                "api_password" => Config::get("app.api_password")
                            ];
                            //var_dump($post_data);

                            $response = $httpClass->post($api_url_post,$post_data,Config::get("app.api_password"));
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

                        $black_keyword = ["amazon","microsoft","twitter","account","sign","login","Verifi","locked","page","admin","document","paypal"];

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
                                "adsense_switch" => $adsense_switch_num,
                                "api_password" => Config::get("app.api_password")
                            ];
                            //var_dump($post_data);

                            $response = $httpClass->post($api_url_post,$post_data,Config::get("app.api_password"));
                            echo $response."\n";


                        }else{
                            echo ">> itemid:".$itemid." set check_malicious_status=8.\n";
                            $post_data = [
                                "itemid" => $itemid,
                                "check_malicious_url_value" => 8,
                                "adsense_switch" => $adsense_switch_num,
                                "api_password" => Config::get("app.api_password")
                            ];
                            //var_dump($post_data);

                            $response = $httpClass->post($api_url_post,$post_data,Config::get("app.api_password"));
                            echo $response."\n";

                        }


                            //sleep(10);
                    }catch(\Exception $e){
                        dump($e);
                        echo ">> itemid:".$itemid." set check_malicious_status=8.\n";
                        $post_data = [
                            "itemid" => $itemid,
                            "check_malicious_url_value" => 8,
                            "adsense_switch" => $adsense_switch_num,
                            "api_password" => Config::get("app.api_password")
                        ];
                        //var_dump($post_data);

                        
                        $response = $httpClass->post($api_url_post,$post_data,Config::get("app.api_password"));
                        echo $response."\n";

                        continue;
                    }
                }

            }
        }catch(\Exception $e){
            dump($e);

        }

    }
}

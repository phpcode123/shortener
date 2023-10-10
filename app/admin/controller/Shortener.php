<?php
namespace app\admin\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Cache;
use think\facade\Request;
use think\facade\Config;
use think\facade\Session;



class Shortener  extends BaseController
{   

    //控制器中间件，执行顺序：全局中间件->应用中间件->路由中间件->控制器中间件
    protected $middleware = ['\app\middleware\CheckLogin::class'];



    public function add(){
    
        View::assign('username',Session::get('username'));
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
 


        return View::fetch('/Shortener/shortener_add');
    }


    public function addpost(){

        $data = Request::post();

        if(Db::table('tp_shortener')->strict(false)->data($data)->insert()){
            $this->success("Data add success.",$_SERVER["HTTP_REFERER"],1);
        }else{
            $this->error("Data add fail.",$_SERVER["HTTP_REFERER"],2);
        }
       
    }


    public function list(){

        $sort = Request::param("sort") ?? "";

        $domain_data = Db::table("tp_domain")->order("itemid","asc")->select();

        //var_dump($domain_data);

        if($sort == ""){
            $list = Db::table('tp_shortener')->order('itemid', 'desc')->paginate([
                'list_rows' => Config::get('app.admin_page_num'),
                'path'  => "/".Config::get("app.admin_path").'/shortener/list'
            ]);
        }else{
            $list = Db::table('tp_shortener')->order('hits', 'desc')->paginate([
                'list_rows' => Config::get('app.admin_page_num'),
                'path'  => "/".Config::get("app.admin_path").'/shortener/list',
                'query' => Request::only(["sort"])
            ]);
        }
        

        //malicious url status 
        $malicious_url_key = Config::get("app.redis_prefix")."malicisou_url_status";
        $malicious_url_status_time = Cache::get($malicious_url_key ,"0");
        //echo $malicious_url_status_time."   ";
        //echo time();
        View::assign("malicious_url_status_time",$malicious_url_status_time);
        View::assign("now_timestamp",time());


        View::assign("domain_data",$domain_data);
        View::assign('username',Session::get('username'));
        View::assign('list',$list);
        View::assign('pc_url',Config::get('app.admin_url'));
        View::assign('admin_url',Config::get('app.admin_url'));
        View::assign('admin_path',Config::get('app.admin_path'));

        return View::fetch('/Shortener/shortener_list');       

    }

    public function url_list(){

        $sort = Request::param("sort") ?? "";

        $domain_data = Db::table("tp_domain")->order("itemid","asc")->select();

        //var_dump($domain_data);

        if($sort == ""){
            $list = Db::table('tp_shortener')->order('itemid', 'desc')->paginate([
                'list_rows' => Config::get('app.admin_page_num'),
                'path'  => "/".Config::get("app.admin_path").'/shortener/url_list'
            ]);
        }else{
            $list = Db::table('tp_shortener')->order('hits', 'desc')->paginate([
                'list_rows' => Config::get('app.admin_page_num'),
                'path'  => "/".Config::get("app.admin_path").'/shortener/url_list',
                'query' => Request::only(["sort"])
            ]);
        }
        

        //malicious url status 
        $malicious_url_key = Config::get("app.redis_prefix")."malicisou_url_status";
        $malicious_url_status_time = Cache::get($malicious_url_key ,"0");
        //echo $malicious_url_status_time."   ";
        //echo time();
        View::assign("malicious_url_status_time",$malicious_url_status_time);
        View::assign("now_timestamp",time());


        View::assign("domain_data",$domain_data);
        View::assign('username',Session::get('username'));
        View::assign('list',$list);
        View::assign('pc_url',Config::get('app.admin_url'));
        View::assign('admin_url',Config::get('app.admin_url'));
        View::assign('admin_path',Config::get('app.admin_path'));

        return View::fetch('/Shortener/shortener_url_list');       

    }


    public function edit(){

        $itemid = Request::param('itemid'); 
        $data = Db::table('tp_shortener')->where('itemid','=',$itemid)->select();


        View::assign('username',Session::get('username'));
        View::assign('data',$data);
        View::assign('itemid',$itemid);
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/Shortener/shortener_edit');      

    }

    public function editPost(){
        $itemid = Request::param("itemid");
        $data = Request::post();
    

        

        if(Db::table('tp_shortener')->strict(false)->where('itemid',"=",$itemid)->update($data)){
            $this->success("Data edit success.",$_SERVER["HTTP_REFERER"],1);
        }else{
            $this->error("Data edit fail.",$_SERVER["HTTP_REFERER"],2);
        }


       

    }


    public function query(){


        View::assign('username',Session::get('username'));
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/Shortener/shortener_query');
    }

    public function querypost(){
        $short_str = Request::param("short_str");

        if(strlen($short_str) == 0){
            $this->error("short_str is empty!",$_SERVER["HTTP_REFERER"],2);
        }

        //判断字符串的长度，从而查询对应的数据库字段
        switch(strlen($short_str)){
            case 6:
                $table_column = "short_url";
                break;
            case 7:
                $table_column = "short_url_7";
                break;
            case 8:
                $table_column = "short_url_8";
                break;
            default:
                $table_column = "short_url";
            
        }



        $list = Db::table('tp_shortener')->where($table_column,"=",$short_str)->order('itemid','desc')->paginate([
            'list_rows' => Config::get('app.admin_page_num'),
            'path'     => "/".Config::get("app.admin_path").'/shortener/querypost',
            'query'    => Request::param(),
        ]);

        $domain_data = Db::table("tp_domain")->order("itemid","asc")->select();
        View::assign('domain_data', $domain_data);
        
        View::assign('list',$list);
        View::assign('username',Session::get('username'));
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/Shortener/shortener_query_list');
    }



    public function querypost_like(){
        $like_str = Request::param("like_str");

        if(strlen($like_str) == 0){
            $this->error("like_str is empty!",$_SERVER["HTTP_REFERER"],2);
        }


        $list = Db::query("select * from tp_shortener where url like \"%".$like_str."%\" order by itemid desc;");


        $domain_data = Db::table("tp_domain")->order("itemid","asc")->select();
        View::assign('domain_data', $domain_data);


        //click total
        $total = 0;
        foreach($list as $item){
            $total += $item["hits"];
        }
        View::assign("total",$total);
        
        View::assign('list',$list);
        View::assign('username',Session::get('username'));
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/Shortener/query_list_like');
    }


    public function querypost_in(){
        $in_str = Request::param("in_str");

        //前端默认提交的字符串是使用逗号或者空格隔开的，要替换字符串拼接SQL
        //如：select * from tp_shortener where short_url in("WHJidG","QkN4WD","MWVqbG","eGNoRF","eE1qNT");
        $in_str = preg_replace("# #i",",",$in_str);
        $in_str = preg_replace("#，#i",",",$in_str);
        $in_str = preg_replace("#,#i","\",\"",$in_str);

    
        if(strlen($in_str) == 0){
            $this->error("in_str is empty!",$_SERVER["HTTP_REFERER"],2);
        }
        $query_sql = "select * from tp_shortener where short_url in (\"".$in_str."\");";


        $list = Db::query($query_sql);

        //adsense_update为0的值，即设置为1时adsense spider还未爬取,只有爬取的链接才会更新此时间戳
        $adsense_update_nums = 0;
        for($i=0;$i<count($list);$i++){
            if($list[$i]['adsense_update'] == 0){
                $adsense_update_nums += 1;
            }
        }
        View::assign("adsense_update_nums",$adsense_update_nums);

        $domain_data = Db::table("tp_domain")->order("itemid","asc")->select();
        View::assign('domain_data', $domain_data);


        
        View::assign("total",count($list));
        View::assign('list',$list);
        View::assign('username',Session::get('username'));
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/Shortener/query_list_in');
    }




    public function delete(){
        $itemid = Request::param("itemid");

        

        if(Db::table('tp_shortener')->where('itemid',$itemid)->delete()){
            $this->success("Data delete success.","/".Config::get('app.admin_path')."/shortener/list",1);
        }else{
            $this->error("Data delete fail.","/".Config::get('app.admin_path')."/shortener/list",2);
        }
    }



    public function  area_analysis(){

        //分析时间，0时为当天时间，1为昨天，以此类推
        $day = Request::param("day") ? Request::param("day") : "0";
        
        $begin_timstamp_day = strtotime(date('Y-m-d').'00:00:00');
        $end_timstamp_day = strtotime(date('Y-m-d').'23:59:59');

        $begin_timestamp = $begin_timstamp_day - 60*60*24*$day;
        $end_timestamp = $end_timstamp_day - 60*60*24*$day;


        $data = Db::table("tp_shortener")->where("timestamp",">",$begin_timestamp)->where("timestamp","<",$end_timestamp)->order("itemid","desc")->select();



        $analysis_array = array();
        //用于统计pc和m端的占比,以及根据IP统计用户数量
        $is_pc_num = 0;
        $is_m_num = 0;
        $pc_user = array();
        $m_user = array();
        foreach($data as $item){
            $country = $item['country'];
            if(array_key_exists($country,$analysis_array)){
                //获取key=>value的值，并将value自增1
                
                $key_value = $analysis_array[$country] + 1;
                $analysis_array[$country] = $key_value;
            }else{
                
                $analysis_array[$country] = 1;
            }

            $user_ip = $item['remote_ip'];
            //pc和移动端的统计
            if($item["is_pc"] == 1){
                $is_pc_num += 1;
         
                if(!in_array($user_ip,$pc_user)){
                    array_push($pc_user, $user_ip);
                }

            }else{
                $is_m_num +=1;
                if(!in_array($user_ip,$m_user)){
                    array_push($m_user, $user_ip);
                }
            }

        }
        

        $total = 0;
        foreach($analysis_array as $k=>$v){
            $total += $v;
        }
        
        //echo ">> Total:{$total}\n";
        arsort($analysis_array);
        //var_dump($analysis_array);
        
        View::assign("is_pc_num",$is_pc_num);
        View::assign("is_m_num",$is_m_num);
        View::assign("pc_user",count($pc_user));
        View::assign("m_user",count($m_user));
        View::assign("list",$analysis_array);
        View::assign("total",$total);
        View::assign("username",Session::get('username'));
        View::assign("admin_path",Config::get('app.admin_path'));
        View::assign("pc_url",Config::get('app.admin_url'));
        return View::fetch('/Shortener/area_analysis');

    }

}

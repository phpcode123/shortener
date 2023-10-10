<?php
namespace app\admin\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Cache;
use think\facade\Request;
use think\facade\Config;
use think\facade\Session;



class Malicious  extends BaseController
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
            $this->success("Data edit success.",$_SERVER["HTTP_REFERER"],1);
        }else{
            $this->error("Data edit fail.",$_SERVER["HTTP_REFERER"],2);
        }
       
    }


    public function list(){


    

        $check_malicious_status= Request::param("check_malicious_status") ? Request::param("check_malicious_status") : "";

        if(empty($check_malicious_status)){
            abort(404,"Please check parameter");
        }
 

        $list = Db::table('tp_shortener')->where('check_malicious_status', $check_malicious_status)->order("itemid","desc")->paginate([
            'list_rows' => Config::get('app.admin_page_num'),
            'path'  => "/".Config::get("app.admin_path").'/malicious/list',
            'query' => Request::only(["check_malicious_status"])
        ]);
    
        $domain_data = Db::table("tp_domain")->order("itemid","asc")->select();
        
        //check_malicious_2_local 运行监控
        $check_malicious_2_local = Config::get("app.redis_prefix")."check_malicious_2_post";
        $check_malicious_2_local_status_time = Cache::get($check_malicious_2_local ,"0");
        View::assign("check_malicious_2_local_status_time",$check_malicious_2_local_status_time);
        View::assign("now_timestamp",time());


        View::assign("check_malicious_status",$check_malicious_status);

        View::assign("domain_data",$domain_data);
        View::assign('username',Session::get('username'));
        View::assign('list',$list);
        View::assign('pc_url',Config::get('app.admin_url'));
        View::assign('admin_url',Config::get('app.admin_url'));
        View::assign('admin_path',Config::get('app.admin_path'));


        //根据check_malicious_status值来匹配不同模板，人工手动逐条审核的值为8，单独定义一个模板
        switch($check_malicious_status){
            case "8":
                return View::fetch('/Malicious/malicious_list_status_8');
                break;
            case "9":
                return View::fetch('/Malicious/malicious_list_status_8');
                break;
            default:
                return View::fetch('/Malicious/malicious_list'); 

        }
              

    }



    public function is_404(){


    

        $domain_data = Db::table("tp_domain")->order("itemid","asc")->select();

        //var_dump($domain_data);
        $param = Request::param("num") ? Request::param("num") : 0;
        
        if($param != 0){
            $list = Db::table('tp_shortener')->where("is_404",$param)->order('itemid', 'desc')->paginate([
                'list_rows' => Config::get('app.admin_page_num'),
                'path'  => "/".Config::get("app.admin_path").'/malicious/is_404',
                'query' => Request::only(["num"])
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

        return View::fetch('/Malicious/malicious_list_404');       

    }

    public function display_ad(){
        $domain_data = Db::table("tp_domain")->order("itemid","asc")->select();

        //var_dump($domain_data);
        $param = Request::param("num") ? Request::param("num") : 0;
        
        if($param != 0){
            $list = Db::table('tp_shortener')->where("display_ad",$param)->order('itemid', 'desc')->paginate([
                'list_rows' => Config::get('app.admin_page_num'),
                'path'  => "/".Config::get("app.admin_path").'/malicious/display_ad',
                'query' => Request::only(["num"])
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

        return View::fetch('/Malicious/malicious_list_display_ad');       

    }

    //v2.01版本后全面停用adsense_switch
    public function adsense_switch(){
        $domain_data = Db::table("tp_domain")->order("itemid","asc")->select();

        //var_dump($domain_data);
        $param = Request::param("num") ? Request::param("num") : 0;
        
        if($param != 0){
            $list = Db::table('tp_shortener')->where("adsense_switch",$param)->order('itemid', 'desc')->paginate([
                'list_rows' => Config::get('app.admin_page_num'),
                'path'  => "/".Config::get("app.admin_path").'/malicious/adsense_switch',
                'query' => Request::only(["num"])
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

        return View::fetch('/Malicious/malicious_list_adsense_switch');       

    }




    public function update(){

        $check_malicious_status= Request::param("check_malicious_status") ? Request::param("check_malicious_status") : "";
        
        if(empty($check_malicious_status)){
            abort(404,"Please check parameter");
        }

        if(Db::table("tp_shortener")->where("check_malicious_status","=",$check_malicious_status)->update(["check_malicious_status"=>2])){
            $this->success("Update success","/".Config::get("app.admin_path").'/click_analysis/list',1);
        }else{
            $this->error("Update error","/".Config::get("app.admin_path").'/click_analysis/list',2);
        }

    }


    public function update_is_404(){

        $check_malicious_status= Request::param("check_malicious_status") ? Request::param("check_malicious_status") : "";
        
        if(empty($check_malicious_status)){
            abort(404,"Please check parameter");
        }

        $malicious_data = Db::table("tp_shortener")->where("check_malicious_status","=",$check_malicious_status)->select();

        $itemid_list_str = "";
        for($i=0; $i<count($malicious_data); $i++){
            if($i == 0){
                $itemid_list_str = $malicious_data[$i]['itemid'];
            }else{
                $itemid_list_str .= ",".$malicious_data[$i]['itemid'];
            }   
        }


        // echo $itemid_list_str;

        // return;

        if(Db::table("tp_shortener")->where("itemid","in",$itemid_list_str)->update(["is_404"=>9,"check_malicious_status"=>2])){
            $this->success("Update success","/".Config::get("app.admin_path").'/click_analysis/list',1);
        }else{
            $this->error("Update error","/".Config::get("app.admin_path").'/click_analysis/list',2);
        }

    }


    public function check_url(){
        $hits = Request::param("hits") ?? "none";
        $source = Request::param("source") ?? "none";
        if($hits == "none"){
            abort("404","Hits number is null, please check it.");
        }


        $domain_data = Db::table("tp_domain")->order("itemid","asc")->select();



        //如果source不等于1就直接输出url
        if($source != "none"){
            $list = Db::table('tp_shortener')->where("hits",">",$hits)->where("last_access_timestamp",">",time()-7*24*60*60)->where("is_404","=","0")->order('itemid', 'desc')->select();

            foreach($list as $item){
                echo $item["url"].",   ".$item['short_url']."\n";
            }
            return;
        }

        $list = Db::table('tp_shortener')->where("hits",">",$hits)->where("last_access_timestamp",">",time()-7*24*60*60)->where("is_404","=","0")->order('itemid', 'desc')->paginate([
            'list_rows' => Config::get('app.admin_page_num'),
            'path'  => "/".Config::get("app.admin_path").'/malicious/check_url',
            'query' => Request::only(["hits"])
        ]);

        View::assign("hits",$hits);        

        View::assign("domain_data",$domain_data);
        View::assign('username',Session::get('username'));
        View::assign('list',$list);
        View::assign('pc_url',Config::get('app.admin_url'));
        View::assign('admin_url',Config::get('app.admin_url'));
        View::assign('admin_path',Config::get('app.admin_path'));

        return View::fetch('/Malicious/malicious_list_check_url');       

    }

}

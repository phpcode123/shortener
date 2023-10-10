<?php
namespace app\admin\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\Config;
use think\facade\View;


class Referer  extends BaseController
{

    //控制器中间件，执行顺序：全局中间件->应用中间件->路由中间件->控制器中间件
    protected $middleware = ['\app\middleware\CheckLogin::class'];

    
    public function list(){

        $list = Db::table('tp_http_referer')->order('itemid','desc')->paginate([
            'list_rows' => Config::get('app.admin_page_num'),
            'path'     => "/".Config::get("app.admin_path").'/referer/list',
        ]);

        
        View::assign('list',$list);
        View::assign('username',Session::get('username'));
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/Referer/referer_list');
    }

    public function edit(){
        $itemid = Request::param('itemid');
        $data = Db::table('tp_http_referer')->where('itemid',$itemid)->select();
        View::assign('data',$data);
        View::assign('username',Session::get('username'));
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/Referer/referer_edit');
    }

    public function editpost(){
        $data = Request::param();
        if(Db::table('tp_http_referer')->strict(false)->where('itemid',$data['itemid'])->update($data)){
            $this->success("Data edit success.",$_SERVER["HTTP_REFERER"],1);
        }else{
            $this->error("Data edit fail.",$_SERVER["HTTP_REFERER"],2);
        }
    }



    public function query(){


        View::assign('username',Session::get('username'));
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/Referer/referer_query');
    }

    public function querypost(){
        $short_str = Request::param("short_str");

        if(strlen($short_str) == 0){
            $this->error("short_str is empty.",$_SERVER["HTTP_REFERER"],2);
        }

        $list = Db::table('tp_http_referer')->where("short_str","=",$short_str)->order('itemid','desc')->paginate([
            'list_rows' => Config::get('app.admin_page_num'),
            'path'     => "/".Config::get("app.admin_path").'/referer/querypost',
            'query'    => Request::param(),
        ]);

        $total_click = count($list);
        
        View::assign('list',$list);
        View::assign('total_click',$total_click);
        View::assign('username',Session::get('username'));
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/Referer/referer_query_list');
    }
    
    public function delete(){
        $itemid = Request::param('itemid');
        
        if(Db::table('tp_http_referer')->where('itemid',$itemid)->delete()){
            $this->success("Data delete success.",$_SERVER["HTTP_REFERER"],1);
        }else{
            $this->error("Data delete fail.",$_SERVER["HTTP_REFERER"],2);
        }
    }

    public function referer_analysis(){

        //分析时间，0时为当天时间，1为昨天，以此类推
        $day = Request::param("day") ? Request::param("day") : "0";
        
        $begin_timstamp_day = strtotime(date('Y-m-d').'00:00:00');
        $end_timstamp_day = strtotime(date('Y-m-d').'23:59:59');

        $begin_timestamp = $begin_timstamp_day - 60*60*24*$day;
        $end_timestamp = $end_timstamp_day - 60*60*24*$day;


        $data = Db::table("tp_http_referer")->where("timestamp",">",$begin_timestamp)->where("timestamp","<",$end_timestamp)->order("itemid","desc")->select();



        $analysis_array = array();

        foreach($data as $item){
            $short_str = $item['short_str'];
            if(array_key_exists($short_str,$analysis_array)){
                //获取key=>value的值，并将value自增1
                
                $key_value = $analysis_array[$short_str] + 1;
                $analysis_array[$short_str] = $key_value;
            }else{
                
                $analysis_array[$short_str] = 1;
            }
        }
        

        $total = 0;
        foreach($analysis_array as $k=>$v){
            $total += $v;
        }
        
        //echo ">> Total:{$total}\n";
        arsort($analysis_array);
        //var_dump($analysis_array);
        
        
        View::assign('list',$analysis_array);
        View::assign('total',$total);
        View::assign('username',Session::get('username'));
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/Referer/referer_analysis');

    }


    public function  area_analysis(){

        //分析时间，0时为当天时间，1为昨天，以此类推
        $day = Request::param("day") ? Request::param("day") : "0";
        
        $begin_timstamp_day = strtotime(date('Y-m-d').'00:00:00');
        $end_timstamp_day = strtotime(date('Y-m-d').'23:59:59');

        $begin_timestamp = $begin_timstamp_day - 60*60*24*$day;
        $end_timestamp = $end_timstamp_day - 60*60*24*$day;


        $data = Db::table("tp_http_referer")->where("timestamp",">",$begin_timestamp)->where("timestamp","<",$end_timestamp)->order("itemid","desc")->select();



        $analysis_array = array();

        foreach($data as $item){
            $country = $item['country'];
            if(array_key_exists($country,$analysis_array)){
                //获取key=>value的值，并将value自增1
                
                $key_value = $analysis_array[$country] + 1;
                $analysis_array[$country] = $key_value;
            }else{
                
                $analysis_array[$country] = 1;
            }
        }
        

        $total = 0;
        foreach($analysis_array as $k=>$v){
            $total += $v;
        }
        
        //echo ">> Total:{$total}\n";
        arsort($analysis_array);
        //var_dump($analysis_array);
        
        
        View::assign('list',$analysis_array);
        View::assign('total',$total);
        View::assign('username',Session::get('username'));
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/Referer/area_analysis');

    }
}

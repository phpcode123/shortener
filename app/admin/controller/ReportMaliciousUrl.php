<?php
namespace app\admin\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Session;



class ReportMaliciousUrl  extends BaseController
{   

    //控制器中间件，执行顺序：全局中间件->应用中间件->路由中间件->控制器中间件
    protected $middleware = ['\app\middleware\CheckLogin::class'];




    public function list(){
  
        $status = Request::param('status');

        $list = Db::table('tp_report_malicious_url')->where('status',"=",$status)->order('itemid', 'desc')->paginate([
            'list_rows' => Config::get('app.admin_page_num'),
            'path'  => "/".Config::get("app.admin_path").'/report_malicious_url/list',
            'query' => Request::only(['status'])
        ]);
     
        

        View::assign('username',Session::get('username'));
        View::assign('list',$list);
        View::assign('pc_url',Config::get('app.admin_url'));
        View::assign('admin_url',Config::get('app.admin_url'));
        View::assign('admin_path',Config::get('app.admin_path'));

        return View::fetch('/ReportMaliciousUrl/report_list');       

    }


    public function read(){
        if(Db::table('tp_report_malicious_url')->strict(false)->where('status',"=",0)->update(["status" => 1])){
            $this->success("Status set success.",$_SERVER["HTTP_REFERER"],1);
        }else{
            $this->error("Status set fail.",$_SERVER["HTTP_REFERER"],2);
        }    

    }


    public function edit(){

        $itemid = Request::param('itemid'); 
        $data = Db::table('tp_report_malicious_url')->where('itemid','=',$itemid)->select();


        View::assign('username',Session::get('username'));
        View::assign('data',$data);
        View::assign('itemid',$itemid);
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/ReportMaliciousUrl/report_edit');      

    }

    public function editPost(){
        $itemid = Request::param("itemid");
        $data = Request::post();
    

        

        if(Db::table('tp_report_malicious_url')->strict(false)->where('itemid',"=",$itemid)->update($data)){
            $this->success("Edit success.",$_SERVER["HTTP_REFERER"],1);
        }else{
            $this->error("Edit fail.",$_SERVER["HTTP_REFERER"], 2);
        }


       

    }

    public function delete(){
        $itemid = Request::param("itemid");


        if(Db::table('tp_report_malicious_url')->where('itemid',$itemid)->delete()){
            $this->success("Delete success.","/".Config::get('app.admin_path')."/report_malicious_url/list",1);
        }else{
            $this->error("Delete fail.","/".Config::get('app.admin_path')."/report_malicious_url/list",2);
        }
    }

}

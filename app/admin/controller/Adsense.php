<?php
namespace app\admin\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Session;
use think\facade\Config;


class Adsense  extends BaseController
{
    
    //控制器中间件，执行顺序：全局中间件->应用中间件->路由中间件->控制器中间件
    protected $middleware = ['\app\middleware\CheckLogin::class'];



    public function add(){
        View::assign('username',Session::get('username'));
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/Adsense/adsense_add');
    }


    public function addpost(){
        $data = Request::post();
        if(Db::table('tp_adsense')->strict(false)->data($data)->insert()){
            $this->success("Data add success.",$_SERVER["HTTP_REFERER"],1);
        }else{
            $this->error("Data add fail.",$_SERVER["HTTP_REFERER"],2);
        }
       
    }
    
    public function list(){

        $list = Db::table('tp_adsense')->order('itemid','desc')->paginate([
            'list_rows' => Config::get('app.admin_page_num'),
            'path'     => "/".Config::get("app.admin_path").'/domain/list',
        ]);

        
        View::assign('list',$list);
        View::assign('username',Session::get('username'));
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/Adsense/adsense_list');
    }

    public function edit(){
        $itemid = Request::param('itemid');
        $data = Db::table('tp_adsense')->where('itemid',$itemid)->select();
        View::assign('data',$data);
        View::assign('username',Session::get('username'));
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/Adsense/adsense_edit');
    }

    public function editpost(){
        $data = Request::param();
        if(Db::table('tp_adsense')->strict(false)->where('itemid',$data['itemid'])->update($data)){
            $this->success("Data edit success.",$_SERVER["HTTP_REFERER"],1);
        }else{
            $this->error("Data edit fail.",$_SERVER["HTTP_REFERER"],2);
        }
    }
    
    public function delete(){
        $itemid = Request::param('itemid');
        
        if(Db::table('tp_adsense')->where('itemid',$itemid)->delete()){
            $this->success("Data delete success.",$_SERVER["HTTP_REFERER"],1);
        }else{
            $this->error("Data delete fail.",$_SERVER["HTTP_REFERER"],2);
        }
    }
}

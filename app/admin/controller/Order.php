<?php
namespace app\admin\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Session;
use think\facade\Config;


class Order  extends BaseController
{
    
    //控制器中间件，执行顺序：全局中间件->应用中间件->路由中间件->控制器中间件
    protected $middleware = ['\app\middleware\CheckLogin::class'];


    
    public function list(){

        $success = Request::param("success") ?? 0;
        if($success == 1){

            $list = Db::table('tp_order')->where("success",1)->order('id','desc')->paginate([
                'list_rows' => Config::get('app.admin_page_num'),
                'path'     => "/".Config::get("app.admin_path").'/order/list',
            ]);

        }else{

            $list = Db::table('tp_order')->order('id','desc')->paginate([
                'list_rows' => Config::get('app.admin_page_num'),
                'path'     => "/".Config::get("app.admin_path").'/order/list',
            ]);
        }

        
        View::assign('list',$list);
        View::assign('username',Session::get('username'));
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/Order/order_list');
    }


    
    public function delete(){
        $itemid = Request::param('id');
        
        if(Db::table('tp_order')->where('itemid',$itemid)->delete()){
            $this->success("Data delete success.",$_SERVER["HTTP_REFERER"],1);
        }else{
            $this->error("Data delete fail.",$_SERVER["HTTP_REFERER"],2);
        }
    }
}

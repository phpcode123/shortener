<?php
namespace app\admin\controller;

use app\BaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Session;

class Login extends BaseController
{

    public function login(){
        return View::fetch('/Login/login');
    }


    public function login_post(){
        $username = Request::param('username');
        $password = Request::param('password');
        
        
        if($username == Config::get("app.admin_username") && $password == Config::get("app.admin_password")){
            Session::set('username',$username); 
            $this->success("Login success.",'/'.Config::get('app.admin_path').'/click_analysis/list',1);
        }else{
            $this->error("Login fail，Please check parameter！",$_SERVER["HTTP_REFERER"],2);
        }
    }



    public function logout(){
        if(!Session::has('username')){
            $this->error("Unauthorized, please login first",'/'.Config::get('app.admin_path').'/login/login', 2);
        }else{
            Session::delete('username');
            $this->success("Logout success.",'/'.Config::get('app.admin_path').'/login/login', 1);
        }

    }
}
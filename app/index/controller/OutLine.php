<?php
namespace app\index\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\Request;
use think\facade\Config;


class OutLine extends BaseController
{
    public function index(){
        

        $data = Db::table("tp_shortener")->where("site_id","=","14")->field('url,short_url')->select();


        for($i=0;$i<count($data);$i++){
            echo $data[$i]['url'].",".$data[$i]['short_url'],",title,description\n";
        }

    }

}
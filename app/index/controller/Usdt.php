<?php
namespace app\index\controller;


use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Cache;
use think\facade\Session;

class Usdt extends BaseController
{

    #/usdt/pay

    public function pay(){

        $url_param = Request::param("url") ??  "";
        $money_param = Request::param("money") ??  "";

        if($url_param == "" || $money_param == ""){
            abort(404,"url or money is null");
        }
 
        $url = base64_decode(str_rot13($url_param));
        $money = base64_decode(str_rot13($money_param));

        $otherclass= new Otherclass($this->app);
        $user_ip = $otherclass->get_user_ip();
        
        //--------------------黑名单关键词判断 begin-------------------------
        $black_itemid_array = [];//黑名单状态码
        $black_url_array = Db::table("tp_black_url")->where("success",0)->select();
        //var_dump($black_url_array);
        for($x=0;$x<count($black_url_array);$x++){
            if(preg_match_all('#'.$black_url_array[$x]['pattern'].'#i',$url)){
                array_push($black_itemid_array,$black_url_array[$x]['itemid']);      
            }
        }
        $black_itemid_str = join(",",$black_itemid_array);
        //var_dump($black_itemid_array);

        //--------------------黑名单关键词判断 begin-------------------------



        $hash_id_key = Config::get("app.redis_prefix")."usdt_hash_id_".md5($url."_".$user_ip);

        if(Cache::get($hash_id_key)){
            $hash_id = Cache::get($hash_id_key);
        }else{
            //如果没有获取到hash_id 或者是hash_id已经超时
            $hash_id = strtoupper(substr( md5(time().mt_rand(10000,99999)), 0, 16));
            //设置redis值
            Cache::Set($hash_id_key,$hash_id,Config::get("app.usdt_pay_timeout"));   
        } 

            
        // ------------------------- url跳转 end ----------------------------------

        //订单金额不能重复,,如果有重复就自增0.01
        
        while(true){
            //echo $money;

            $price_data = Db::table("tp_order")->where("price",$money)->where("create_time",">",time()-Config::get("app.usdt_pay_timeout"))->where("success",0)->select();

            

            //var_dump($price_data);
            //echo "123";
            if(count($price_data)>0){
                if(count($price_data) == 1){
                    //有可能是当前的订单，如果不是则自增0.01
                    if($price_data[0]['hash_id'] == $hash_id){
                        break;
                    }else{
                        $money += 0.01;
                    }
                }else{
                    $money += 0.01;
                }
                
            }else{
                break;
            }
        }
        
        $hash_data = Db::table("tp_order")->where("hash_id",$hash_id)->select();
        //var_dump($hash_data);
        //判断数据库中是否有当前的订单hash_id,防止刷新页面后订单号不停的变化
        if(count($hash_data) == 0){
            $insert_data = [
                "username"=>substr($url,0,254),
                "book_id" => $black_itemid_str,
                "type"     => "1",
                "note"     => "UnblockUrl",
                "price"    => $money,
                "hash_id"   => $hash_id,
                "payment"   => "usdt",//支付网关
                "create_time" => time()
            ];
        
            //对应tp_order 的id自增项
            $id = Db::table("tp_order")->strict(false)->insertGetId($insert_data);
            
        }else{
            $id = $hash_data[0]['id'];
            $price = $hash_data[0]['price'];
        }




        $callback_param = "url=".$url_param."&money=".$money_param."&hash_id=".$hash_id;

        $this->go_pay($hash_id,$money,$callback_param);
            
    }

    /**
     * 回调
     * 
     */
    public function callback()
    {

        $url_param = Request::param("url") ??  "";
        $money_param = Request::param("money") ??  "";

        if($url_param == "" || $money_param == ""){
            abort(404,"url or money is null");
        }
 
        $url = base64_decode(str_rot13($url_param));
        $money = base64_decode(str_rot13($money_param));

        $otherclass= new Otherclass($this->app);
        $user_ip = $otherclass->get_user_ip();
        
        //--------------------黑名单关键词判断 begin-------------------------
        $black_itemid_array = [];//黑名单状态码
        $black_url_array = Db::table("tp_black_url")->where("success",0)->select();
        //var_dump($black_url_array);
        for($x=0;$x<count($black_url_array);$x++){
            if(preg_match_all('#'.$black_url_array[$x]['pattern'].'#i',$url)){
                array_push($black_itemid_array,$black_url_array[$x]['itemid']);      
            }
        }
        $black_itemid_str = join(",",$black_itemid_array);
        //echo $black_itemid_str;//271,302,323
        //--------------------黑名单关键词判断 begin-------------------------


        // -------------------   校验订单号 begin -----------------------------
        $hash_id = Request::param("hash_id") ?? Request::param("hash_id");


        //检查订单数据，如果数据库中没有找到这个id和hash_id对应的订单数据，说明订单是伪造
        $order_data = Db::table("tp_order")->where("hash_id",$hash_id)->select();
        //var_dump($order_data);
        if(count($order_data) == 0){
            $this->error("Order error.","/","",50,array());
        }
        $order_id = $order_data[0]['id'];
        // -------------------   校验订单号 end -----------------------------

        //订单状态，1为付款成功，0为未付款或者付款失败
        $success = $order_data[0]['success'];


        //支付失败和支付成功的处理思路
        if($success == 0){
            $this->error("Pay failed.","/","",5,array());
        }else{

            //如果status状态为0说明未走上述流程，如果为1则是已经走过逻辑流程了
            //为了避免用户总是刷新付款后的跳转url，导致走多次流程
            if($order_data[0]['status']==0){
            
                $update_data = [
                    "pay_time" => time(),
                    "success" => 1
                ];

                Db::table("tp_order")->where("id",$order_id)->update($update_data);
            

            }
            $pattern_data = [
                "pay_time" => time(),
                "expire_time" => time() + 60*60*24*31,
                "success" => 1
            ];

            //将正则规则库更新到数据库
            Db::table("tp_black_url")->where("itemid","in",$black_itemid_str)->update($pattern_data);

            $this->success("Unblock Successfully","/",3);
            
        }
    


        

    }





    /**
     * @param
     * $product 商品
     * $price 价钱
     * $shipping 运费
     * $description 描述内容
     */
    public function go_pay($hash_id, $money, $callback_param)
    {

    
        $callback_param = $callback_param;

        //总价
        $total_price = $money;


        View::assign("total_price",$total_price);
        View::assign("hash_id",$hash_id);

        //超时时间
        $hash_data = Db::table("tp_order")->where("hash_id",$hash_id)->select();
        View::assign("timeout_timestamp",($hash_data[0]['create_time']+Config::get("app.usdt_pay_timeout"))*1000);//十分钟过期,时间戳长度必须为毫秒级别
        
        //调用订单数据
        View::assign("hash_data",$hash_data);
        View::assign("usdt_address",Config::get("app.usdt_address"));

        //回调url
        View::assign("callback_url","/usdt/callback?".$callback_param);
        
        echo View::fetch("/Template_1/Pay/usdt_pay");
    }




    /*
    *检查数据支付状态
    *
    */

    public function check_status(){

        $hash_id = Request::param("hash_id") ?? "";

        if($hash_id == ""){
            abort(404,"hash_id is null");
        }

        //查询订单
        $hash_data = Db::table("tp_order")->where("hash_id",$hash_id)->select();


        if(count($hash_data) == 0){
            $pay_status = 0;
        }else{
            $pay_status = $hash_data[0]['success'];
        }

        $status_data = [
            "status" => $pay_status
        ];

        echo json_encode($status_data);



    }

   

}
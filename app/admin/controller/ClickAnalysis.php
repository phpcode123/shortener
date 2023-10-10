<?php
namespace app\admin\controller;


use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Session;
use think\facade\Cache;


class ClickAnalysis  extends BaseController
{   
    //控制器中间件，执行顺序：全局中间件->应用中间件->路由中间件->控制器中间件
    protected $middleware = ['\app\middleware\CheckLogin::class'];

    public function list(){
        //获取当前url的参数
        $otherclass = new Otherclass($this->app);

        $list = array();
        for($i=0; $i< Config::get("app.clicks_analysis_days"); $i++){
            $new_array_ = array();
            $date_time = date("Y-m-d", time() - 60*60*24*$i);

            //总点击数量
            $redis_clicks_key = Config::get("app.redis_prefix")."_shortener_clicks_".date("Y-m-d", time() - 60*60*24*$i);
            //pc端点击
            $redis_pc_clicks_key = Config::get("app.redis_prefix")."_shortener_pc_clicks_".date("Y-m-d", time() - 60*60*24*$i);
            //m端点击
            $redis_m_clicks_key = Config::get("app.redis_prefix")."_shortener_m_clicks_".date("Y-m-d", time() - 60*60*24*$i);
            //广告中间页面点击
            $redis_middle_page_clicks_key = Config::get("app.redis_prefix")."_shortener_middle_page_clicks_".date("Y-m-d", time() - 60*60*24*$i);



            $redis_short_url_key = Config::get("app.redis_prefix")."_shortener_short_url_".date("Y-m-d", time() - 60*60*24*$i);

            //获取总点击数
            if(Cache::has($redis_clicks_key)){
                $total_clicks = Cache::get($redis_clicks_key);
            }else{
                $total_clicks = 0;
            }


            //获取pc点击数
            if(Cache::has($redis_pc_clicks_key)){
                $total_pc_clicks = Cache::get($redis_pc_clicks_key);
            }else{
                $total_pc_clicks = 0;
            }


            //获取m端点击数
            if(Cache::has($redis_m_clicks_key)){
                $total_m_clicks = Cache::get($redis_m_clicks_key);
            }else{
                $total_m_clicks = 0;
            }


            //获取中间页点击数
            if(Cache::has($redis_middle_page_clicks_key)){
                $total_middle_page_clicks = Cache::get($redis_middle_page_clicks_key);
            }else{
                $total_middle_page_clicks = 0;
            }

            //获取当日生成的short_url数量
            if(Cache::has($redis_short_url_key)){
                $total_short_url = Cache::get($redis_short_url_key);
            }else{
                $total_short_url = 0;
            }

            //将数据增加到new_array()容器中
            $new_array_["date_time"] = $date_time;
            $new_array_["all_clicks"] = $total_clicks;
            $new_array_["pc_clicks"] = $total_pc_clicks;
            $new_array_["m_clicks"] = $total_m_clicks;
            $new_array_["middle_page_clicks"] = $total_middle_page_clicks;
            $new_array_["short_url"] = $total_short_url;


            array_push($list, $new_array_);
        
            
        }
        //var_dump($list);

        //------------  将读取的数据储存在数据库中 begin ---------------------
        $insert_click_analysis_data = array();

        //将数组使用日期作为键，然后再对其进行升序，这样使用日期插入到数据库中的值就是按照最新日期排序的
        for($i=0; $i < count($list); $i++){
            $insert_click_analysis_data[$list[$i]['date_time']] = $list[$i];
        }
        
        asort($insert_click_analysis_data);
        foreach($insert_click_analysis_data as $key => $value){
            //当天最新的数据不要插入到数据库中（当天最新的数据值还未积累完）
            if($key != date("Y-m-d", time())){
                $analysis_data = Db::table("tp_click_analysis")->where("date_time","=",$key)->select();
                //如果返回的数据值大于0，则说明数据库中已经有此项数据，否则就将此项数据插入数据库
                if(count($analysis_data) == 0){
                    //var_dump($value);
                    Db::table("tp_click_analysis")->strict(false)->insert($value);
                }
            }
        }
        //------------  将读取的数据储存在数据库中 end ----------------------


        $data_list = Db::table('tp_click_analysis')->order('itemid', 'desc')->paginate([
            'list_rows' => Config::get('app.admin_page_num'),
            'path'  => "/".Config::get("app.admin_path").'/click_analysis/list',
            'query' => Request::param(),
        ]); 

        $today_datetime = date("Y-m-d", time());

        $page_num = Request::param("page") ? Request::param("page") : "1";



        $malicious_data = Db::table("tp_report_malicious_url")->where("status","0")->select();
        $contact_data = Db::table("tp_contact")->where("status","0")->select();
        $shortener_last_data = Db::table("tp_shortener")->order("itemid","desc")->limit(1)->select();
        


        //malicious url status 
        $malicious_url_key = Config::get("app.redis_prefix")."malicisou_url_status";
        $malicious_url_status_time = Cache::get($malicious_url_key ,"0");
        //echo $malicious_url_status_time."   ";
        //echo time();
        View::assign("malicious_url_status_time",$malicious_url_status_time);
        View::assign("now_timestamp",time());


        //check_malicious_2_local 运行监控
        $check_malicious_2_local = Config::get("app.redis_prefix")."check_malicious_2_post";
        $check_malicious_2_local_status_time = Cache::get($check_malicious_2_local ,"0");
        View::assign("check_malicious_2_local_status_time",$check_malicious_2_local_status_time);


        //抓取异常和嫌疑链接数据监控统计
        //8为监控脚本访问异常和需要逐条待审核的链接
        //9为中了监控脚本黑名单关键词的链接
        $check_malicious_8_data = Db::table("tp_shortener")->where("check_malicious_status","8")->select();
        $check_malicious_9_data = Db::table("tp_shortener")->where("check_malicious_status","9")->select();

        View::assign("check_malicious_8_count",count($check_malicious_8_data));
        View::assign("check_malicious_9_count",count($check_malicious_9_data));

        

        View::assign("malicious_data_num",count($malicious_data));
        View::assign("contact_data_num",count($contact_data));
        View::assign("shortener_last_data",$shortener_last_data);


        View::assign('data_list',$data_list);
        View::assign('today_datetime',$today_datetime);
        View::assign('page_num',$page_num);
        View::assign('username',Session::get('username'));
        View::assign('list',$list);
        View::assign('pc_url',Config::get('app.admin_url'));
        View::assign('admin_url',Config::get('app.admin_url'));
        View::assign('admin_path',Config::get('app.admin_path'));

        return View::fetch('/ClickAnalysis/list');       

    }

    public function edit(){
        $itemid = Request::param("itemid") ? Request::param("itemid") : 0;
        if(empty($itemid)){
            $this->error("Itemid is empty.",$_SERVER["HTTP_REFERER"],1);
        }

        $data = Db::table('tp_click_analysis')->where('itemid','=',$itemid)->select();
        

        View::assign('username',Session::get('username'));
        View::assign('data',$data);
        View::assign('admin_path',Config::get('app.admin_path'));
        View::assign('pc_url',Config::get('app.admin_url'));
        return View::fetch('/ClickAnalysis/edit');      
    }

    public function editPost(){
        $data = Request::post();

        $itemid = $data['itemid'];
        $ad_income = $data['ad_income'];
        $ad_views = $data['ad_views'];
        $ad_display = $data['ad_display'];
        $ad_hits = $data['ad_hits'];
        $ad_cpc = $data['ad_cpc'];
        //分母不能为0
        try{
            $ad_rpm = $ad_income/$ad_views*1000;
            $ad_ctr = $ad_hits/$ad_views*100;
        }catch(\Exception $e){
            $this->error("AD_VIEWS can not be 0.","/".Config::get("app.admin_path")."/click_analysis/list",2);
        }


        $insert_data = [
            "ad_views" => $ad_views,
            "ad_display" => $ad_display,
            "ad_hits" => $ad_hits,
            "ad_rpm" => $ad_rpm,
            "ad_income" => $ad_income,
            "ad_ctr" => $ad_ctr,
            "ad_cpc" => $ad_cpc
        ];

    

        if(Db::table('tp_click_analysis')->strict(false)->where('itemid',"=",$itemid)->update($insert_data)){
            $this->success("Data edit success.","/".Config::get("app.admin_path")."/click_analysis/list",1);
        }else{
            $this->error("Data edit fail.","/".Config::get("app.admin_path")."/click_analysis/list",2);
        }
    }
}

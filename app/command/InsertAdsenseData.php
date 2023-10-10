<?php
declare (strict_types = 1);

namespace app\command;

use think\facade\Db;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;


class InsertAdsenseData extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('app\command\inputadsensedata')
            ->setDescription('the app\command\inputadsensedata command');
    }

    protected function execute(Input $input, Output $output)
    {
        //adsense data path
        //注意顺序必须与后台一致，否则导入的数据可能出现错乱
        //click_analysis/list DATE_TIME AD_VIEWS AD_DISPLAY	AD_HITS	AD_RPM AD_INCOME AD_CTR	AD_CPC
        //在adsense后台手动排下顺序
        //日期
        //网页浏览量
        //展示次数
        //点击次数
        //网页RPM
        //估算收入
        //网页CTR
        //CPC

        $adsense_data_file = app()->getRootPath()."/extend/adsense/report.csv";
        $data_file = fopen($adsense_data_file,"r");
        while(!feof($data_file)){
            $line = fgets($data_file);
            $line_list = explode(",", $line);
            
            $date_time = $line_list[0]; 
            $ad_views = $line_list[1]; 
            $ad_display = $line_list[2]; 
            $ad_hits = $line_list[3]; 
            $ad_rpm = $line_list[4]; 
            $ad_income = $line_list[5]; 
            $ad_ctr = $line_list[6]*100; 
            $ad_cpc = $line_list[7];  
            
            $insert_data = [
                "ad_views" => $ad_views,
                "ad_display" => $ad_display,
                "ad_hits" => $ad_hits,
                "ad_rpm" => $ad_rpm,
                "ad_income" => $ad_income,
                "ad_ctr" => $ad_ctr,
                "ad_cpc" => $ad_cpc
            ];
            var_dump($insert_data);
            
            Db::table('tp_click_analysis')->strict(false)->where('date_time',"=",$date_time)->update($insert_data);
        }
        fclose($data_file);
        
    }
}

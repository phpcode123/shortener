<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        'check_malicious' => 'app\command\CheckMalicious',
        'check_malicious_2_local'  => 'app\command\CheckMalicious2Local',
        'redis_index'  => 'app\command\RedisIndex',
        'insert_adsense_data'  => 'app\command\InsertAdsenseData',
        'usdt_check'  => 'app\command\UsdtCheck',
        'youtube_spider'  => 'app\command\YoutubeSpider',
        'version'  => 'app\command\Version',
    ],
];

<?php
// +----------------------------------------------------------------------
// | 多语言设置
// +----------------------------------------------------------------------

return [
    // 默认语言
    'default_lang'    => env('lang.default_lang', 'en-us'),
    // 允许的语言列表
    'allow_lang_list' => ['en-us','zh-hk','zh-tw','zh-mo','zh-sg','en','af','af-za','ar','ar-dz','ar-bh','ar-eg','ar-iq','ar-jo','ar-kw','ar-lb','ar-ly','ar-ma','ar-om','ar-qa','ar-sa','ar-sy','ar-tn','ar-ae','ar-ye','fr','fr-be','fr-ca','fr-fr','fr-lu','fr-mc','fr-ch','de','de-at','de-de','de-li','de-lu','de-ch','ja','ja-jp','id','id-id','it','it-it','it-ch','ko','ko-kr','es','es-ar','es-bo','es-cl','es-co','es-cr','es-do','es-ec','es-sv','es-gt','es-hn','es-mx','es-ni','es-pa','es-py','es-pe','es-pr','es-es','es-uy','es-ve','pt','pt-br','pt-pt','vi','vi-vn','sk','sk-sk','th','th-th','be','be-by','bg','bg-bg','ru','ru-ru','ru-mo','fa','fa-ir','nl','nl-be','nl-nl','el','el-gr','hu','hu-hu','no','nb-no','nn-no','sv','sv-fi','sv-se','tr','tr-tr','uk','uk-ua','cs','cs-cz','da','da-dk','fi','fi-fi','pl','pl-pl'],
    // 多语言自动侦测变量名 get获取参数
    'detect_var'      => 'lang',
    // 是否使用Cookie记录
    'use_cookie'      => true,
    // 多语言cookie变量
    'cookie_var'      => 'think_lang',
    // 多语言header变量
    'header_var'      => 'think-lang',
    // 扩展语言包
    'extend_list'     => [],
    // Accept-Language转义为对应语言包名称
    'accept_language' => [
        'zh-hans-cn' => 'en-us',
    ],
    // 是否支持语言分组
    'allow_group'     => false,
];

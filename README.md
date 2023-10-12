## 避免不法份子滥用，此项目仅为展示，部分核心代码删除。      

## 简介    

1、Shortener前后迭代近半年有余，程序基本趋于稳定。

2、软件设计开发时就较为着重于性能，代码精简~~

3、软件系免费分享，项目也只适用于国外英文项目，请大家勿要在国内瞎搞，出事后果自担。

   
## 技术相关

* 环境：linux + nginx + mysql + php + redis
* 后端：ThinkPHP6.0
* 前端：Tabler + Bootstrap + jQuery

## 安装使用

* 本程序仅支持LNMP环境，其它环境未测试，建议安装使用linux宝塔。（MYSQL5.7 + PHP7.4 + REDIS）
* 安装：主程序上传到web，在宝塔面板中绑定好主域名，然后修改/shortener/config/app.php配置文件 
* MYSQL:建立空数据库，恢复/shortener/shortener_20230911.sql文件，然后配置数据库文件
~~~

/shortener/config/database.php, 并修改下列行(字母大写部分)
...
'database'        => env('database.database', 'YOUR_DATABASE'),
'username'        => env('database.username', 'YOUR_MYSQL_USERNAME'),
'password'        => env('database.password', 'YOUR_MYSQL_PASSWORD'),
...
~~~

* MySql安装配置好后在MySql界面插入两行数据(此程序为多域名版本，认域名。将第一行中的192.168.0.5:8088改为自己的主域，多域名自己在后台添加)
~~~
INSERT INTO `tp_domain` VALUES(1,"http://","192.168.0.5:8088/","Test_title","Index_title","Index_keyword","Index_description",1,1,1,0,1,0,"");

INSERT INTO `tp_shortener` VALUES (1,'',1,'','V0N6UU','dEZMRlh','bU5ia3E0','https://www.baidu.com/',0,5,'1.1.1.1','',1648795566,0,1,0,'','',1,0,1,1,1,2,0);
~~~




* 伪静态文件目录(只做了Nginx适配)：/shortener/public/.htaccess  内容复制宝塔配置里即可
* 后台地址：https://yoursite.com/admin.php/login/login  用户名：admin  密码：admin888 (默认用户名和密码)

## 命令行任务

* 相关命令行工具在目录/Shortener/app/command/下,执行php think *  //*号指下列命名，如：php think check_malicious
~~~

    'check_malicious' => 'app\command\CheckMalicious',
    'check_malicious_2_local'  => 'app\command\CheckMalicious2Local',
    'redis_index'  => 'app\command\RedisIndex',
    'insert_adsense_data'  => 'app\command\InsertAdsenseData',
    'usdt_check'  => 'app\command\UsdtCheck',
    'youtube_spider'  => 'app\command\YoutubeSpider',
    'version'  => 'app\command\Version',

~~~

## 其它问题

### 如何更改后台登录账号密码？
~~~
修改网站配置文件：/shortener/config/app.php    （修改大写字母部分即可）
    'admin_username'         => 'YOUR_ADMIN_USERNAME', //后台用户名
    'admin_password'         => 'YOUR_ADMIN_PASSWORD', //后台密码
~~~


### 如何更改后台登录地址？
~~~
1、先将/shortener/public/admin.php admin.php文件命名为自己想要的 如：loginasadad.php
2、修改网站配置文件：/shortener/config/app.php    （admin_path地址必须与步骤1修改的一致）如:

'admin_path'             => 'loginasadad.php',//后台入口文件，防止后台被爆破

后台地址：https://yoursite.com/loginasadad.php/login/login
~~~

## 版本说明
* 程序只带来了$7500刀的adsense收益，因流量较小一直未达到盈利预期，后期本应持续迭代的功能都停了。大家后期流量做起来了可以联系我二开。

* 程序使用方法一会儿也写不完，大家自己慢慢探索。
* Google Adsense收益截图  
![adsense](/public/static/images/adsense.png)

## 程序界面截图
* 前台首页  

![index](/public/static/images/1.png)

* 后台部分截图  

![](/public/static/images/2.png)
![](/public/static/images/3.png)
![](/public/static/images/4.png)
![](/public/static/images/5.png)

##
 **<font color=Red>DO NOT USE IT FOR MALICIOUS！</font>**
##


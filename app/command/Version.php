<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Db;

class Version extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('app\command\Version')
            ->setDescription('the app\command\Version command');
    }


    
    protected function execute(Input $input, Output $output)
    {

        
        $app_version = Config::get("app.app_version");

        echo "Shortener version: {$app_version}\n";

    }

    
}

<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
use \Workerman\Worker;
use \Workerman\WebServer;
use \GatewayWorker\Gateway;
use \GatewayWorker\BusinessWorker;
use \Workerman\Autoloader;

// 自动加载类
require_once __DIR__ . '/../../Workerman/Autoloader.php';
Autoloader::setRootPath(__DIR__);

//Worker::$stdoutFile = '/home/work/wzb/project/release/log/workerman.log';

// gateway 进程，这里使用Text协议，可以用telnet测试
//$gateway = new Gateway("Text://120.24.36.177:8282");
//异步任务服务
$gateway = new Worker("Text://127.0.0.1:7272");
// gateway名称，status方便查看
$gateway->name = 'watch_server_async_gateway';
// gateway进程数
$gateway->count = 20;

$gateway->onMessage=function($connection,$message)
{
	//$connection->send($message.'hello');
	//echo 'async'.$message.PHP_EOL;
	HandleData::handle_async_data($connection,$message);
};

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START')) {
    Worker::runAll();
}


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

$debug = new Gateway("Websocket://0.0.0.0:9998");
// gateway名称，status方便查看
$debug->name = 'watch_server_debug_websocket';
// gateway进程数
$debug->count = 2;
// 本机ip，分布式部署时使用内网ip
$debug->lanIp = '127.0.0.1';
// 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
// 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
$debug->startPort = 3000;
// 服务注册地址
$debug->registerAddress = '127.0.0.1:1238';

// 心跳间隔
//$gateway->pingInterval = 10;
// 心跳数据
//$gateway->pingData = '{"type":"ping"}';
//
// 心跳间隔
$debug->pingInterval = 10;
//心跳数据
$debug->pingData = 'ping';
$debug->pingNotResponseLimit = 3;



// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START')) {
    Worker::runAll();
}


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

// #### 内部推送端口(假设当前服务器内网ip为192.168.100.100) ####
//$internal_gateway = new Gateway("Text://10.44.125.196:8283");
$internal_gateway = new Gateway("JsonInt://0.0.0.0:10002");
$internal_gateway->name='watch_server_internalGateway';
$internal_gateway->count=8;
$internal_gateway->startPort = 2822;
// 端口为start_register.php中监听的端口，聊天室默认是1236
$internal_gateway->registerAddress = '127.0.0.1:1330';
// #### 内部推送端口设置完毕 ####

// 心跳间隔
//$gateway->pingInterval = 10;
// 心跳数据
//$gateway->pingData = '{"type":"ping"}';
$gateway->pingInterval = 60;
//心跳数据
$gateway->pingData = '';
$gateway->pingNotResponseLimit = 3;

/*
// 当客户端连接上来时，设置连接的onWebSocketConnect，即在websocket握手时的回调
$gateway->onConnect = function($connection)
{
    $connection->onWebSocketConnect = function($connection , $http_header)
    {
        // 可以在这里判断连接来源是否合法，不合法就关掉连接
        // $_SERVER['HTTP_ORIGIN']标识来自哪个站点的页面发起的websocket链接
        if($_SERVER['HTTP_ORIGIN'] != 'http://kedou.workerman.net')
        {
            $connection->close();
        }
        // onWebSocketConnect 里面$_GET $_SERVER是可用的
        // var_dump($_GET, $_SERVER);
    };
};
*/

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START')) {
    Worker::runAll();
}


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
use \Workerman\Protocols\Websocket;

// 自动加载类
require_once __DIR__ . '/../../Workerman/Autoloader.php';

$recv_worker = new Worker('Mytcp://0.0.0.0:8080');
$recv_worker->onWorkerStart = function($recv_worker)
{
    $send_worker = new Worker('Websocket://0.0.0.0:8008');
    $send_worker->onMessage = function($connection, $data)
    {
    };
    $recv_worker->sendWorker = $send_worker;
    $send_worker->listen();
};

$recv_worker->onMessage = function($connection, $data)use($recv_worker)
{
     echo "rr".PHP_EOL;
    //file_put_contents ( "test.jpg", $data);
    foreach($recv_worker->sendWorker->connections as $send_connection)
    {
        //$send_connection->websocketType = "\x82";
        echo "send".PHP_EOL;
        $pic=file_get_contents("test.jpg");
        $send_connection->send($pic);
    }
};

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

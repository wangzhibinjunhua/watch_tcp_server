<?php
use \Workerman\Worker;
use \Workerman\Lib\Timer;
use \Workerman\Connection\AsyncTcpConnection;

$worker = new Worker();
$worker->onWorkerStart = 'connect';

function connect(){

    static $count = 0;
    // 2000个链接
    if ($count++ >= 20000) return;
    // 建立异步链接
    $imei=123456789012345+$count;
    $con = new AsyncTcpConnection('tcp://120.76.47.120:10003');

    $con->onConnect = function($con) {
       // 递归调用connect
       connect();
    };
    $con->onMessage = function($con, $msg) {
        //echo "recv $msg\n";
    };
    $con->onClose = function($con) {
        echo "con close\n";
    };
    // 当前链接每10秒发个心跳包
    Timer::add(100, function()use($con){
        $con->send('0015HA*'.$imei.'*LK');
    });
    $con->connect();
    echo $count, " connections complete\n";
}
Worker::runAll();

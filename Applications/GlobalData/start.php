<?php
use Workerman\Worker;
use GlobalData\Server;
require_once __DIR__ . '/../../Workerman/Autoloader.php';
require_once __DIR__ . '/src/Server.php';
$worker = new Server('127.0.0.1',2207);
Worker::runAll();

<?php
/**
* @author wzb<wangzhibin_x@foxmail.com>
* @date Aug 29, 2016 5:47:37 PM
* 获取redis消息队列中的数据,拼接sql 批量入库
*/
require_once('/home/work/wzb/project/release/common/db_config.php');
$redis=new Redis();
$redis->connect('127.0.0.1',6379);
$redis->auth("huayingtek2016");

//获取现有消息队列的长度
$count = 0;
$max = $redis->lLen("ha_watch_info");
$showtime=date("Y-m-d H:i:s");
echo $max.'----'.$showtime.PHP_EOL;
//获取消息队列的内容
$insert_sql='insert into watch_info (imei,gps_lon,gps_lat,watch_time,system_time,location_lon,location_lat,location_content,location_type,ud_content,battery,unix_time) values ';

//回滚数组
$roll_back_arr=array();

while($count<$max){
	$msg=$redis->lPop("ha_watch_info");
	$roll_back_arr=$msg;

	if($msg == 'nil' || !isset($msg)){
		$insert_sql .= ";";
		break;
	}

	$insert_sql .= " ($msg),";
	$count++;
}


//存在数据,批量入库
if($count != 0){
	$db=connect_database('ha_watch');
	if(!$db){
		die("could not connect mysql");
	}
	$insert_sql = rtrim($insert_sql,",").";";
	//echo "xxx:".$insert_sql.PHP_EOL;
	$res = mysql_query($insert_sql);
	//echo mysql_error();
	//var_dump($res);
	//数据库插入失败回滚
	if(!$res){
		foreach ($roll_back_arr as $k){
			$redis->rPush("ha_watch_info",$k);
		}
	}
}

$redis->close();






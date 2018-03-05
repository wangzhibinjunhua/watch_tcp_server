<?php

$db_host="localhost:3306";
$db_user="root";
$db_password="huayingtekmysql20160709";
$db_name="health";

/**
 * @Author   wzb<wangzhibin_x@qq.com>
 * @DateTime 2016-07-09T10:47:24+0800
 * 公用数据库配置文件
 * @return   [bool] true:连接数据库成功;false:失败
 *
 */
function connect_database($database_name)
{
	global $db_host,$db_user,$db_password,$db_name;
	$db_name=$database_name;
	$db = mysql_connect ( $db_host, $db_user, $db_password );
    $tb = mysql_select_db ( $db_name );
    if($tb){
    	return true;
    }else{
    	return false;
    }

}


<?php
class Config_Db_example
{
	$db_host="localhost:3306";
	$db_user="user";
	$db_password="password";
	$db_name="health";

/**
 * @Author   wzb<wangzhibin_x@qq.com>
 * @DateTime 2016-07-09T10:47:24+0800
 * 公用数据库配置文件
 * @return   [bool] true:连接数据库成功;false:失败
 *
 */
function connect_database()
{
	global $db_host,$db_user,$db_password,$db_name;
	$db = mysql_connect ( $db_host, $db_user, $db_password );
    $tb = mysql_select_db ( $db_name );
    if($tb){
    	return true;
    }else{
    	return false;
    }

}

}

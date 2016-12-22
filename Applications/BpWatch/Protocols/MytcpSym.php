<?php

//namespace Protocols;
use Workerman\Connection\TcpConnection;
use Config\Config;
require_once __DIR__ . '/../../Statistics/Clients/StatisticClient.php';
/**
 * for watch jajale
 * author wzb<wangzhibin_x@qq.com>
 * 2016-08-08
 */
/**
 * mytcp Protocol.
 * for 儿童手表通信协议
 * 开头四位字节为包体长度
 * 0005abcde
 */
class Mytcp
{
    /**
     * Check the integrity of the package.
     *
     * @param string        $buffer
     * @param TcpConnection $connection
     * @return int
     */
    public static function input($buffer, TcpConnection $connection)
    {
        if (strlen($buffer) < 21) {
            //return 0;
            //合法长度
            return;
        }
        //检验数据格式
        if(substr($buffer,4,3) != 'HA*'){
        	return;
        }

        $body_len=hexdec(substr($buffer,0,4));
        $total_len=$body_len+4;
        if($total_len > strlen($buffer)){
        	return 0;
        }
        return $total_len;
    }

    /**
     * Encode.
     *
     * @param string $buffer
     * @return string
     */
    public static function decode($buffer)
    {
       // if(\Config\Config::DEBUG_STATISTICS){
       if(false){
        	//statistics
        	// 统计开始
        	\StatisticClient::tick("bp_watch", 'debug_data_receive');
        	// 统计的产生，接口调用是否成功、错误码、错误日志
        	$success = false; $code = 20160001; $msg = $buffer;
        	// 上报结果
        	\StatisticClient::report('bp_watch', 'debug_data_receive', $success, $code, $msg);
        	//end statistics
        }
    	return substr($buffer, 4);
    }

    /**
     * Decode.
     *
     * @param string $buffer
     * @return string
     */
    public static function encode($buffer)
    {
    	$rs_len=sprintf("%04x",strlen($buffer)); //业务逻辑去处理
    	//if(\Config\Config::DEBUG_STATISTICS){
    	if(false){
    		//statistics
    		// 统计开始
    		\StatisticClient::tick("bp_watch", 'debug_data_send');
    		// 统计的产生，接口调用是否成功、错误码、错误日志
    		$success = false; $code = 20160002; $msg = $buffer;
    		// 上报结果
    		\StatisticClient::report('bp_watch', 'debug_data_send', $success, $code, $msg);
    		//end statistics
    	}
    	return $rs_len.$buffer;
    }
}

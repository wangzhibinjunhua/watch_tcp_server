<?php
namespace Protocols;
use Workerman\Connection\TcpConnection;
require_once __DIR__ . '/../../Statistics/Clients/StatisticClient.php';
/**
* @author wzb<wangzhibin_x@foxmail.com>
* @date Sep 1, 2016 4:01:48 PM
* json协议
* 首部4字节网络字节序unsigned int，标记整个包的长度 数据部分为Json字符串
* ****{"id":"13456789","cmd":"ping","info":""}
*/

class JsonInt
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

		// 接收到的数据还不够4字节，无法得知包的长度，返回0继续等待数据
		if(strlen($buffer)<4)
		{
			return 0;
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
		if(\Config\Config::DEBUG_STATISTICS_APP){
			//statistics
			// 统计开始
			\StatisticClient::tick("bp_watch_app", 'debug_data_receive');
			// 统计的产生，接口调用是否成功、错误码、错误日志
			$success = false; $code = 20160003; $msg = $buffer;
			// 上报结果
			\StatisticClient::report('bp_watch_app', 'debug_data_receive', $success, $code, $msg);
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
		//$rs_len=sprintf("%04x",strlen($buffer));//业务代码去实现
		//return $rs_len.$buffer;
		if(\Config\Config::DEBUG_STATISTICS_APP){
			//statistics
			// 统计开始
			\StatisticClient::tick("bp_watch_app", 'debug_data_send');
			// 统计的产生，接口调用是否成功、错误码、错误日志
			$success = false; $code = 20160004; $msg = $buffer;
			// 上报结果
			\StatisticClient::report('bp_watch_app', 'debug_data_send', $success, $code, $msg);
			//end statistics
		}
		return $buffer;
	}
}

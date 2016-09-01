<?php
namespace Protocols;
use Workerman\Connection\TcpConnection;
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
		return $buffer;
	}
}

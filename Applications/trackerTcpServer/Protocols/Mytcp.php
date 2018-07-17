<?php
namespace Protocols;
use Workerman\Connection\TcpConnection;
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
        //echo "## ".$buffer;
        if (strlen($buffer) < 21) {
            //return 0;
            //合法长度
            return;
        }
        //检验数据格式
        if(substr($buffer,4,3) != 'CS*'){
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
    	//$rs_len=sprintf("%04x",strlen($buffer)); //业务逻辑去处理
    	return $buffer;
    }
}

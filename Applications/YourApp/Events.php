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

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id) {
        //echo $client_id."\n";
        // 向当前client_id发送数据
        //Gateway::sendToClient($client_id, "Hello $client_id");
        // 向所有人发送
        //Gateway::sendToAll("$client_id login");
    }

   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */

   public static function onMessage($client_id, $message) {
        echo $message."\n";
        //$message=pack('H*',$message);
        file_put_contents("tt.amr",$message, FILE_APPEND);
        $message_data=json_decode($message,true);
        if(!$message_data){
          $len=hexdec(substr($message,0,4));
          $type=substr($message,4,3);
          $imei=substr($message,7,16);
          $cmd=substr($message,24,2);
          $msg=substr($message,26,$len-22);
          echo "len= $len \n";
          echo "type= $type \n ";
          echo "imei= $imei \n ";
          echo "cmd= $cmd \n ";
          echo "msg= $msg \n";
          //有效数据
          if($type == 'CS*'){
              Gateway::bindUid($client_id, $imei);

              switch($cmd)
              {
                //链路保持
                case 'LK':
                  Gateway::sendToUid($imei,substr($message,0,26)."\n");
                  return;

                //位置上报
                case 'UD':
                  Gateway::sendToUid($imei,$msg);
                  return;
                //语音
                case 'TK':
                Gateway::sendToUid('1234567890123457','aaaaa'."\n");
                  return;
              }
          }
        }else{

            switch ($message_data['type']) {
              case 'send':
                # code...
                $new_message1=array('type'=>'pk','msg'=>'test');
                Gateway::sendToUid($message_data['user'],json_encode($new_message1));
                break;
              case 'getlist':
                $list=Gateway::getClientInfoByGroup("123");
                $new_message=array('type'=>'pk','msg'=>'test2');
                $new_message['list']=$list;
                Gateway::sendToUid($message_data['user'],json_encode($new_message));
                break;
              default:
                # code...
                break;
            }
        }

   }

   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id) {
       // 向所有人发送
       //GateWay::sendToAll("$client_id logout");
   }
}

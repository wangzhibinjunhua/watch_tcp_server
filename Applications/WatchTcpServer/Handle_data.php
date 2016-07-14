<?php
use \GatewayWorker\Lib\Gateway;
class handle_data
{
	   /**
    * [handle_watch_data description]
    * @Author   wzb<wangzhibin_x@qq.com>
    * @DateTime 2016-07-11T20:05:24+0800
    * @ 处理手表终端发送过来的数据
    * LENGTHCS*YYYYYYYYYY*LK,msg 格式,YYYY是15位数字Imei号
    */
   public static function handle_watch_data($client_id, $message)
   {
          $len=hexdec(substr($message,0,4));
          $msg_body=substr($message,4,strlen($message)-4);
          if(strlen($msg_body) != $len){
            return;
          }

          $msg_array=explode('*', $msg_body);
          $type=$msg_array[0];
          $imei=$msg_array[1];

          $msg_msg=explode(',',$msg_array[2]);
          $cmd=$msg_msg[0];

          // echo "len= $len \n";
          // echo "type= $type \n ";
          // echo "imei= $imei \n ";
          // echo "cmd= $cmd \n ";
          // echo "msg_msg= $msg_array[2] \n";
          //有效数据
          if($type == 'CS'){
              Gateway::bindUid($client_id, $imei);

              switch($cmd)
              {
                //链路保持
                case 'LK':
                  $rs='CS*'.$imei.'*LK';
                  $rs_len=sprintf("%04x",strlen($rs));
                  Gateway::sendToUid($imei,$rs_len.$rs.PHP_EOL);
                  return;

                //位置上报
                case 'UD':
                  Gateway::sendToUid($imei,$msg);
                  return;
                //语音
                case 'TK':
                Gateway::sendToUid('1234567890123457','aaaaa'.PHP_EOL);
                  return;
              }
          }
   }


   /**
    * [handle_server_data description]
    * @Author   wzb<wangzhibin_x@qq.com>
    * @DateTime 2016-07-11T20:08:11+0800
    * @处理api接口数据 $message定义为json数据
    */
   public function handle_server_data($client_id,$message)
   {
        switch ($message['type']) {
              case 'send':
                echo "send"."]\n";
                Gateway::sendToAll($message['content'].PHP_EOL);
                break;
              default:
                # code...
                break;
            }
   }


}

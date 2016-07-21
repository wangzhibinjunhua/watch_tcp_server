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
   public static function handle_watch_data_test($client_id, $message)
   {
          static $filename='1.amr';
          static $tk_flag=false;
          static $tk_recv_len=0;
          static $tk_total_len=0;
          static $imei;
          $len=hexdec(substr($message,0,4));

          if($tk_flag){
              if($tk_recv_len<$tk_total_len+4){
                file_put_contents($filename,$message,FILE_APPEND);
                $tk_recv_len +=strlen($message);
              }else{

              $tk_flag=false;
              $tk_recv_len=0;
              $tk_total_len=0;
              $rs_tk='CS*'.$imei.'*TK,1';
              $rs_tk_len=sprintf("%04x",strlen($rs_tk));
              Gateway::sendToUid($imei,$rs_tk_len.$rs_tk);
              return;
            }
          }else{

          $msg_body=substr($message,4,strlen($message)-4);
          // if(strlen($msg_body) != $len){
          //   //return;
          // }

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
                  Gateway::sendToUid($imei,$rs_len.$rs);
                  return;
                //位置上报
                case 'UD':
                  Gateway::sendToUid($imei,"ud");
                  return;
                //语音
                case 'TK': // lencs*imei*tk,amr数据
                $tk_flag=true;
                $tk_recv_len +=strlen($message);
                $tk_total_len=hexdec(substr($message,0,4));
                $filename=rand(1,100).'.amr';
                //$id=$msg_msg[2];
                //$total=$msg_msg[3];
                //$amr=$msg_msg[4];
                $head_len=22;
                $amr=substr($msg_body,$head_len,strlen($msg_body)-$head_len);
                file_put_contents($filename,$amr,FILE_APPEND);
                chmod($filename,0777);

                  return;
              }
          }
        }
   }


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
           echo "cmd= $cmd \n ";
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
                  Gateway::sendToUid($imei,$rs_len.$rs);
                  return;

                //位置上报
                case 'UD':
                  Gateway::sendToUid($imei,"ud");
                  return;
                //语音
                case 'TK':
                $filename=$msg_msg[1];
                $id=$msg_msg[2];
                $total=$msg_msg[3];
                //$amr=$msg_msg[4];
                $head_len=6+strlen($filename)+strlen($id)+strlen($total)+4+15;
                $amr=substr($msg_body,$head_len,strlen($msg_body)-$head_len);
                file_put_contents($filename,$amr,FILE_APPEND);
                chmod($filename,0777);
                $rs_tk='CS*'.$imei.'*TK,1';
                $rs_tk_len=sprintf("%04x",strlen($rs_tk));
                Gateway::sendToUid($imei,$rs_tk_len.$rs_tk);
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
                if($message['content'] == 'tk'){
                    $file=file_get_contents('test.amr');
                    $rs='CS*358688000000158*TK,'.$file;
                    $rs_len=sprintf("%04x",strlen($rs));
                    Gateway::sendToAll($rs_len.$rs);
                }else{
                  Gateway::sendToAll($message['content']);
                }
                break;
              default:
                # code...
                break;
            }
   }


}

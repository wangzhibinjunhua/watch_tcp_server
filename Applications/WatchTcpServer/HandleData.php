<?php
use \GatewayWorker\Lib\Gateway;
use Events\Lbs\EventsLbsCommon;
use Events\WeatherService\WeatherService;
class HandleData
{

    public static function pack_data($data)
    {
        $data_len=sprintf("%04x",strlen($data));
        return $data_len.$data;
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
                  static $filename='1.amr';
                  static $imei;

                  //echo $message.'  xx'.PHP_EOL;

                  $msg_array=explode('*', $message);
                  if(count($msg_array)<3){
                      return;
                  }
                  $type=$msg_array[0];
                  $imei=$msg_array[1];

                  $msg_msg=explode(',',$msg_array[2]);
                  $cmd=$msg_msg[0];


                  Gateway::bindUid($client_id, $imei);

                  switch($cmd)
                  {
                    //链路保持
                    case 'LK':
                      $rs_lk='CS*'.$imei.'*LK';
                      //$rs_lk_len=sprintf("%04x",strlen($rs));
                      Gateway::sendToUid($imei,self::pack_data($rs_lk));
                      return;
                    //位置上报
                    case 'UD':
                      $rs_ud='CS*'.$imei.'*UD';
                      //$rs_ud_len=sprintf("%04x",strlen($rs_ud));
                      Gateway::sendToUid($imei,self::pack_data($rs_ud));
                      $ud_parse=new EventsLbsCommon();
                      $ud_parse->parse($message);
                      return;
                    //语音
                    case 'TK': // lencs*imei*tk,amr数据
                      $filename=__DIR__.'/amr/'.rand(1,100).'.amr';
                      $head_len=22;
                      $amr=substr($message,$head_len,strlen($message)-$head_len);
                      file_put_contents($filename,$amr,FILE_APPEND);
                      $rs_tk='CS*'.$imei.'*TK,1';
                      //$rs_tk_len=sprintf("%04x",strlen($rs_tk));
                      Gateway::sendToUid($imei,self::pack_data($rs_tk));
                      return;

                    case 'SYSTEMTIME':
                      $rs_st='CS*'.$imei.'*SYSTEMTIME,'.time().'000';
                      //$rs_st_len=sprintf("%04x",strlen($rs_st));
                      Gateway::sendToUid($imei,self::pack_data($rs_st));
                    case 'WEATHER':
                      $rs_wea='CS*'.$imei.'*WEATHER,';
                      //$rs_wea_len=sprintf("%04x",strlen($rs_wea));
                      Gateway::sendToUid($imei,self::pack_data($rs_wea.'1'));
					  $weather_service=new WeatherService();
					  $rs_weather=$weather_service->parse($message);
					  Gateway::sendToUid($imei,self::pack_data($rs_wea.$rs_weather));
					  return;
                    default:
                    return;
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
         $message_data=json_decode($message,true);
         switch ($message_data['type']) {
              case 'send':
                if($message_data['content'] == 'tk'){
                    $file=file_get_contents('test.amr');
                    $rs='CS*201508220452222*TK,'.$file;
                    //$rs_len=sprintf("%04x",strlen($rs));
                    Gateway::sendToUid($imei,self::pack_data($rs));
                }else{
                  Gateway::sendToAll($message_data['content']);
                }
                break;
              default:
                # code...
                break;
            }
   }


}

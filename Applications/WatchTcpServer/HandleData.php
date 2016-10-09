<?php
use \GatewayWorker\Lib\Gateway;
use Events\Lbs\EventsLbsCommon;
use Events\WeatherService\WeatherService;
use Workerman\Connection\AsyncTcpConnection;
use \GatewayWorker\Lib\Db;
class HandleData {
	public static function pack_data($data) {
		$data_len = sprintf ( "%04x", strlen ( $data ) );
		return $data_len . $data;
	}

	public static function async($user_id,$task_data,$ext=null)
	{
		$task_connection=new AsyncTcpConnection('Text://127.0.0.1:7272');
		$task_connection->send($task_data);
		$task_connection->onMessage=function($task_connection,$task_result)use($user_id)
		{
			//echo "task_result:".$task_result.PHP_EOL;
			if(!empty($task_result)){
				$json=json_decode($task_result,true);
				if($json){
					foreach ($json as $arr){
						foreach ($arr as $tel) {

							//echo $tel.PHP_EOL;
							$tk_notiy=array('id'=>$tel,'cmd'=>'TK','info'=>1);
							Gateway::sendToUid($tel,self::pack_data(json_encode($tk_notiy)));
						}
					}

				}else{
					Gateway::sendToUid($user_id,$task_result);
				}
			}
			$task_connection->close();
		};
		$task_connection->connect();
	}


	/**
	* @author wzb<wangzhibin_x@foxmail.com>
	* @date Sep 6, 2016 2:37:37 PM
	* 处理异步任务数据
	*/
	public static function handle_async_data($connection,$message)
	{
		$result='';
		$msg_array = explode ( '*', $message );
		//echo count($msg_array).PHP_EOL;
		$msg_msg = explode ( ',', $msg_array[2] );
		$cmd = $msg_msg [0];
		$imei = $msg_array [1];
		switch($cmd){
			case 'WEATHER':
				$weather_service = new WeatherService ();
				$rs_weather = $weather_service->parse ( $message );
				$rs_wea = 'CS*' . $imei . '*WEATHER,';
				$result=self::pack_data($rs_wea . $rs_weather);
				break;
			case 'UD':
				$ud_parse = new EventsLbsCommon ();
				$ud_parse->parse ( $message );
				break;
			case 'TK':
				//存入数据库
				$filename=$msg_msg[1];
				//echo $filename.PHP_EOL;
				$db=Db::instance('db_watch');
				$app_user=$db->select('app_id')->from('watch_app_watch')->where("watch_imei=$imei")->query();
				//var_dump($app_user);
				$result=json_encode($app_user);
				foreach ($app_user as $arr){
					foreach ($arr as $tel) {
						$db->insert('watch_message')->cols(array('user_id'=>$tel,'imei'=>$imei,'file'=>$filename,'stamp'=>time()))->query();

					}
				}

				break;
			default:
				break;
		}
		$connection->send($result);
	}

	/**
	 * [handle_watch_data description]
	 *
	 * @author wzb<wangzhibin_x@qq.com>
	 *         @DateTime 2016-07-11T20:05:24+0800
	 *         @ 处理手表终端发送过来的数据
	 *         LENGTHCS*YYYYYYYYYY*LK,msg 格式,YYYY是15位数字Imei号
	 */
	public static function handle_watch_data($client_id, $message) {
		static $filename = '1.amr';
		static $imei;

		// echo $message.' xx'.PHP_EOL;

		$msg_array = explode ( '*', $message );
		if (count ( $msg_array ) < 3) {
			return;
		}
		$type = $msg_array [0];
		$imei = $msg_array [1];

		$msg_msg = explode ( ',', $msg_array [2] );
		$cmd = $msg_msg [0];

		Gateway::bindUid ( $client_id, $imei );

		switch ($cmd) {
			// 链路保持
			case 'LK' :
				$rs_lk = 'CS*' . $imei . '*LK';
				// $rs_lk_len=sprintf("%04x",strlen($rs));
				Gateway::sendToUid ( $imei, self::pack_data ( $rs_lk ) );
				return;
			// 位置上报
			case 'UD' :
				$rs_ud = 'CS*' . $imei . '*UD';
				// $rs_ud_len=sprintf("%04x",strlen($rs_ud));
				Gateway::sendToUid ( $imei, self::pack_data ( $rs_ud ) );
				//$ud_parse = new EventsLbsCommon ();
				//$ud_parse->parse ( $message );
				//用异步任务处理
				self::async($imei,$message);
				return;
			// 语音
			case 'TK' : // lencs*imei*tk,amr数据

				$rs_tk = 'CS*' . $imei . '*TK,1';
				// $rs_tk_len=sprintf("%04x",strlen($rs_tk));
				Gateway::sendToUid ( $imei, self::pack_data ( $rs_tk ) );
				$filename=$imei.'_'.time(). '.amr';
				$filepath = '/var/www/html/core/media/childwatch/' . $filename;
				$head_len = 22;
				$amr = substr ( $message, $head_len, strlen ( $message ) - $head_len );
				file_put_contents ( $filepath, $amr, FILE_APPEND );
				//异步处理录音文件
				$async_msg='CS*'.$imei.'*TK,'.$filename;
				self::async($imei,$async_msg);
				return;

			case 'SYSTEMTIME' :
				$rs_st = 'CS*' . $imei . '*SYSTEMTIME,' . time () . '000';
				// $rs_st_len=sprintf("%04x",strlen($rs_st));
				Gateway::sendToUid ( $imei, self::pack_data ( $rs_st ) );
				return;
			case 'WEATHER' :
				$rs_wea = 'CS*' . $imei . '*WEATHER,';
				// $rs_wea_len=sprintf("%04x",strlen($rs_wea));
				Gateway::sendToUid ( $imei, self::pack_data ( $rs_wea . '1' ) );

// 				$weather_service = new WeatherService ();
// 				$rs_weather = $weather_service->parse ( $message );
// 				Gateway::sendToUid ( $imei, self::pack_data ( $rs_wea . $rs_weather ) );
				//采用异步任务处理curl耗时任务
				self::async($imei,$message);
				return;
			case 'TEST':
				$rs_test=array('id'=>'12345678901','cmd'=>'test','info'=>'hahah123');
				//echo json_encode($rs_test).PHP_EOL;
				Gateway::sendToUid ( '12345678901', self::pack_data ( json_encode($rs_test)) );
				return;
				break;
			default :
				return;
		}
	}

	/**
	* @author wzb<wangzhibin_x@foxmail.com>
	* @date Sep 6, 2016 4:09:09 PM
	* $message 为json数据包{"id":"","cmd":"","imei":"","info":""}
	* amr音频文件转为base64
	*/
	public static function handle_server_data($client_id, $message) {
		$message_data = json_decode ( $message, true );
		if(!$message_data){
			return;
		}
		$id=$message_data['id'];
		Gateway::bindUid ( $client_id, $id );
		//echo $message_data ['cmd'] . PHP_EOL;
		switch ($message_data ['cmd']) {
			case 'ping':
				Gateway::sendToUid($id, self::pack_data($message));
				return;
				break;

			case 'tk':
				//判断imei是否在线
				//echo '## tk'.PHP_EOL;
				$imei=$message_data['imei'];
				$amr=base64_decode($message_data['info']);
				//file_put_contents("1234.amr",$amr,FILE_APPEND);
				if(!Gateway::isUidOnline($imei)){
					$rs_tk=array('id'=>$id,'cmd'=>'tk','imei'=>$imei,'info'=>'offline');
					Gateway::sendToUid($id, self::pack_data(json_encode($rs_tk)));
				}else{
					$rs_tk='CS*'.$imei.'*TK,'. $amr;
					Gateway::sendToUid($imei, self::pack_data($rs_tk));
					$rs_tk1=array('id'=>$id,'cmd'=>'tk','imei'=>$imei,'info'=>'ok');
					Gateway::sendToUid($id, self::pack_data(json_encode($rs_tk1)));
				}

				break;
			case 'test' :
				if ($message_data ['info'] == 'tk') {
					$file = file_get_contents ( 'test.amr' );
					$rs = 'CS*201508220452222*TK,' . $file;
					// $rs_len=sprintf("%04x",strlen($rs));
					Gateway::sendToAll ( self::pack_data ( $rs ) );
				} else {
					Gateway::sendToAll ( $message_data ['info'] );
				}
				break;
			default :
				// code...
				break;
		}
	}
}

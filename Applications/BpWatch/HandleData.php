<?php
require_once __DIR__ . '/../GlobalData/src/Client.php';
require_once __DIR__ . '/../Statistics/Clients/StatisticClient.php';
use \GatewayWorker\Lib\Gateway;
use Events\Lbs\EventsLbsCommon;
use Events\WeatherService\WeatherService;
use Workerman\Connection\AsyncTcpConnection;
use \GatewayWorker\Lib\Db;
use \Workerman\Lib\Timer;
class HandleData {

	public static function pack_data($data) {
		$data_len = sprintf ( "%04x", strlen ( $data ) );
		return $data_len . $data;
	}

	public static function async($user_id,$task_data,$ext=null)
	{
		$task_connection=new AsyncTcpConnection('Text://127.0.0.1:10001');
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
		$media_type=0;
		$sys_time=date("Y-m-d H:i:s");
		$db=Db::instance('db_watch');
		switch($cmd){
			case 'WEATHER':
				$weather_service = new WeatherService ();
				$rs_weather = $weather_service->parse ( $message );
				$rs_wea = 'HA*' . $imei . '*WEATHER,';
				$result=self::pack_data($rs_wea . $rs_weather);
				break;
			case 'UD':
				$ud_parse = new EventsLbsCommon ();
				$ud_parse->parse ( $message );
				break;
			case 'AL':
				$al_parse=new EventsLbsCommon ();
				$al_parse->parse ( $message );
				break;
			case 'TK':
				//存入数据库
				$filename=$msg_msg[1];
				$media_type='0';
				//echo $filename.PHP_EOL;
				$app_user=$db->select('app_id')->from('watch_app_watch')->where("watch_imei=$imei")->query();
				//var_dump($app_user);
				$result=json_encode($app_user);
				foreach ($app_user as $arr){
					foreach ($arr as $tel) {
						$db->insert('watch_message')->cols(array('type'=>$media_type,'user_id'=>$tel,'imei'=>$imei,'file'=>$filename,'stamp'=>time(),'datetime'=>$sys_time))->query();

					}
				}

				break;
			case 'PHOTO':
				//存入数据库
				$filename=$msg_msg[1];
				$media_type='1';
				//echo $filename.PHP_EOL;

				$app_user=$db->select('app_id')->from('watch_app_watch')->where("watch_imei=$imei")->query();
				//var_dump($app_user);
				$result=json_encode($app_user);
				foreach ($app_user as $arr){
					foreach ($arr as $tel) {
						$db->insert('watch_message')->cols(array('type'=>$media_type,'user_id'=>$tel,'imei'=>$imei,'file'=>$filename,'stamp'=>time(),'datetime'=>$sys_time))->query();

					}
				}
				break;
			case 'HR':
				$health_type=0;
				$hr_data=$msg_msg[1];
				$db->insert('watch_health_data')->cols(array('type'=>$health_type,'hr'=>$hr_data,'imei'=>$imei,'unix_time'=>time(),'create_time'=>$sys_time))->query();
				break;
			case 'BP':
				$health_type=1;
				$hr_data=$msg_msg[3];
				$bph=$msg_msg[1];
				$bpl=$msg_msg[2];
				$db->insert('watch_health_data')->cols(array('type'=>$health_type,'hr'=>$hr_data,'bph'=>$bph,'bpl'=>$bpl,'imei'=>$imei,'unix_time'=>time(),'create_time'=>$sys_time))->query();
				break;
			case 'ECG':
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
		//for debug
		if(true){
			$debug_info=$message.' --'.date("Y-m-d H:i:s");
			Gateway::sendToGroup('debug1',$debug_info);
		}
		//end

		$msg_array = explode ( '*', $message );
		if (count ( $msg_array ) < 3) {
			return;
		}
		$type = $msg_array [0];
		$imei = $msg_array [1];

		$msg_msg = explode ( ',', $msg_array [2] );
		$cmd = $msg_msg [0];

		Gateway::bindUid ( $client_id, $imei );

		/*if($cmd != 'LK'){
			$global = new GlobalData\Client('127.0.0.1:2207');
			//$global->$imei=time();
			//$_SESSION[$imei]=time();
			echo '11'.PHP_EOL;
			if(isset($global->$imei)){
				echo '22'.PHP_EOL;
				Timer::del($global->$imei);
			}
		}*/// for newtest
		
		//statistics
		// 统计开始
		StatisticClient::tick("bp_watch", 'watch_data');
		// 统计的产生，接口调用是否成功、错误码、错误日志
		$success = true; $code = 0; $msg = '';
		// 上报结果
		$msg=$imei.':'.$cmd;
		StatisticClient::report('bp_watch', 'watch_data', $success, $code, $msg);
		//end statistics
		
		switch ($cmd) {
			// 链路保持
			case 'LK' :
				$rs_lk = 'HA*' . $imei . '*LK';
				// $rs_lk_len=sprintf("%04x",strlen($rs));
				Gateway::sendToUid ( $imei, self::pack_data ( $rs_lk ) );
				return;
			case 'HR':
				$rs='HA*'.$imei.'*HR';
				Gateway::sendToUid ( $imei, self::pack_data ( $rs ) );
				self::async($imei,$message);
				return;
			case 'BP':
				$rs='HA*'.$imei.'*BP';
				Gateway::sendToUid ( $imei, self::pack_data ( $rs ) );
				self::async($imei,$message);
				return;
			case 'ECG':
				$rs='HA*'.$imei.'*ECG';
				Gateway::sendToUid ( $imei, self::pack_data ( $rs ) );
				self::async($imei,$message);
				return;
			case 'SLEEP':
				$rs='HA*'.$imei.'*SLEEP';
				Gateway::sendToUid ( $imei, self::pack_data ( $rs ) );
				return;
			// 位置上报
			case 'UD' :
				$rs_ud = 'HA*' . $imei . '*UD';
				// $rs_ud_len=sprintf("%04x",strlen($rs_ud));
				Gateway::sendToUid ( $imei, self::pack_data ( $rs_ud ) );
				//$ud_parse = new EventsLbsCommon ();
				//$ud_parse->parse ( $message );
				//用异步任务处理
				self::async($imei,$message);
				return;
			case 'AL':
				$rs='HA*'.$imei.'*AL';
				Gateway::sendToUid ( $imei, self::pack_data ( $rs ) );
				self::async($imei,$message);
				return;
			// 语音
			case 'TK' : // lencs*imei*tk,amr数据
				if(strlen($message) == 23){
					return;
				}
				//echo "tk".PHP_EOL;
				$rs_tk = 'HA*' . $imei . '*TK,1';
				// $rs_tk_len=sprintf("%04x",strlen($rs_tk));
				Gateway::sendToUid ( $imei, self::pack_data ( $rs_tk ) );
				$filename=$imei.'_'.time(). '.amr';
				$filepath = '/var/www/html/core/media/hawatch/' . $filename;
				$head_len = 22;
				$amr = substr ( $message, $head_len, strlen ( $message ) - $head_len );
				file_put_contents ( $filepath, $amr, FILE_APPEND );
				//异步处理录音文件
				$async_msg='HA*'.$imei.'*TK,'.$filename;
				self::async($imei,$async_msg);
				return;

			case 'SYSTEMTIME' :
				$rs_st = 'HA*' . $imei . '*SYSTEMTIME,' . time () . '000';
				// $rs_st_len=sprintf("%04x",strlen($rs_st));
				Gateway::sendToUid ( $imei, self::pack_data ( $rs_st ) );
				return;
			case 'WEATHER' :
				$rs_wea = 'HA*' . $imei . '*WEATHER,';
				// $rs_wea_len=sprintf("%04x",strlen($rs_wea));
				Gateway::sendToUid ( $imei, self::pack_data ( $rs_wea . '1' ) );

// 				$weather_service = new WeatherService ();
// 				$rs_weather = $weather_service->parse ( $message );
// 				Gateway::sendToUid ( $imei, self::pack_data ( $rs_wea . $rs_weather ) );
				//采用异步任务处理curl耗时任务
				self::async($imei,$message);
				return;
			case 'PHOTO':
				$photo_header=25;
				$photo_jpg=substr ( $message, $photo_header, strlen ( $message ) - $photo_header );
				$p_filename=$imei.'_'.time(). '.jpg';
				$p_filepath = '/var/www/html/core/media/hawatch/' . $p_filename;
				file_put_contents ( $p_filepath, $photo_jpg, FILE_APPEND );
				$rs_p='HA*' . $imei . '*PHOTO,1';
				Gateway::sendToUid ( $imei, self::pack_data ( $rs_p ) );
				//异步处理
				$async_p_msg='HA*'.$imei.'*PHOTO,'.$p_filename;
				self::async($imei,$async_p_msg);
				// if($msg_msg[1]==0){
				// 	//手表主动拍照上传
				// 	$rs_p='CS*' . $imei . '*PHOTO,1';
				// 	Gateway::sendToUid ( $imei, self::pack_data ( $rs_p ) );
				// }else if($msg_msg[1]==1){
				// 	//app控制手表拍照上传	,服务器不用回复手表
				// }
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
		//statistics
		// 统计开始
		StatisticClient::tick("bp_watch", 'app_data');
		// 统计的产生，接口调用是否成功、错误码、错误日志
		$success = true; $code = 0; $msg = '';
		// 上报结果
		$msg=$id.':'.$message_data ['cmd'];
		StatisticClient::report('bp_watch', 'app_data', $success, $code, $msg);
		//end statistics
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
					$rs_tk='HA*'.$imei.'*TK,'. $amr;
					Gateway::sendToUid($imei, self::pack_data($rs_tk));
					$rs_tk1=array('id'=>$id,'cmd'=>'tk','imei'=>$imei,'info'=>'ok');
					Gateway::sendToUid($id, self::pack_data(json_encode($rs_tk1)));
				}

				break;
			case 'test' :
				if ($message_data ['info'] == 'tk') {
					$file = file_get_contents ( 'test.amr' );
					$rs = 'HA*201508220452222*TK,' . $file;
					// $rs_len=sprintf("%04x",strlen($rs));
					Gateway::sendToAll ( self::pack_data ( $rs ) );
				} else {
					$tmsg=$message_data ['info'];
					$tmsg_array = explode ( '*', $tmsg );
					$timei=$tmsg_array[1];
					//Gateway::sendToAll ( $message_data ['info'] );
					Gateway::sendToUid($timei,$tmsg);
				}
				break;
			case 'newtest':
				$imei=$message_data['imei'];
				$rs='HA*123456789012345*'.$message_data['info'];
				Gateway::sendToUid($imei, self::pack_data($rs));
				$global = new GlobalData\Client('127.0.0.1:2207');
				Timer::del($global->$imei);
				$global->$imei=Timer::add(5,function()use($imei,$rs,$global){
					static $count=0;
					if($count>1){
						Timer::del($global->$imei);
						$client_arr=Gateway::getClientIdByUid($imei);
						foreach ($client_arr as $id) {
							# code...
							Gateway::closeClient($id);

						}

					}
					$time_now=time();
				//	$global = new GlobalData\Client('127.0.0.1:2207');
					//if (!isset($global->$imei)) {
					if (!isset($_SESSION[$imei])) {
						$_SESSION[$imei] = $time_now;
						//continue;
					}
					echo $time_now." --- ".$_SESSION[$imei].'  imei:'.$imei.'  count:'.$count.PHP_EOL;
					if($time_now - $_SESSION[$imei] >20){
						Gateway::sendToUid($imei, self::pack_data($rs));
						$_SESSION[$imei]=time();
						$count++;
					}

				});
				break;
			default :
				// code...
				break;
		}
	}
}

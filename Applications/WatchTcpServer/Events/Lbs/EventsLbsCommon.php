<?php

/**
 *定位数据处理类
 *
 */
namespace Events\Lbs;
use Wtools\CUrl;
use \GatewayWorker\Lib\Db;

//test
//$data = "CS*201508220451111*UD,040816,123951,V,0,N,0,E,0,0,0.0000000,0,4,100,0,0,00000000,1,255,460,0,10145,3682,5,1,TP-LINK415,b0:d5:9d:5d:43:6e,-72";
//EventsLbsCommon::parse($data);

// $json='{"ret":200,"data":{"status":"1","info":"OK","infocode":"10000","result":{"type":"4","location":"113.9760327,22.559372","radius":"550","desc":"广东省 深圳市 南山区 龙珠五路 靠近桃源派出所(龙珠五路)","country":"中国","province":"广东省","city":"深圳市","citycode":"0755","adcode":"440305","road":"龙珠五路","street":"龙珠五路","poi":"桃源派出所(龙珠五路)"}},"msg":""}';
// $rs=json_decode($json,true);
// var_dump($rs);
// var_dump('222'.$rs['data']['result']['type']);

class EventsLbsCommon {
	
	
	/**
	 * [解析定位数据]
	 *
	 * @author wzb<wangzhibin_x@qq.com>
	 *         @DateTime 2016-08-18T11:45:54+0800
	 * @return [type] [description]
	 */
	public static function parse($data) {
		$ud_content=$data;
		$data_arr = explode ( ',', $data );
		//var_dump ( $data_arr );
		$imei = substr ( $data_arr [0], 3, 15 );
		$date = $data_arr [1];
		$time = $data_arr [2];
		$watch_time = substr ( $date, 4, 2 ) . '-' . substr ( $date, 2, 2 ) . '-' . substr ( $date, 0, 2 ) . ' ' . substr ( $time, 0, 2 ) . ':' . substr ( $time, 2, 2 ) . ':' . substr ( $time, 4, 2 );
		$battery = $data_arr [13];
		$watch_status = $data_arr [16];
		
		$state_num = $data_arr [17];
		$mcc = $data_arr [19];
		$mnc=$data_arr[20];
		$lac=$data_arr[21];
		$cellid=$data_arr[22];
		$signle=$data_arr[23];
		$bts_others='';
		for($i=1;$i<$state_num;$i++){
			$id=24+($i-1)*3;
			$bts_others .=$mcc.','.$mnc.','.$data_arr[$id].','.$data_arr[$id+1].','.$data_arr[$id+2].'|';
		}
		$bts_main=$mcc.','.$mnc.','.$lac.','.$cellid.','.$signle;

		$wifi_num=$data_arr[24+($state_num-1)*3];
		$wifi_info='';
		for($j=1;$j<=$wifi_num;$j++){
			$wifi_index=25+($state_num-1)*3+($j-1)*3;
			$wifi_info.=$data_arr[$wifi_index+1].','.$data_arr[$wifi_index+2].','.$data_arr[$wifi_index].'|';
		}
		
		$gps_lat='';
		$gps_lon='';
		$location_lat='';
		$location_lon='';
		$location_type='';
		$location_content='';
		
		$gps_status = $data_arr [3];
		if ($gps_status == 'A') {
			// gps定位
			if($data_arr[5] == 'S'){
				$location_lat=$gps_lat=0-$data_arr[4];
			}else{
				$location_lat=$gps_lat=$data_arr[4];
			}
			
			if($data_arr[7] == 'E'){
				$location_lon=$gps_lon=0-$data_arr[6];
			}else{
				$location_lon=$gps_lon=$data_arr[6];
			}
			$location_type=0;
			
		}else if ($gps_status == 'V') {
			// 基站wifi定位
			$result=self::get_lbs($bts_main,$bts_others, $wifi_info);
			if($result){
				$rs=json_decode($result,true);
				$rs_type=$rs['data']['result']['type'];
				if($rs_type == 3){
					//wifi定位
					$location_type=1;
				}else if($rs_type == 4){
					//基站定位
					$location_type=2;
				}else{
					$location_type=$rs_type;
				}
				if($location_type != 0){
					$location=$rs['data']['result']['location'];
					$location_lon=explode(',', $location)[0];
					$location_lat=explode(',', $location)[1];
					$location_content=$rs['data']['result']['desc'];
				}
			}
			//echo $result.PHP_EOL;
		}
		
		//insert to sql
		$db_watch=Db::instance('db_watch');
		$sys_time=date("Y-m-d H:i:s");
		$sql="insert into watch_info (imei,gps_lon,gps_lat,watch_time,
				system_time,location_lon,location_lat,location_content,
				location_type,ud_content,battery) values ('$imei','$gps_lon',
				'$gps_lat','$watch_time','$sys_time','$location_lon','$location_lat',
				'$location_content','$location_type','$ud_content','$battery')";
		//echo $sql;
		$db_watch->query($sql);
		
	}
	
	
	/**
	* @author wzb<wangzhibin_x@foxmail.com>
	* @date Aug 19, 2016 6:30:54 PM
	* 调用高德api 使用基站wifi定位
	*/
	public static function get_lbs($bts,$nearbts,$wifi_info)
	{
		$curl=new CUrl();
		$api='http://lib.huayinghealth.com/lib-x/?service=lbs.data&';
		$url=$api.'bts='.$bts.'&nearbts='.$nearbts.'&macs='.$wifi_info;
		//echo $url.PHP_EOL;
		return $curl->get($url);
	}
	
	
	/**
	* @author wzb<wangzhibin_x@foxmail.com>
	* @date Aug 21, 2016 11:43:29 AM
	* 存入数据库
	*/
	public static function save_db()
	{
		
		
	}
}

<?php

/**
 *定位数据处理类
 *
 */
namespace Events\Lbs;
use Events\CUrl;
//test
//$data = "CS*201508220451111*UD,040816,123951,V,0,N,0,E,0,0,0.0000000,0,4,100,0,0,00000000,1,255,460,0,10145,3682,5,1,TP-LINK415,b0:d5:9d:5d:43:6e,-72";
//EventsLbsCommon::parse($data);
class EventsLbsCommon {
	
	
	/**
	 * [解析定位数据]
	 *
	 * @author wzb<wangzhibin_x@qq.com>
	 *         @DateTime 2016-08-18T11:45:54+0800
	 * @return [type] [description]
	 */
	public static function parse($data) {
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
			$wifi_info.=$data_arr[$wifi_index].','.$data_arr[$wifi_index+1].','.$data_arr[$wifi_index+2].'|';
		}
		
		echo $bts_all.PHP_EOL;
		echo $wifi_info.PHP_EOL;
		$gps_lat='';
		$gps_lon='';
		$location_lat='';
		$location_lon='';
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
			
		}else if ($gps_status == 'V') {
			// 基站wifi定位
			$result=self::get_lbs($bts_main,$bts_others, $wifi_info);
			echo $result;
		}
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
		echo $url.PHP_EOL;
		return $curl->get($url);
	}
}

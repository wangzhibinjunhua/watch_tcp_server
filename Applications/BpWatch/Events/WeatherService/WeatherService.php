<?php
/**
* @author wzb<wangzhibin_x@foxmail.com>
* @date Aug 21, 2016 3:08:42 PM
* 从网络获取天气数据
*/
namespace Events\WeatherService;
use \GatewayWorker\Wtools\CUrl;


class WeatherService
{
	public static function parse($data)
	{
		$data_arr = explode ( ',', $data );
		//update by wzb 20161206 增加了时间,故都后移两位
		$gps_status=$data_arr[1+2];
		$cityname='';
		if($gps_status == 'A'){
			//gps 定位
			$gps_lon_arr=explode('.', $data_arr[3+2]);
			$gps_lon=$gps_lon_arr[0].'.'.substr($gps_lon_arr[1], 0,6);
			$gps_lat_arr=explode('.', $data_arr[2+2]);
			$gps_lat=$gps_lat_arr[0].'.'.substr($gps_lat_arr[1], 0,6);
			$cityname=self::get_cityname_by_gps($gps_lon.','.$gps_lat);
			//file_put_contents("temp.log",$cityname.PHP_EOL,FILE_APPEND);
			if($cityname == null){
				return '2';
			}
			return self::get_weather_by_cityname($cityname);
		}else if($gps_status == 'V'){
			//wifi 基站定位
			$cityname=self::get_cityname_by_wifi($data);
			if($cityname == null){
				return '2';
			}
			return self::get_weather_by_cityname($cityname);
		}
	}

	/**
	* @author wzb<wangzhibin_x@foxmail.com>
	* @date Aug 22, 2016 2:25:50 PM
	* 根据经纬度坐标获取地理位置信息,城市名称
	* $location : 经纬度坐标
	* return: 城市名
	*/
	public static function get_cityname_by_gps($location)
	{
		$curl=new CUrl();
		$api='http://lib.huayinghealth.com/lib-x/?service=geocode.regeo&';
		$url=$api.'location='.$location;
		$rs=$curl->get($url);
		$rs_arr=json_decode($rs,true);
		if($rs_arr['data'] == null){
			return null;
		}
		return $rs_arr['data']['regeocode']['addressComponent']['city'];
	}

	/**
	* @author wzb<wangzhibin_x@foxmail.com>
	* @date Aug 22, 2016 2:27:18 PM
	* 根据基站wifi信息获取地理位置信息,城市名称
	* $wifi: 基站和wifi信息
	* return:城市名
	*/
	public static function get_cityname_by_wifi($data)
	{
		//update by wzb 20161206 增加了时间,故都后移两位,增加了gsm时延,后面再后移一位
		$data_arr = explode ( ',', $data );
		$state_num = $data_arr [4+2];
		$mcc = $data_arr [5+2];
		$mnc=$data_arr[6+2+1];
		$lac=$data_arr[7+2+1];
		$cellid=$data_arr[8+2+1];
		$signle=$data_arr[9+2+1];
		$bts_others='';
		for($i=1;$i<$state_num;$i++){
			$id=10+2+1+($i-1)*3;
			$bts_others .=$mcc.','.$mnc.','.$data_arr[$id].','.$data_arr[$id+1].','.$data_arr[$id+2].'|';
		}
		$bts_main=$mcc.','.$mnc.','.$lac.','.$cellid.','.$signle;

		$wifi_num=$data_arr[10+2+1+($state_num-1)*3];
		$wifi_info='';
		for($j=1;$j<=$wifi_num;$j++){
			$wifi_index=11+2+1+($state_num-1)*3+($j-1)*3;
			$wifi_info.=$data_arr[$wifi_index+1].','.$data_arr[$wifi_index+2].','.$data_arr[$wifi_index].'|';
		}
		//echo 'bts='.$bts_main.'&nearbts='.$bts_others.'$macs='.$wifi_info;
		$curl=new CUrl();
		$api='http://lib.huayinghealth.com/lib-x/?service=lbs.data&';
		$url=$api.'bts='.$bts_main.'&nearbts='.$bts_others.'&macs='.$wifi_info;
		$rs=$curl->get($url);
		$rs_arr=json_decode($rs,true);
		if($rs_arr['data'] == null){
			return null;
		}
		return $rs_arr['data']['result']['city'];

	}

	/**
	* @author wzb<wangzhibin_x@foxmail.com>
	* @date Aug 22, 2016 2:32:11 PM
	* 根据城市名称获取天气数据
	* $cityname 城市名称 比如北京,深圳
	* return 天气数据 30,1
	* for 儿童手表: 30表示30摄氏度
	* 	0— 晴
	*	1— 多云
	*	2— 阴
	*	3— 雨(包含雨夹雪类天气)
	*	4— 暴雨
	*	5—雪
	*/
	public static function get_weather_by_cityname($cityname)
	{
		$curl=new CUrl();
		$api='http://lib.huayinghealth.com/lib-x/?service=weather.get_weather_info&';
		$url=$api.'cityname='.$cityname;
		$rs=$curl->get($url);
		$rs_arr=json_decode($rs,true);
		if($rs_arr['data'] == null){
			return '2';
		}
		$temperature=$rs_arr['data']['result']['data']['realtime']['weather']['temperature'];
		$wea_info=$rs_arr['data']['result']['data']['realtime']['weather']['info'];
		$wea_id='0';
		if(strpos($wea_info,'云') !== false){
			$wea_id='1';
		}else if(strpos($wea_info,'晴') !== false){
			$wea_id='0';
		}else if(strpos($wea_info,'暴雨') !== false){
			$wea_id='4';
		}else if(strpos($wea_info,'雨') !== false){
			$wea_id='3';
		}else if(strpos($wea_info,'雪') !== false){
			$wea_id='5';
		}
		return $temperature.','.$wea_id;

	}


}

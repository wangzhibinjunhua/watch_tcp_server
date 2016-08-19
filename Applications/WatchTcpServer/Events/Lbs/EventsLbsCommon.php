<?php

/**
*定位数据处理类
*
*/
namespace Events\Lbs;
class EventsLbsCommon
{

	/**
	 * [解析定位数据]
	 * @Author   wzb<wangzhibin_x@qq.com>
	 * @DateTime 2016-08-18T11:45:54+0800
	 * @return   [type]                   [description]
	 */
	public static function parse($data)
	{
		$data_arr=explode(',', $data);
		$imei=substr($data_arr[0],3,15 );
		echo $imei.__FILE__;
	}
	

}

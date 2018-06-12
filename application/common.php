<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
 /**
     * 上传约驾 活动照片
     * @param   $uid  约驾活动发起者的id
     * @param   $aid  约驾活动id
     * @param   $files 上传的照片 
     */
    function uploads($aid,$uid,$files)
    { 
    	foreach($files as $file){
        	// 移动到框架应用根目录/uploads/ 目录下
        	$info = $file->validate(['ext'=>'jpg,png,gif'])->move( '../uploads');
        	if($info){
        		//照片上传成功
        		$pic = 'http://localhost/yuej/uploads/'.$info->getSaveName();
        		$data = [
        			'aid' => $aid,
        			'creator_id' => $uid,
        			'pic' => $pic
        		];
        		$res = Db::table('yue_activity_picture')
        		       ->insert($data);
        		$msg = ['status' => 1,'msg' => '上传照片成功'];
        	} else {
            	// 上传失败获取错误信息
            	$message = $file->getError();
            	$msg = ['status' => 0,'msg' => $message];
        	}    
    	}
    	return $msg;
    }
    /**
     * 根据用户id获取用户在yue_user中的信息
     * @param  $uid 约驾小程序中的用户id
     * @return $yue_user 约驾小程序用户信息
     */
    function selYueUser($uid){
        $yue_user = Db::table('yue_user')
                     ->where('id',$uid)
                     ->find();
        return $yue_user;
    }
    /**
	 * 获取城市的经纬度
	 * @param  地点名
	 * @return $address
	 */
	function map($address)
	{
		$ak = 'p4n0i3mPmFxWtUowbdtEBhBcbXvs5Pw0';
		$url = 'http://api.map.baidu.com/geocoder/v2/?address='.$address.'&output=json&ak='.$ak;
		$weixin = file_get_contents($url);
        $jsondecode = json_decode($weixin);//对json格式的字符串进行编码
        $arr = get_object_vars($jsondecode);//转换成数组
        $arr2 = get_object_vars($arr['result']);
        $arr3 = get_object_vars($arr2['location']);
        return $arr3;  
	}
	/**
	 * 根据两个点的经纬度求距离
	 * @param string  $lat1     地点1的纬度
	 * @param string  $lng1     地点1的经度
	 * @param string  $lat2     地点2的纬度
	 * @param string  $lng2     地点2的经度
	 * @param integer $decimal  小数点位数
	 * @return  $distance 这两个点的距离
	 */
	function getDistance($lat1,$lng1,$lat2,$lng2,$decimal = 2) 
	{
        $pi = 3.1415926535898;//π
        $radius = 6371;//地球半径
        $radLat1 = $lat1 * $pi / 180.0;
        $radLat2 = $lat2 * $pi / 180.0;
        $a = $radLat1 - $radLat2;
        $b = ($lng1 * $pi / 180.0) - ($lng2 * $pi / 180.0);
        //asin 反正弦 sqrt 平方根 pow 指数 sin 正弦 cos 余弦
        $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2))) * $radius;
        return $juli = round($s, $decimal);
    }
    /**
     * 获取用户的位置信息
     * @param  $uid 用户id
     * @return $user_location 经纬度
     */
    function getUserLocation($uid){
    	$user_location = Db::table('yue_user')
    	                 ->where('id',$uid)
    	                 ->field('lat,lng')
    	                 ->find();
    	return $user_location;
    }
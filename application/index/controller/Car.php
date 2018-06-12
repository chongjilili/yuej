<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
/**
 * 获取车的信息
 */
class Car extends Controller{

	/**
	 * 获取所有的车的品牌
	 * @return  车的品牌
	 */
	public function menu(){
		$car = Db::table('co_car_menu')->field('id,name')->select();
		return $car;
	}
	/**
	 * 选中车的品牌后显示车的品牌中里面的所有型号
	 * @return 车的型号
	 */
	public function type()
	{
		$brand = input('get.id');
		return Db::table('co_car_cate')->where('brand',$brand)->field('id,type')->select();
	}
	/**
	 * 增加我的汽车
	 * @return $msg 状态值和消息
	 */
	public function addMyCar()
	{
		/* 
		/^[\u4e00-\u9fa5]{1}[A-Z]{1}[A-Z_0-9]{5}$/;
		正则验证车牌号
		*/
		//获取信息
		$data = input('post.');
		$res = Db::table('yue_user_car')->strict(false)->insert($data);
		if($res){
			$msg = ['status' => 1,'添加我的汽车成功'];
		} else {
			$msg = ['status' => 0,'添加我的汽车失败，请稍后重试'];
		}
		return $msg;
	}
	/**
	 * 我的汽车列表
	 * @return 我已经添加的汽车
	 */
	public function myCarList(){
		$uid = input('get.uid');
		$res = Db::table('yue_user_car')
		       ->where('uid',$uid)
		       ->select();
		if(!$res || empty($res)){
			$msg = ['status' => 0,'msg' => '您尚未添加我的汽车，请添加'];
		} else {
			$msg = ['status' => 1,'msg' => '这是您的汽车列表页',$res];
		}
		return $msg;
	}
}


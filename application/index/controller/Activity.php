<?php 
namespace app\index\controller;

use think\Db;
use think\Controller;

class Activity extends Controller
{
	/**
	 * 约驾活动列表页
	 * @return 约驾活动信息
	 */
	public function list()
	{
		//当前用户id
		$uid = input('get.uid');
		$user_lat = getUserLocation($uid)['lat'];
		$user_lng = getUserLocation($uid)['lng'];
		$activity = Db::table('yue_activity')
		            ->alias('a')
		            ->join('yue_user u','a.creator_id = u.id')
		            ->field('a.id,title,path,start_time,anumber,vip,origin,a.create_time,car_type,
		            	head_pic,sex,name,origin_lat,origin_lng,a.status')
		            ->order('vip desc')
		            ->where('a.anumber','>',0)
					->where('a.status',1)
		            ->select();
		//计算出约驾活动出发地和当前用户的距离
		foreach ($activity as $key => $value) {
			$activity[$key]['distance'] = getDistance($user_lat,$user_lng,$value['origin_lat'],$value['origin_lng']); 
		}
		$count = count($activity);
		//把距离最小的放到前面
		//双重for循环, 每循环一次都会把一个最大值放最后
		for ($i = 0; $i < $count - 1; $i++) 
		{	
			//由于每次比较都会把一个最大值放最后, 所以可以每次循环时, 少比较一次
			for ($j = 0; $j < $count - 1 -  $i; $j++) 
			{
				if ($activity[$j]['distance'] > $activity[$j + 1]['distance']) 
				{
					$tmp = $activity[$j];
					$activity[$j] = $activity[$j + 1];
					$activity[$j + 1] = $tmp;
				}
			}
		}
		if($activity)
		{
			$msg = ['status' =>1,'msg' =>'获取活动列表成功',$activity];
		} else {
			$msg = ['status' =>0,'msg' =>'当前区域暂无活动'];
		}
		return $msg;
	}
	/**
	 * 某个约驾活动详情
	 * @return  发起者的姓名，性别，头像，约驾活动创建时间,路线，浏览量，车型，活动详情，已参与人数，总人数，
	 */
	public function details(){
		$aid = input('get.aid');
		$uid = input('get.uid');
		$res =  Db::table('yue_activity')
		         ->alias('a')
		         ->join('yue_user u','a.creator_id = u.id')
		         ->join('yue_activity_picture p','p.aid = a.id')
		         ->field('a.id,title,sex,head_pic,name,start_time,a.create_time,wechat_id,origin,pic,path,a.creator_id,car_type,anumber,details,anumber')
		         ->where('a.id',$aid)
		         ->select();
		foreach ($res as $key => $value) {
			$pic[] = $value['pic'];
		}
		$activity = $res['0'];
		$activity['pic'] = $pic;
		//获取已经参加的人的头像
	    $taker = Db::table('yue_participant')
	             ->alias('p')
	             ->join('yue_user u','u.id = p.uid')
		         ->where('aid',$aid)
		         ->field('head_pic')
		         ->select();
		if($taker){
			foreach ($taker as $key => $value) {
				$head_pic[] = $value['head_pic'];
			}
			$activity['taker'] = $head_pic;
		} else {
			$activity['taker'] = '暂无参与者';
		}
		//判断用户是否参与过这个活动
		$isTake = Db::table('yue_participant')
		          ->where('aid',$aid)
		          ->where('uid',$uid)
		          ->find();
		if($isTake){
			//用户参加了这个活动
			$activity['isTake'] = 1;
		} else {
			//用户还没参加这个活动
			$activity['isTake'] = 0;
		}

		//查询已经报名参加这个活动的人的头像
		return $activity;
	}
	/**
	 * 检查是否添加了我的汽车
	 * @return $msg 状态值，提示信息，用户id
	 */
	public function findMyCar()
	{
		$uid = input('get.uid');
		$u_car = Db::table('yue_user_car')
			       ->where('uid',$uid)
			       ->field('id')
			       ->find();
	    if($u_car){
	    	//添加了我的汽车
	    	$msg = ['status' => 1,'msg' =>'您已添加我的汽车，可继续操作',$u_car];
	    } else {
	    	//没有添加我的汽车
	    	$msg = ['status' => 0,'msg' =>'您尚未添加我的汽车，请添加后再来','uid' => $uid];
	    }
	    return $msg;
	}
    /**
     * 发布约驾活动
     * @return $msg 成功或者失败
     */
	public function addActivity()
	{
		$data = input('post.');
		//获取当前用户id
		$data['creator_id'] = $uid = input('post.uid');
		//获取出发地
		if($validate->check($data))
		{
			$data['origin'] = input('post.origin');
			//获取出发地的经纬度
	    	$data['origin_lat'] = map($data['origin'])['lat'];
			$data['origin_lng'] = map($data['origin'])['lng'];
			$data['create_time'] = date('Y-m-d H:i:s', time());
			$data['anumber'] = $data['number'];
			//查询当前用户的openid，
			$openId = selYueUser($uid)['open_id'];
			//然后查询出他在u_user表中的id，
			$u_user = Db::table('u_user')
			         ->where('open_id',$openId)
			         ->field('id')
			         ->find();
			$u_uid = $u_user['id'];
			//然后在u_card中查找这个用户是否购买了邦保养卡
			$u_card = Db::table('u_card')
			          ->where('uid',$u_uid)
			          ->field('id')
			          ->select();
			if($u_card){
				$data['vip'] = 1;
			} else {
				$data['vip'] = 0;
			}
			$aid = Db::table('yue_activity')
		           ->strict(false)
			       ->insertGetId($data);
		    if($aid > 0)
			{
				$file = request()->file('image');
				if($file){
					$res = uploads($aid,$uid,$file);
				}
		    	$msg = ['status' => 1,'msg' => '发布约驾成功，请等待有兴趣的人联系'];
		    } else {
		    	$msg = ['status' => 0,'msg' => '发布约驾失败，请稍后尝试'];
		    }	
		} else {
			$msg = ['status' => 0,'msg' =>$validate->getError()];
		}
		return $msg;
	}

	/**
	 * 我的约驾列表
	 * @return $data 用户发布的约驾信息
	 */
	public function myActivity()
	{
		//获取当前用户的id
		$uid = input('get.uid');
		$data = Db::name('yue_activity')
		        ->where('creator_id',$uid)
		        ->field('id,title,creator_id,path,create_time')
		        ->select();
		return $data;
	}
	/**
	 * 我的某个约驾活动详情
	 * @return [type] [description]
	 */
	public function myActivityDetails()
	{
		$aid = input('get.aid');
		$uid = input('get.uid');
		$res =  Db::table('yue_activity')
		         ->alias('a')
		         ->join('yue_user u','a.creator_id = u.id')
		         ->join('yue_activity_picture p','p.aid = a.id')
		         ->field('a.id,title,sex,head_pic,name,start_time,a.create_time,wechat_id,origin,pic,path,a.creator_id,car_type,anumber,details,anumber')
		         ->where('a.id',$aid)
		         ->select();
			
		foreach ($res as $key => $value) {
			$pic[] = $value['pic'];
		}
		if(!empty($res['0'])){
		$activity = $res['0'];	
		}
		$activity['pic'] = $pic;
		//获取已经参加的人的头像
	    $taker = Db::table('yue_participant')
	             ->alias('p')
	             ->join('yue_user u','u.id = p.uid')
		         ->where('aid',$aid)
		         ->field('head_pic,p.wechat_id')
		         ->select();
		if($taker){
			foreach ($taker as $key => $value) {
				$head_pic[] = $value['head_pic'];
				$wechat_id[] = $value['wechat_id'];
			}
			$activity['taker'] = $head_pic;
			$activity['taker_wechat_id'] = $wechat_id;
		} else {
			$activity['taker'] = '暂无参与者';
		}
	

		return $activity;
	}
	/**
	 * 取消我的约驾活动
	 * @return $msg 成功或者失败
	 */
	public function delActivity()
	{
		$aid = input('get.aid');
		$uid = input('get.uid');
		$del = Db::table('yue_activity')
			       ->where('id',$aid)
			       ->delete();
	    if($del){
	    	$msg = ['status' => 1,'msg' => '约驾活动已取消，若有参与者，请及时与参与者联系','uid' => $uid];
		} else {
			$msg = ['status' => 0,'msg' => '约驾活动暂时无法删除，请稍后再试','uid' => $uid];
		}
		return $msg;
	}
	/**
	 * 我的约驾活动信息修改界面
	 * @return 当前选中的约驾活动信息详情
	 */
	public function alterActivity(){
		$aid = input('get.aid');
		$res =  Db::table('yue_activity')
			       	->alias('a')
				   	->leftJoin('yue_activity_picture p','p.aid = a.id')
		       		->where('a.id',$aid)
				   	->field('a.id,title,origin,start_time,stop_time,path,car_type,number,pic')
		   	    	->select();
	    if($res['0']){
	    	$activity = $res['0'];
			foreach ($res as $key => $value) {
				$pic[] = $value['pic'];
			}
			$activity['pic'] = $pic; 
	    } else {
	    	$activity = $res;
	    }
		
		return $activity;
	}
	/**
	 * 执行用户的修改约驾活动操作
	 * @return [type] [description]
	 */
	public function doAlterActivity()
	{
		//1.修改->删除原有图片，重新上传图片，即使和原有图片相同
		$aid = input('post.aid');
		$uid = input('post.uid');
		//1.1删除这个活动照片在数据库中的信息
		Db::startTrans();
		$del = Db::table('yue_activity_picture')
		       ->where('aid',$aid)
			   ->delete();
		$data = input('post.');
		$data['origin_lat'] = map($data['origin'])['lat'];
		$data['origin_lng'] = map($data['origin'])['lng'];
		$data['anumber'] = $data['number'];
		unset($data['aid']);
		unset($data['uid']);
		$res = Db::table('yue_activity')
		           ->where('id',$aid)
			       ->update($data);
	    if($res){
			$file = request()->file('image');
			if($file){
				$res = uploads($aid,$uid,$file);
				Db::commit();
			}
	    	$msg = ['status' => 1,'msg' => '更改成功，请等待有兴趣的人联系'];
	    } else {
	    	Db::rollback();
	    	$msg = ['status' => 0,'msg' => '更改失败，请稍后尝试'];
	    }	
		return $msg;
		
	}
}

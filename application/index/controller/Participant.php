<?php 
namespace app\index\controller;

use think\Controller;
use think\Db;

class Participant extends Controller
{
    /**
     * 参与活动
     * @return [type] [description]
     */
	public function takePart()
	{
		//获取活动id
		$data['aid'] = $aid = input('post.id');
		//获取当前用户id
		$data['uid'] = $uid = input('post.uid');
		$data['wechat_id'] = input('post.wechat_id');
		//用户输入的参与人数
		$data['number'] = $num = input('post.number');
		$validate = validate('Part');
		if($validate->check($data))
		{
			$activity = Db::table('yue_activity')
		          ->where('id',$aid)
		          ->find();
			//活动总人数
			$anumber = $activity['anumber'];
			//活动剩余人数
			$activity['anumber'] = $anumber - $num;
			//更新活动信息表
			$res = Db::table('yue_activity')
		        ->where('id',$aid)
		        ->update($activity);
			//向参与约驾活动信息表中添加新的数据
			$take = Db::table('yue_participant')
			        ->strict(false)
			        ->insert($data);
			if($take){
				$msg = ['status' => 1,'msg' => '参与约驾活动成功，请及时与活动发起者联系','aid' => $aid];
			} else {
				$msg = ['status' => 0,'msg' => '参与约驾活动失败，请稍后重试','aid' => $aid];
			}
		} else {
			$msg = ['status' => 0,'msg' =>$validate->getError(),'aid'=>$aid];
		}
		
		return $msg; 
	}
	/**
	 * 我参与的约驾活动的列表
	 * @return 关于我的约驾活动信息
	 */
	public function myTakePart()
	{
		$uid = input('get.uid');
		$res = Db::table('yue_participant')
		       ->alias('p')
		       ->join('yue_activity a','a.id = p.aid')
		       ->where('p.uid',$uid)
		       ->field('a.id,uid,title,path,start_time')
		       ->select();
		if($res){
			$msg = ['status' => 1, 'msg' => '获取我参与的约驾活动成功',$res];
		} else {
			$msg = ['status' => 0, 'msg' => '您尚未参与约驾'];
		}
		return $msg;
	}
	/**
	 * 取消已经参与的约驾活动
	 * @return 取消成功或者失败
	 */
	public function delMyTakePart()
	{
		$aid = input('get.aid');
		$uid = input('get.uid');
		$myTake = Db::table('yue_participant')
		          ->where('aid',$aid)
		          ->where('uid',$uid)
		          ->find();	
		//这个用户的参与人数
		$num = $myTake['number'];
		Db::startTrans();
		//恢复活动剩余人数
		if($myTake)
		{
			$res = Db::table('yue_participant')
		           ->where('aid',$aid)
		           ->where('uid',$uid)
		           ->delete();
			$activity = Db::table('yue_activity')
			        ->where('id',$aid)
			        ->find();
		   	//活动总人数
		    $number = $activity['number'];
	    	//活动剩余人数
		    $anumber = $activity['anumber'];
	    	//剩余人数 = 现在的剩余人数 + 这个用户之前输入的参与人数
	    	$anumber = $anumber + $num;
	    	if($anumber > $number)
	    	{
				$anumber = $number;
	    	}
	    	Db::table('yue_activity')
	    	    ->where('id',$aid)
	    	    ->update(['anumber' => $anumber]);
	    	Db::commit(); 
	    	if($res){
	    		$msg = ['status' => 1,'msg' => '您已取消参与约驾活动，请与活动发起者联系'];
	    	} else {
	    		$msg = ['status' => 0,'msg' => '取消失败，请稍后重试'];
	    	}
		} else {
			Db::rollback();
			$msg = ['status' => 0,'msg' => '取消失败，请稍后重试'];
		}
		return $msg;
	}
}
<?php 
namespace app\admin\controller;

use think\Db;
use think\Controller;

class User extends Base
{
	/**
	 * 约驾小程序用户列表
	 * @return [type] [description]
	 */
	public function userList(){
		$user = Db::table('yue_user')
		        ->order('create_time')
				->paginate(10);
	    $this->assign('user',$user);
		return $this->fetch();	
	}
	/**
	 * 用户详情
	 */
	public function detail(){
		$id = request()->param()['id'];
		$user = Db::table('yue_user')
		        ->where('id',$id)
				->find();
		$this->assign('user',$user);
		return $this->fetch();		
	}
	/**
	 * 查看用户参与
	 */
	public function userTake(){
		$id = request()->param()['id'];
		$take = Db::table('yue_participant')
		               ->alias('p')
					   ->join('yue_activity a','a.id = p.aid')
					   ->where('p.uid',$id)
					   ->select();
		$user = Db::table('yue_user')
		        ->where('id',$id)
				->field('id,name')
				->find();
	    $this->assign('user',$user);	
		$this->assign('take',$take);
		return $this->fetch();
	}
	/**
	 *用户参与的约驾活动详情 
	 */
	public function userTakeDetail(){
		$id = request()->param()['id'];
		$uid = request()->param()['uid'];
		$activity = Db::table('yue_activity')
		            ->alias('a')
					->join('yue_user u','u.id = a.creator_id')
					->where('a.id',$id)
					->field('a.id,title,car_type,a.create_time,origin,start_time,details,stop_time,path,number,anumber,a.status,name,wechat_id,sex')
					->find();		
		$pic = Db::table('yue_activity_picture')
		       ->alias('p')
			   ->join('yue_activity a','a.id = p.aid')
			   ->where('a.id',$id)
			   ->field('pic')
			   ->select();	  
	    $this->assign('pic',$pic);
		$this->assign('uid',$uid);
		$this->assign('activity',$activity);
		return $this->fetch();
	}
}
<?php 
namespace app\admin\controller;

use think\Db;
use think\Controller;

class Message  extends Base
{
	
	/**
	 * 发布系统消息
	 * @return   [<description>]
	 */
	public function addMessage(){
		$data = input('post.');
		$data['create_time'] = date('Y-m-d H:i:s', time());
		$res = Db::table('yue_message')
		       ->strict(false)
		       ->insert($data);
		if($res){
			$this->success('发布成功','admin/message/msgList');
		} else {
			$this->error('发布失败，请稍后重试','admin/message/msgList');
		}
	}
	/**
	 * 添加系统消息表单页
	 */
	public function add(){
		return $this->fetch();
	}
	/**
	 * 系统消息列表
	 * @return [type] [description]
	 */
	public function msgList(){
		$list = Db::table('yue_message') 
		        ->order('create_time desc')
				->paginate(10);
	    $this->assign('list',$list);
		return $this->fetch();						
	}
	/**
	 * 删除系统消息
	 */
	public function delMessage(){
		$id = request()->param()['id'];
		$res = Db::table('yue_message')
		        ->where('id',$id)
		        ->delete();
	    if($res){
	    	$this->success('删除系统消息成功','admin/message/msgList');
	    } else {
	    	$this->error('删除系统消息失败','admin/message/msgList');
	    }
	}
}
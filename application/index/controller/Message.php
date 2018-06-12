<?php 
namespace app\index\controller;

use think\Db;
use msg\Msg;
use think\Controller;
/**
 * 约驾小程序 我的 消息通知
 */
class Message extends Controller
{
	function initialize(){
		$this->uid = input('get.uid');
		$this->Msg = new Msg();
	}
	/**
	 * 用户进入消息通知后，向库里插入新发送的
	 * @return [type] [description]
	 */
	public function msg(){
		
		$res = $this->Msg->getUrMsg($this->uid);
		if($res){
			$msg = ['status' =>1,'msg' => '已获得最新消息'];
		} else {
			$msg = ['status' =>0,'msg' => '暂无新的系统消息'];
		}
		return $msg;
	}
	/**
	 * 消息列表全部
	 * @return [type] [description]
	 */
	public function msgList()
	{
		$list = $this->Msg->msgList($this->uid);
		if($list){
			$msg = ['status' => 1,'msg' => '获取列表成功','list' =>$list];
		} else {
			$msg = ['status' => 0,'msg' => '获取列表失败'];
		}
		return $msg;
	}
	/**
	 * 获取未读消息列表
	 * @return [type] [description]
	 */
	public function unread()
	{
		$list = $this->Msg->msgLists($this->uid,0);
		if($list)
		{
			$msg = ['status' => 1,'msg' => '获取未读列表成功','list' =>$list];
		} else {
			$msg = ['status' => 0,'msg' => '获取未读列表失败'];
		}
		return $msg;
	}
	/**
	 * 获取消息详情
	 * @return 消息详情
	 */
	public function msgDetail()
	{
		$mid = input('get.mid');
		$detail = $this->Msg->msgDetail($mid,$this->uid);
		if($detail){
			$msg = ['status' => 1,'msg' => '获取消息详情成功',$detail];
		} else {
			$msg = ['status' => 0,'msg' => '获取消息详情失败'];
		}
		return $msg;
	}
	/**
	 * 用户删除他的系统消息
	 * @return [type] [description]
	 */
	public function delMessage(){
		$mid = input('get.mid');
		$res = Db::table('yue_message_user')
		       ->where('uid',$this->uid)
		       ->where('mid',$mid)
		       ->delete();
		if($res){
			$msg = ['status' => 1,'msg' => '删除消息成功'];
		} else {
			$msg = ['status' => 0,'msg' => '删除消息失败，请重试'];
		}
		return $msg;
	}
}
<?php 
namespace app\admin\controller;
use think\Db;
use think\Controller;

class Admin extends Base
{
	/**
	 * 管理员注册
	 * @return [type] [description]
	 */
	public function register(){
		$data     = input('post.');
		$validate = validate('Register');
		if($validate->check($data)){
			$data['pwd'] = md5($data['pwd']);
			$res = Db::table('yue_admin')
			       ->strict(false)
			       ->insert($data);
			if($res){
				$msg = ['status' => 1,'msg' => '注册成功'];
			} else {
				$msg = ['status' => 0,'msg' => '注册失败'];
			}	
		} else {
			$msg = ['status' => 0,'msg' => $validate->getError()];
		}
		return $msg;
	}
	public function index(){
		return $this->fetch();
	}
	
	/**
	 * 管理员退出登录
	 * @return [type] [description]
	 */
	public function logout()
	{
		session('admin',null);
		return $this->redirect('admin/login/login');
	}
}
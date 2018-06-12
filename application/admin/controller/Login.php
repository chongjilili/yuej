<?php 
namespace app\admin\controller;

use think\Controller;
use think\Db;

class Login extends Controller
{
	public function login(){
		return $this->fetch();
	}
	/**
	 * 管理员登录
	 * @return [type] [description]
	 */
	public function doLogin()
	{
		$data     = input('post.');
		$validate = validate('Login');
		if($validate->check($data)){
			$arr = Db::table('yue_admin')
			       ->where('username',$data['username'])
			       ->where('pwd',md5($data['pwd']))
			       ->find();
			if($arr){
				session('admin',$arr);			
				$this->success('登录成功','admin/admin/index');
			} else {
				$this->error('登录失败','admin/login/login');
			}
		} else {
			$this->error($validate->getError(),'admin/login/login');
		}
	}
}
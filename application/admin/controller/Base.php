<?php 
namespace app\admin\controller;

use think\Db;
use think\Controller;
use think\Validate;
use think\facade\Session;

class Base extends Controller
{
	/**
	 * 初始化函数，判断用户是否登录
	 * @return [type] [description]
	 */
	public function initialize(){
		if (!Session::has('admin')) {
			$this->error('请先登录...', 'admin/login/login');
		}
	}
}
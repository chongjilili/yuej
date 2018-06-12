<?php 
namespace app\admin\validate;

use think\Validate;

class Register extends Validate
{
	protected $rule = [
		'username' => 'require|length:5,50|unique:yue_admin',
		'pwd' => 'require|length:5,50',
		'repwd'=>'require|confirm:pwd'
	];

	protected $message = [
		'username.require'   => '用户名不能为空',
		'username.length'    => '用户名长度必须在5-50个字符间',
		'username.unique'    => '用户名已存在',
		'pwd.require'        => '密码不能为空',
		'pwd.length'         => '密码长度必须在5-50个字符间',
		'repwd.require'      => '确认密码不能为空',
        'repwd.confirm'      => '两次输入不一致，请重新输入'
	];
}
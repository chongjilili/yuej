<?php
namespace app\admin\validate;

use think\Validate;

class Login extends Validate 
{
	protected $rule = [
		'username' => 'require|length:5,50',
		'pwd'      => 'require|length:5,50',
		'captcha'  => 'require|captcha'
	];

	protected $message = [
		'username.require' => '用户名不能为空',
		'username.length'  => '用户名长度在5-50个字符之间',
		'pwd.require'      => '密码不能为空',
		'pwd.length'       => '密码长度在5-50个字符之间',
		'captcha.require'  => '验证码必须输入',
		'captcha.captcha'  => '验证码不正确'
	];
}
<?php 
namespace app\index\validate;

use think\Validate;

class Act extends Validate
{
	protected $rule = [
		'number' => 'require|number'
	];

	protected $message = [
		'number.require' => '必须输入人数',
		'number.number'  => '人数必须为数字'
	];
}
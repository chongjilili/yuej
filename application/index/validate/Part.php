<?php
namespace app\index\validate;

use think\Validate;

class Part extends Validate
{
	protected $rule = [
	    'wechat_id' => 'require',
		'number'    => 'require|number'
	];
	
	protected $message = [
	    'wechat_id.require' => '微信号必须存在',
	    'number.require'    => '人数必须输入',
	    'number.number'     => '人数必须为数字'
	];
}

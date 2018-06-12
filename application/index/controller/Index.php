<?php
namespace app\index\controller;

use think\Db;

class Index
{ 
    
    /**
     * 登陆注册
     * @return $data 
     */
    public function login()
    {
    	//获取传过来的数据
    	$code = imput('get.code');    	
    	//对数据进行判断
    	if(empty($code) || $code == null || !$code){
    		$data = ['status' => 0,'msg' =>'微信code获取失败'];
    	} else {
            $arr = input('get.');
    		//获取信息成功的情况下
    		$openid = $this->get_openid($code)['openid'];
            //用户唯一编号
            $user_sn = $this->get_openid($code)['unionid'];
    		//查找数据库中是否存在这个用户
    		$res = Db::table('yue_user')->where('open_id',$openid)->find();
    		//如果存在，则更新这个用户的相关数据
    		if($res > 0){
    			Db::table('yue_user')->where('open_id',$openid)->update($arr);
    			$data = ['status' => 1,'msg' => '登录成功','uid' => $res['id']];
    		} else {
    			//如果不存在这个用户则添加这个用户
    			$arr['openid'] = $openid;
    			$arr['creat_time'] = time();
                $arr['user_sn'] = $user_sn;
    			//不存在的字段直接抛弃，返回新增数据的自增主键
    			$id = Db::table('yue_user')->strict(false)->insertGetId($arr);
    			$data = ['status' => 2,'msg' => '注册并登陆成功','uid' => $id];
    		}
    	}
    	return json($data);
    }
    /**
     * 获取openid
     * @param  临时登录凭证code
     * @return $arr 包含openid session_key unionid
     */
    public function get_openid($code)
    {
        //小程序的appid和secret
        $appid = 'wx6a435f292257fe95';
        $secret = '99154286e4fff9deb8c1d7a8eb6c4860';
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$code.'&grant_type=authorization_code';
        $weixin = file_get_contents($url);
        $jsondecode = json_decode($weixin);//对json格式的字符串进行编码
        $arr = get_object_vars($jsondecode);//转换成数组
        return $arr;
    }
}

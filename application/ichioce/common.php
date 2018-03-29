<?php

use think\Db;
use \think\Request;
use \wxAuthLib;
use \wx_public;
// 应用公共文件

function auath($id,$type){
	//==========================appid做成活的====
	$appid = 'wx844f289f684d283f';
	$wxInfoLib = new wxAuthLib();
	$wx = new \wx_public();
	if($type=='activity'){
		$redirect_uri = urlencode('http://'.$_SERVER['HTTP_HOST'].url('activity_insert',array('key'=>$appid,'id'=>$id)));
	}else if($type=='text'){
		$redirect_uri = urlencode('http://'.$_SERVER['HTTP_HOST'].url('insert',array('key'=>$appid,'id'=>$id)));
	}

	$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_base&state=123&component_appid=$this->appid#wechat_redirect";
	$res = $this->redirect($url);
}
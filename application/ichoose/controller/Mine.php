<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/11/21
 * Time: 17:02
 */

namespace app\ichoose\controller;
use think\Db;
use wx_api;
use think\Controller;
use think\log;
use \php_log;
use \wxAuthLib;
use \wx_public;

class mine extends Controller
{
	protected $appid = "";
	protected $secret = "";
	protected $token = "";
	protected $encodingAesKey = "";

	public function __construct()
	{
		parent::__construct();
		$this->appid = "wx9cefb7405a465206";
		$this->secret = "c2f0313091a0b753b614d9aed4f73e47";
		$this->token = "omJNpZEhZeHj1ZxFECKkP48B5VFbk1HP";
		$this->encodingAesKey = "H0VEFBYRYsSSsTjSMkoTQiV6CMLLjIoNP6hBIkhMi54";
	}

	public function index(){
		echo '我发起的';
	}

	public function start(){

		$status = input('param.status');
		$wx = new \wx_public();
		$wxInfoLib = new wxAuthLib();
		if(empty($status)){
			$this->auath( null,'start');
		}else{
			$appid = input('param.key');
			$code = $_GET['code'];
			$aa = $wxInfoLib->getComponentAccessToken();
			$url = "https://api.weixin.qq.com/sns/oauth2/component/access_token?appid=$appid&code=$code&grant_type=authorization_code&component_appid=$this->appid&component_access_token=$aa";
			$res = $wxInfoLib->httpGet($url);
			$res = json_decode($res,true);
			$openid = $res['openid'];
			$uid = Db::table('ty_account_user')->where('openid',$openid)->field('id')->find()['id'];
			$vo_list = Db::table('i_vote_list')->alias('a')
				->join('ty_account_user b','a.uid = b.id')
				->field('a.title,a.create_type,a.create_time,b.nickname as name,a.con')
				->where('b.id',$uid)
				->select();
			$act_list = Db::table('i_activity_list')->alias('a')
				->join('ty_account_user b','a.uid = b.id')
				->field('a.title,a.create_type,a.create_time,b.nickname as name,a.con')
				->where('b.id',$uid)
				->select();
			$list = array_merge($vo_list,$act_list);
			dump($list);

		}

	}

	public function auath($id='',$type){
		//==========================appid做成活的=============================
		$appid = 'wx844f289f684d283f';
		$wxInfoLib = new wxAuthLib();
		$wx = new \wx_public();

		if($type=='activity'){
			$redirect_uri = urlencode('http://'.$_SERVER['HTTP_HOST'].url('activity_insert',array('key'=>$appid,'id'=>$id)));
		}else if($type=='text'){
			$redirect_uri = urlencode('http://'.$_SERVER['HTTP_HOST'].url('insert',array('key'=>$appid,'id'=>$id)));
		}else if($type=='start'){
			$redirect_uri = urlencode('http://'.$_SERVER['HTTP_HOST'].url('start',array('key'=>$appid,'status'=>'not')));
		}
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_base&state=123&component_appid=$this->appid#wechat_redirect";
		$res = $this->redirect($url);
	}
}